/**
 * Populates binlog with test data.
 */
CREATE TABLE `oxcategories` (
   OXID VARCHAR(100),
   OXTITLE VARCHAR(100)
);

CREATE TABLE `oxobject2category` (
  OXID VARCHAR(100),
  OXCATNID VARCHAR(100),
  OXOBJECTID VARCHAR(100)
);

CREATE TABLE `oxarticles` (
  OXID VARCHAR(100),
  OXTITLE VARCHAR(100)
);

INSERT INTO `oxcategories` (OXID, OXTITLE) VALUES ('cat0', 'title1');
UPDATE `oxcategories` SET OXTITLE='title2' WHERE OXID='cat0';

INSERT INTO `oxarticles` (OXID) VALUES ('art0');
INSERT INTO `oxarticles` (OXID) VALUES ('art1');
INSERT INTO `oxarticles` (OXID) VALUES ('art2');

INSERT INTO `oxobject2category` (OXID, OXCATNID, OXOBJECTID) VALUES ('oc0', 'cat0', 'art0');
INSERT INTO `oxobject2category` (OXID, OXCATNID, OXOBJECTID) VALUES ('oc1', 'cat0', 'art1');

UPDATE `oxarticles` SET OXTITLE='Product 1' WHERE OXID='art0';
UPDATE `oxarticles` SET OXTITLE='Product 2' WHERE OXID='art1';
UPDATE `oxarticles` SET OXTITLE='Product 3' WHERE OXID='art2';

DELETE FROM `oxarticles` WHERE OXID='art1';
