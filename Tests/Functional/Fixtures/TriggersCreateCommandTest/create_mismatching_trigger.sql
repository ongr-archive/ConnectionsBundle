DROP TRIGGER IF EXISTS `oxcategory_insert`;

CREATE TRIGGER oxcategory_insert
AFTER INSERT ON `oxcategories`
FOR EACH ROW
  BEGIN INSERT INTO `ongr_sync_jobs` SET `document_type`='category',`document_id`=NEW.OXID,`timestamp`=NOW(),`status`=0,`type`='U',`update_type`='1'; END;
