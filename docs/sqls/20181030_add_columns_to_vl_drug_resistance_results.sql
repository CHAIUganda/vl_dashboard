ALTER TABLE vl_drug_resistance_results 
ADD COLUMN accession_number varchar(15), 
ADD COLUMN cphl_patient_unique_id varchar(30),
ADD COLUMN vl_sample_id varchar(30),
 
ADD COLUMN entity_patient_id varchar(30),
ADD COLUMN analyst varchar(50), 
ADD COLUMN no_charge_reason varchar(255),

ADD COLUMN study_name varchar(50),
ADD COLUMN study_code varchar(15), 
ADD COLUMN test_date datetime,
ADD COLUMN result_date datetime,

ADD COLUMN result_string LONGTEXT, 
ADD COLUMN form_number varchar(15),
ADD COLUMN api_username varchar(10);