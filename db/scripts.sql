create or replace view accountbase2 as
select year(t.date) year, month(t.date) month, t.date,t.account_id, sum(amount) income, 0 expence from transactions t where t.type = 1 group by 3, 4
union all
select year(t.date) year, month(t.date) month, t.date,t.account_id, 0 income, sum(amount) expence from transactions t where t.type = 2 group by 3, 4;

select year date
,account_id
,get_currency_name(account_id) currency
,get_account_name(account_id) account
,round(sum(income)/100,2) income
,round(sum(expence)/100,2) expence
,round(GET_ACCOUNT_CURRENT_BALANCE(account_id, STR_TO_DATE(CONCAT(year(date),'-12-31'),'%Y-%m-%d'))/100,2) balance
from accountbase2 where account_id = 3 group by 1;

select date
,get_currency_name(account_id) currency
,get_account_name(account_id) account
,round(sum(income)/100,2) income
,round(sum(expence)/100,2) expence
,round(GET_ACCOUNT_CURRENT_BALANCE(account_id, date)/100,2) balance
from accountbase2 where account_id = 3 and year=2012 and month = 5;

create or replace VIEW `vaccounts2` AS select `accounts`.`_id` AS `_id`,`accounts`.`name` AS `name`,`accounts`.`type` AS `type`,`accounts`.`currency_id` AS `currency_id`,`GET_CURRENCY_NAME`(`accounts`.`_id`) AS `currency`,ifnull(round(`accounts`.`opening_balance`/100,2),0.00) AS `opening_balance`,ifnull(round(`accounts`.`minimum_balance`/100,2),0.00) AS `minimum_balance`,ifnull(round(`accounts`.`current_balance`/100,2),0.00) AS `current_balance`,`accounts`.`favourite` AS `favourite`,`accounts`.`notes` AS `notes`,ifnull(round(`GET_AMOUNT_EQUIVALENT`(`accounts`.`current_balance`,`accounts`.`currency_id`)/100,2),0.00) AS `equivalent` from `accounts` where `accounts`.`close_date` IS NULL and current_balance <> 0 order by `accounts`.`name`;

create or replace view vtransactions2 as
select t._id, case when t.type = 2 then round(t.amount/100,2)*-1 else round(t.amount/100,2) end amount, GET_CURRENCY_ID(t.account_id) currency_id, GET_CURRENCY_NAME(t.account_id) currency, t.account_id, GET_ACCOUNT_NAME(t.account_id) account, t.mirror_account_id, GET_ACCOUNT_NAME(t.mirror_account_id) mirror_account, t.type, t.category_id, ifnull(GET_CATEGORY_NAME(category_id),'Трансфер') category, t.date from transactions t;

delimiter $$
DROP FUNCTION IF EXISTS GET_ACCOUNT_CURRENT_BALANCE$$
CREATE FUNCTION GET_ACCOUNT_CURRENT_BALANCE (id INT, _date DATE)
RETURNS bigint(20)
BEGIN
DECLARE current_balance bigint(20);
select (ifnull(SUM(x.amount),0)+ifnull(SUM(x.opening_balance),0)) into current_balance from (
SELECT CASE WHEN t.type = 1 THEN t.amount WHEN t.type = 2 THEN t.amount * -1 END amount, 0 opening_balance
FROM transactions t WHERE t.account_id=id  and t.date <= _date
union all
select 0, opening_balance from accounts where _id = id
) x;
RETURN current_balance;
END;
$$
delimiter ;

CREATE OR REPLACE VIEW vcategories2 AS 
 SELECT categories._id, get_category_name(categories._id) AS name, 
        CASE
            WHEN categories.type = 1 THEN 'Прихід'::text
            ELSE 'Розхід'::text
        END AS type, 
        CASE
            WHEN categories.visible = 1 THEN 'Так'::text
            ELSE 'Ні'::text
        END AS visible, categories.close_date, categories.type AS type_dig, categories.parent_id,
        case when categories.shared::int = 1 then 'Публічна' else 'Приватна' end as shared_text, shared::int "shared"
   FROM categories
  WHERE categories.close_date IS NULL
  ORDER BY get_category_name(categories._id);

ALTER TABLE vcategories2
  OWNER TO mdb3;
  
  
create or replace view vcurrencies2 as
select _id,code,symbol,case when home = 1 then 'Так' else NULL end home,round(rate/100,2) rate from currencies where close_date is null order by name;

ALTER TABLE `currencies` ADD `close_date` date default NULL;
ALTER TABLE `categories` ADD `close_date` date default NULL;

create or replace view plandetails2 as
select _id,name,sdate,edate,GET_ACCOUNT_NAME(account_id) account,round(amount/100,2) amount,comment from plans where cdate is null
union all
select plan_id,stage,(select sdate from plans where _id = plan_id limit 1),NULL,NULL,round(stage_cost/100,2) stage_cost,NULL from plan_schedule order by 1;

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
`_id` int(6) NOT NULL AUTO_INCREMENT,
`extension` varchar(6) COMMENT 'File extension',
`filebody` MEDIUMBLOB NOT NULL COMMENT 'File content',
`transaction_id` int(11) default null,
PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`_id`);

DELIMITER $$
DROP PROCEDURE IF EXISTS CREATE_TRANSACTION$$ 
CREATE PROCEDURE CREATE_TRANSACTION(account_id int(11),amount bigint(20),category_id int(11),notes varchar(100),mirror_id int(11),mirror_account_id int(11), date date, type int(1),OUT tid int(11))
LANGUAGE SQL
NOT DETERMINISTIC
MODIFIES SQL DATA
BEGIN
IF type in (1,2) THEN
INSERT INTO transactions(`account_id`,`amount`,`category_id`,`notes`,`status`,`mirror_id`,`mirror_account_id`,`date`,`type`)
values(account_id,amount,category_id,notes,1,mirror_id,mirror_account_id,date,type);
SET tid = LAST_INSERT_ID();
ELSEIF type = 3 THEN
set max_sp_recursion_depth=2;
CALL CREATE_TRANSACTION(account_id,amount,category_id,notes,NULL,mirror_account_id,date,2,@aid);
CALL CREATE_TRANSACTION(mirror_account_id,amount,category_id,notes,@aid,account_id,date,1,@bid);
update transactions set `mirror_id` = @bid where _id = @aid; 
END IF;
END $$
delimiter ;

create or replace view vfiles as
select _id,transaction_id,concat(lpad(_id,6,'0'),'.',extension) name from files order by 1;

create or replace view report2 as
select year(t.date) year,t.amount, t.equivalent, t.type, GET_CURRENCY_NAME(t.account_id) currency,t.category_id category,c.visible from transactions t, categories c where t.category_id = c._id and t.mirror_id is NULL;

create or replace view vplans as
select p._id, p.name,p.sdate, p.edate, get_currency_name(p.account_id) currency, p.account_id,
get_account_name(p.account_id) account,
case when p.sdate <= current_date() then ifnull(round((select sum(s.stage_cost) from plan_schedule s where s.plan_id = p._id and s.stage <= month_diff(p.sdate,current_date())+1)/100,2),0)
else 0.00 end planamount, round(GET_ACCOUNT_CURRENT_BALANCE(p.account_id,current_date())/100,2) realamount from plans p where p.cdate is null order by 1;

create or replace view report3b as
select if(type=1,GET_AMOUNT_EQUIVALENT(amount,GET_CURRENCY_ID(account_id)),GET_AMOUNT_EQUIVALENT(amount,GET_CURRENCY_ID(account_id))*-1) amount,year(date) year,month(date) month from transactions where mirror_account_id is null;

create or replace view report3c as
select amount a,0 b,0 c,0 d,0 e,0 f,0 g,0 h,0 i,0 j,0 k,0 l,year,month from report3b where month=1
union all
select 0,amount,0,0,0,0,0,0,0,0,0,0,year,month from report3b where month=2
union all
select 0,0,amount,0,0,0,0,0,0,0,0,0,year,month from report3b where month=3
union all
select 0,0,0,amount,0,0,0,0,0,0,0,0,year,month from report3b where month=4
union all
select 0,0,0,0,amount,0,0,0,0,0,0,0,year,month from report3b where month=5
union all
select 0,0,0,0,0,amount,0,0,0,0,0,0,year,month from report3b where month=6
union all
select 0,0,0,0,0,0,amount,0,0,0,0,0,year,month from report3b where month=7
union all
select 0,0,0,0,0,0,0,amount,0,0,0,0,year,month from report3b where month=8
union all
select 0,0,0,0,0,0,0,0,amount,0,0,0,year,month from report3b where month=9
union all
select 0,0,0,0,0,0,0,0,0,amount,0,0,year,month from report3b where month=10
union all
select 0,0,0,0,0,0,0,0,0,0,amount,0,year,month from report3b where month=11
union all
select 0,0,0,0,0,0,0,0,0,0,0,amount,year,month from report3b where month=12;

create or replace view report3d as
select SUM(a) '1',SUM(b) '2',SUM(c) '3',SUM(d) '4',SUM(e) '5',SUM(f) '6',SUM(g) '7',SUM(h) '8',SUM(i) '9',SUM(j) '10',SUM(k) '11',SUM(l) '12',year from report3c
group by year order by year;

DELIMITER $$
DROP PROCEDURE IF EXISTS DELETE_TRANSACTION$$ 
CREATE PROCEDURE DELETE_TRANSACTION(tid int(11))
LANGUAGE SQL
NOT DETERMINISTIC
MODIFIES SQL DATA
BEGIN
-- declare vars
DECLARE _mt INT;
--
IF exists(select 1 from transactions where _id = tid) THEN
select mirror_id into _mt from transactions where _id = tid;
IF _mt IS NOT NULL THEN
delete from transactions where _id = _mt;
END IF;
delete from transactions where _id = tid; 
END IF;
END $$
delimiter ;

-- 2013-10-10 --
DELIMITER $$
DROP PROCEDURE IF EXISTS CREATE_PLAN_SCHEDULE$$ 
CREATE PROCEDURE CREATE_PLAN_SCHEDULE(_plan_id int,startdate date,enddate date,startamount bigint(20),endamount bigint(20))
LANGUAGE SQL
DETERMINISTIC
MODIFIES SQL DATA
BEGIN
DECLARE i INT Default 1;
DECLARE n INT DEFAULT 1;
DECLARE c INT DEFAULT 0;
DECLARE lc INT;
SET n:=MONTH_DIFF(startdate,enddate);
SET c:=floor((endamount-startamount)/n);
SET lc:=c+((endamount-startamount)%n);
main_loop: LOOP
IF i=n THEN
insert into plan_schedule(plan_id,stage,stage_cost) values(_plan_id,i,lc);
ELSE         
insert into plan_schedule(plan_id,stage,stage_cost) values(_plan_id,i,c);
END IF;
SET i=i+1;
IF i=n+1 THEN
LEAVE main_loop;
END IF;
END LOOP main_loop;
END $$
delimiter ;

DELIMITER $$
DROP PROCEDURE IF EXISTS RECALCULATE_PLAN_SCHEDULE$$ 
CREATE PROCEDURE RECALCULATE_PLAN_SCHEDULE(_plan_id int,enddate date,endamount bigint(20))
LANGUAGE SQL
DETERMINISTIC
MODIFIES SQL DATA
BEGIN
-- declare vars
DECLARE _account INT;
DECLARE _before INT;
DECLARE _after INT;
DECLARE _account_balance bigint(20);
DECLARE _s date;
DECLARE _chunk bigint(20);
DECLARE _last_chunk bigint(20);
declare i int;
declare n int;
--
-- check current technical account balance 
select account_id, sdate into _account, _s from plans where _id = _plan_id;
IF _account is not null THEN
select GET_ACCOUNT_CURRENT_BALANCE(_account, CURRENT_DATE()) into _account_balance;
SET _before:=MONTH_DIFF(_s,CURRENT_DATE());
IF _account_balance = 0 THEN
update plan_schedule set stage_cost=0 where plan_id = _plan_id and stage<=_before;
ELSE
SET _chunk:=round(_account_balance/_before);
update plan_schedule set stage_cost=_chunk where plan_id = _plan_id and stage<_before;
update plan_schedule set stage_cost=(_account_balance-_chunk*(_before-1)) where plan_id = _plan_id and stage=_before;
END IF;
SET _after:=MONTH_DIFF(CURRENT_DATE(),enddate);
if _after > 0 then
SET i:=_before+1;
SET n:=_before+_after;
DELETE FROM plan_schedule WHERE plan_id = _plan_id and stage > _before;
select endamount-_account_balance into _account_balance;
select floor(_account_balance/_after) into _chunk;
select _chunk+(_account_balance%_after) into _last_chunk;
loop1: LOOP
if i=n then
insert into plan_schedule(plan_id,stage,stage_cost) values(_plan_id,i,_last_chunk);
else
insert into plan_schedule(plan_id,stage,stage_cost) values(_plan_id,i,_chunk);
end if;
SET i=i+1;
IF i=n+1 THEN
LEAVE loop1;
END IF;
END LOOP loop1; 
END IF;
END IF;
END $$
delimiter ;
-- 2014-01-16 --
ALTER TABLE transactions ADD COLUMN owner integer;

ALTER TABLE transactions
  ADD CONSTRAINT transactions_owner_users_id_fk FOREIGN KEY (owner) REFERENCES users (_id)
   ON UPDATE NO ACTION ON DELETE RESTRICT;
   
CREATE INDEX fki_transactions_owner_users_id_fk
  ON transactions(owner);

-- DROP FUNCTION create_transaction(integer, bigint, integer, character varying, integer, integer, date, integer);

CREATE OR REPLACE FUNCTION create_transaction(_account_id integer, _amount bigint, _category_id integer, _notes character varying, _mirror_id integer, _mirror_account_id integer, _date date, _type integer, _uid integer)
  RETURNS character varying AS
$BODY$
DECLARE
tid int;
aid int;
acid int;
bcid int;
BEGIN
IF _type in (1,2) THEN
INSERT INTO transactions(account_id,amount,category_id,notes,status,mirror_id,mirror_account_id,date,type,owner)
values(_account_id,_amount,_category_id,_notes,1,_mirror_id,_mirror_account_id,_date,_type,_uid) RETURNING _id INTO tid;
RETURN tid::varchar;
ELSEIF _type = 3 THEN
select into acid currency_id from accounts where _id = _account_id limit 1;
select into bcid currency_id from accounts where _id = _mirror_account_id limit 1;
if acid is null or bcid is null then
RAISE EXCEPTION 'curency_id of one of the accounts is missing a=%, b=%', acid,bcid;
else
	if acid <> bcid then
	RAISE EXCEPTION 'curency_id of the accounts do not match a=%, b=%', acid,bcid;
	end if;
end if;
select into aid create_transaction(_account_id,_amount,_category_id,_notes,NULL,_mirror_account_id,_date,2,_uid)::int;
select into tid create_transaction(_mirror_account_id,_amount,_category_id,_notes,aid,_account_id,_date,1,_uid)::int;
update transactions set mirror_id = tid where _id = aid;
return aid::varchar||','||tid::varchar;
END IF;
END; $BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION create_transaction(integer, bigint, integer, character varying, integer, integer, date, integer, integer)
  OWNER TO mdb3;

ALTER TABLE categories ADD COLUMN shared boolean NOT NULL DEFAULT false;
