#
# Table structure for table 'tx_formpdf_domain_model_pdftemplate'
#
CREATE TABLE tx_formpdf_domain_model_pdftemplate (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
	  file int(11) unsigned NOT NULL default '0',
    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_formpdf_domain_model_htmltemplate'
#
CREATE TABLE tx_formpdf_domain_model_htmltemplate (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
	  file int(11) unsigned NOT NULL default '0',
	  name varchar(255) DEFAULT '' NOT NULL,
    PRIMARY KEY (uid),
    KEY parent (pid)
);
