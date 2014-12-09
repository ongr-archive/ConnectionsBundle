CREATE TABLE `oxcategories` (
   OXID VARCHAR(100)
);

CREATE TABLE `oxobject2category` (
  OXID VARCHAR(100),
  OXCATNID VARCHAR(100),
  OXOBJECTID VARCHAR(100)
);

CREATE TABLE `oxarticles` (
  OXID VARCHAR(100)
);

INSERT INTO `oxcategories` (OXID) VALUES ('cat0');

INSERT INTO `oxarticles` (OXID) VALUES ('art0');
INSERT INTO `oxarticles` (OXID) VALUES ('art1');

INSERT INTO `oxobject2category` (OXID, OXCATNID, OXOBJECTID) VALUES ('oc0', 'cat0', 'art0');
INSERT INTO `oxobject2category` (OXID, OXCATNID, OXOBJECTID) VALUES ('oc0', 'cat0', 'art1');
