Alter table vl_districts add column dhis2_uid varchar(12) after district; 
Alter table vl_districts add column dhis2_name varchar(40) after district;

Alter table vl_facilities add column district_uid varchar(12) after facility; 
Alter table vl_facilities add column dhis2_uid varchar(12) after facility; 
Alter table vl_facilities add column dhis2_name varchar(40) after facility;

UPDATE vl_districts SET dhis2_name='Sembabule District', dhis2_uid='j7AQsnEYmvi' WHERE id=82;
UPDATE vl_districts SET dhis2_name='Maracha District', dhis2_uid='WyR8Eetj7Uw' WHERE id=58;
