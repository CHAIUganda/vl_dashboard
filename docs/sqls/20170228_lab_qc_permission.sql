INSERT INTO `vldash`.`permissions` (
`id` ,
`name` ,
`display_name` ,
`description` ,
`created_at` ,
`updated_at`
)
VALUES (
3 , 'lab_qc', 'Lab QC', 'Quality Control for Lab', '2017-02-28 00:00:00', '0000-00-00 00:00:00'
);

INSERT INTO `vldash`.`roles` (
`id` ,
`name` ,
`display_name` ,
`description` ,
`created_at` ,
`updated_at`
)
VALUES (
4 , 'lab_qc_only', 'Lab QC Only', 'Do Quality Control Only for Lab section', '2017-02-28 00:00:00', '0000-00-00 00:00:00'
);


INSERT INTO `vldash`.`permission_role` (
`permission_id` ,
`role_id`
)
VALUES (
'3', '3'
), (
'3', '4'
);