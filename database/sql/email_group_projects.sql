-- Run this ALTER once, before deploying the Projects form field.
ALTER TABLE `marketing_email_group_contacts`
    ADD COLUMN `project` VARCHAR(255) NULL AFTER `name`;

-- Preview the split. The final " - " separates the person from the project.
SELECT `id`, `name` AS `original_name`,
    TRIM(LEFT(`name`, CHAR_LENGTH(`name`) - CHAR_LENGTH(SUBSTRING_INDEX(`name`, ' - ', -1)) - 3)) AS `person_name`,
    TRIM(SUBSTRING_INDEX(`name`, ' - ', -1)) AS `project_name`
FROM `marketing_email_group_contacts`
WHERE TRIM(COALESCE(`project`, '')) = ''
    AND `name` LIKE '% - %'
    AND TRIM(SUBSTRING_INDEX(`name`, ' - ', -1)) <> ''
    AND TRIM(LEFT(`name`, CHAR_LENGTH(`name`) - CHAR_LENGTH(SUBSTRING_INDEX(`name`, ' - ', -1)) - 3)) <> '';

-- Fill only missing projects, then remove the project suffix from the name.
-- Assign project first so both expressions use the original name in MySQL.
UPDATE `marketing_email_group_contacts`
SET `project` = TRIM(SUBSTRING_INDEX(`name`, ' - ', -1)),
    `name` = TRIM(LEFT(`name`, CHAR_LENGTH(`name`) - CHAR_LENGTH(SUBSTRING_INDEX(`name`, ' - ', -1)) - 3))
WHERE TRIM(COALESCE(`project`, '')) = ''
    AND `name` LIKE '% - %'
    AND TRIM(SUBSTRING_INDEX(`name`, ' - ', -1)) <> ''
    AND TRIM(LEFT(`name`, CHAR_LENGTH(`name`) - CHAR_LENGTH(SUBSTRING_INDEX(`name`, ' - ', -1)) - 3)) <> '';
