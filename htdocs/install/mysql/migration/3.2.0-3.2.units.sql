--Copyright (C) 2012      Cedric Salvador       <csalvador@gpcsolutions.fr>
--
-- Be careful to requests order.
-- This file must be loaded by calling /install/index.php page
-- when current version is 3.2.0 or higher. 
--
-- To rename a table:       ALTER TABLE llx_table RENAME TO llx_table_new;
-- To add a column:         ALTER TABLE llx_table ADD COLUMN newcol varchar(60) NOT NULL DEFAULT '0' AFTER existingcol;
-- To rename a column:      ALTER TABLE llx_table CHANGE COLUMN oldname newname varchar(60);
-- To drop a column:        ALTER TABLE llx_table DROP COLUMN oldname;
-- To change type of field: ALTER TABLE llx_table MODIFY name varchar(60);
-- To restrict request to Mysql version x.y use -- VMYSQLx.y
-- To restrict request to Pgsql version x.y use -- VPGSQLx.y


-- -- VPGSQL8.2 DELETE FROM llx_usergroup_user      WHERE fk_user      NOT IN (SELECT rowid from llx_user);
-- -- VMYSQL4.1 DELETE FROM llx_usergroup_user      WHERE fk_usergroup NOT IN (SELECT rowid from llx_usergroup);

-- Delete old themes setup
DELETE FROM llx_user_param WHERE param = 'MAIN_THEME' and value = 'freelug';

update llx_propal set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_commande set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_facture set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_commande_fournisseur set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_contrat set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_deplacement set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_facture_fourn set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_facture_rec set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_fichinter set fk_projet = null where fk_projet not in (select rowid from llx_projet);
update llx_projet_task set fk_projet = null where fk_projet not in (select rowid from llx_projet);

update llx_commande set fk_user_author = null where fk_user_author not in (select rowid from llx_user);

create table llx_c_units(
	rowid integer AUTO_INCREMENT PRIMARY KEY,
	code varchar(3),
	label varchar(50),
	short_label varchar(5),
	active tinyint DEFAULT 1 NOT NULL
)ENGINE=innodb;

ALTER TABLE llx_c_units ADD UNIQUE uk_c_units_code(code);

INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('P','piece','p', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('SET','set','se', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('S','second','s', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('H','hour','h', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('D','day','d', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('KG','kilogram','kg', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('G','gram','g', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('M','meter','m', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('LM','linear meter','lm', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('M2','square meter','m2', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('M3','cubic meter','m3', 1);
INSERT INTO llx_c_units ( code, label, short_label, active) VALUES ('L','liter','l', 1);


alter table llx_product add fk_unit integer default 1;
ALTER TABLE llx_product ADD CONSTRAINT fk_product_fk_unit FOREIGN KEY (fk_unit) REFERENCES llx_c_units (rowid);

alter table llx_facturedet_rec add fk_unit integer default 1;
ALTER TABLE llx_facturedet_rec ADD CONSTRAINT fk_facturedet_rec_fk_unit FOREIGN KEY (fk_unit) REFERENCES llx_c_units (rowid);

alter table llx_facturedet add fk_unit integer default 1;
ALTER TABLE llx_facturedet ADD CONSTRAINT fk_facturedet_fk_unit FOREIGN KEY (fk_unit) REFERENCES llx_c_units (rowid);

alter table llx_propaldet add fk_unit integer default 1;
ALTER TABLE llx_propaldet ADD CONSTRAINT fk_propaldet_fk_unit FOREIGN KEY (fk_unit) REFERENCES llx_c_units (rowid);

alter table llx_commandedet add fk_unit integer default 1;
ALTER TABLE llx_commandedet ADD CONSTRAINT fk_commandedet_fk_unit FOREIGN KEY (fk_unit) REFERENCES llx_c_units (rowid);
