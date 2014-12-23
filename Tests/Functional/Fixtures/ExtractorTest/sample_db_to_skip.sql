/**
 * Populates binlog with test data, this data should be skipped when parsing binlog eventually.
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

INSERT INTO `oxcategories` (OXID, OXTITLE) VALUES ('cat15', 'title15');
UPDATE `oxcategories` SET OXTITLE='title30' WHERE OXID='cat15';

INSERT INTO `oxarticles` (OXID) VALUES ('art10');
INSERT INTO `oxarticles` (OXID) VALUES ('art11');
INSERT INTO `oxarticles` (OXID) VALUES ('art12');

INSERT INTO `oxobject2category` (OXID, OXCATNID, OXOBJECTID) VALUES ('oc10', 'cat15', 'art10');
INSERT INTO `oxobject2category` (OXID, OXCATNID, OXOBJECTID) VALUES ('oc11', 'cat15', 'art11');

UPDATE `oxarticles` SET OXTITLE='Product 10' WHERE OXID='art10';
UPDATE `oxarticles` SET OXTITLE='Product 11' WHERE OXID='art11';
UPDATE `oxarticles` SET OXTITLE='Product 12' WHERE OXID='art12';

DELETE FROM `oxarticles` WHERE OXID='art11';

SELECT SLEEP(5); -- Ensure time difference in binlog.