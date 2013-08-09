CREATE TABLE %%prefix%%cat_layout_zo (
  ml_zo_id int(11) unsigned NOT NULL auto_increment,
  fk_cat int(11) unsigned NOT NULL default '0',
  fk_layout int(11) unsigned NOT NULL default '0',
  is_default tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (ml_zo_id)
) AUTO_INCREMENT=117 ;_

CREATE TABLE %%prefix%%cat_plg_comp (  mmc_id int(11) unsigned NOT NULL,  fk_cat int(11) unsigned NOT NULL default '0',  fk_modcomp int(11) unsigned NOT NULL default '0',  order_num int(4) unsigned NOT NULL default '0',  PRIMARY KEY  (mmc_id)) ;_
INSERT INTO %%prefix%%cat_plg_comp VALUES (1, 0, 1, 1);_
INSERT INTO %%prefix%%cat_plg_comp VALUES (3, 0, 2, 2);_
INSERT INTO %%prefix%%cat_plg_comp VALUES (4, 0, 3, 0);_
INSERT INTO %%prefix%%cat_plg_comp VALUES (5, 0, 5, 1);_

CREATE TABLE _%%prefix%%plg_comps__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=6 ;_
INSERT INTO _%%prefix%%plg_comps__seq VALUES (5);_

CREATE TABLE %%prefix%%classification (
  classify_id int(11) unsigned NOT NULL auto_increment,
  classify_name varchar(50) NOT NULL default '',
  number int(4) unsigned NOT NULL default '0',
  fk_syskey int(11) unsigned NOT NULL default '0',
  fk_dict int(11) NOT NULL default '0',
  PRIMARY KEY  (classify_id),
  UNIQUE KEY name_syskey (classify_name, fk_syskey),
  UNIQUE KEY number_syskey ( fk_syskey, number ),
  KEY fk_syskey (fk_syskey)
) AUTO_INCREMENT=48 ;_
INSERT INTO %%prefix%%classification VALUES (1, 'in_progress', 20, 12, 300);_
INSERT INTO %%prefix%%classification VALUES (2, 'published', 100, 12, 297);_
INSERT INTO %%prefix%%classification VALUES (3, 'deactivated', 60, 12, 298);_
INSERT INTO %%prefix%%classification VALUES (4, 'to_be_approved', 40, 12, 299);_
INSERT INTO %%prefix%%classification VALUES (6, 'SYSTEM', 100, 1, 0);_
INSERT INTO %%prefix%%classification VALUES (7, 'USERDEFINED', 200, 1, 0);_
INSERT INTO %%prefix%%classification VALUES (41, 'ASC', 100, 16, 309);_
INSERT INTO %%prefix%%classification VALUES (9, 'de', 100, 5, 7);_
INSERT INTO %%prefix%%classification VALUES (10, 'en', 200, 5, 8);_
INSERT INTO %%prefix%%classification VALUES (11, 'MySQL', 100, 6, 0);_
INSERT INTO %%prefix%%classification VALUES (12, 'PostgreSQL', 200, 6, 0);_
INSERT INTO %%prefix%%classification VALUES (20, 'main', 100, 14, 0);_
INSERT INTO %%prefix%%classification VALUES (21, 'system', 200, 14, 0);_
INSERT INTO %%prefix%%classification VALUES (22, 'component', 300, 14, 0);_
INSERT INTO %%prefix%%classification VALUES (24, 'System', 100, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (25, 'Category', 400, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (26, 'Modules', 300, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (27, 'Files', 301, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (28, 'Dictionary', 302, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (29, 'Layout &amp; CSS', 600, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (30, 'Site Information', 200, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (31, 'RSS', 303, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (32, 'TopArticles (Latest)', 304, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (33, 'Articles', 305, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (34, 'object_id', 100, 15, 147);_
INSERT INTO %%prefix%%classification VALUES (35, 'text', 200, 15, 148);_
INSERT INTO %%prefix%%classification VALUES (38, 'editable', 100, 7, 0);_
INSERT INTO %%prefix%%classification VALUES (37, 'User', 500, 11, 0);_
INSERT INTO %%prefix%%classification VALUES (39, 'not-editable', 200, 7, 0);_
INSERT INTO %%prefix%%classification VALUES (40, 'invisible', 300, 7, 0);_
INSERT INTO %%prefix%%classification VALUES (42, 'DESC', 200, 16, 310);_
INSERT INTO %%prefix%%classification VALUES (43, 'boolean', 100, 17, 0);_
INSERT INTO %%prefix%%classification VALUES (44, 'syskey_id', 200, 17, 0);_
INSERT INTO %%prefix%%classification VALUES (45, 'integer', 300, 17, 0);_
INSERT INTO %%prefix%%classification VALUES (46, 'varchar255', 400, 17, 0);_
INSERT INTO %%prefix%%classification VALUES (47, 'longtext', 500, 17, 0);_

CREATE TABLE %%prefix%%comments (
  comment_id int(11) unsigned NOT NULL auto_increment,
  fk_content int(11) unsigned NOT NULL default '0',
  fk_comment int(11) unsigned NOT NULL default '0',
  fk_author int(11) unsigned NOT NULL default '0',
  author varchar(80) NOT NULL default '',
  ip_address varchar(24) NOT NULL default '',
  heading varchar(80) default NULL,
  contenttext text,
  created datetime default '0000-00-00 00:00:00',
  status_id int(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (comment_id),
  KEY content_status_created (fk_content, status_id, created)
) AUTO_INCREMENT=3 ;_




CREATE TABLE %%prefix%%config (
  config_id int(11) unsigned NOT NULL,
  fk_section int(11) unsigned NOT NULL default '0',
  var_name varchar(100) NOT NULL default '',
  var_value text NOT NULL,
  var_description text NOT NULL,
  var_type varchar(255) NOT NULL default 'text',
  editable smallint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (config_id)
);_
INSERT INTO %%prefix%%config VALUES (1, 24, 'debugging_active', '1', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (3, 30, 'webmasterEmail', 'aheusingfeld@goldstift.de', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (125, 24, 'showOptPlugins', '1', 'generally turn off optional plugins section', '43', 1);_
INSERT INTO %%prefix%%config VALUES (6, 24, 'langKey', 'de', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (8, 24, 'writeLog', '1', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (9, 24, 'writeSQLLog', '0', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (10, 24, 'writeInternSQLLog', '0', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (12, 24, 'showPrePage', '0', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (14, 24, 'showTop5', '1', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (16, 24, 'showStyleswitcher', '0', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (123, 24, 'user_request_keep_time', '15', 'How long shall user requests be kept in request log before they are dropped? (in minutes)', '45', 1);_
INSERT INTO %%prefix%%config VALUES (18, 24, 'showPathway', '1', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (19, 24, 'showSearch', '0', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (20, 24, 'showRegisterUser', '0', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (21, 24, 'showForgotPword', '1', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (23, 30, 'metaDescription', 'Die Internetseite der Johanniter Kindertagesstätte Hilden lädt Sie ein, einfach mal rein zu schauen. ', '', '47', 1);_
INSERT INTO %%prefix%%config VALUES (24, 30, 'metaKeywords', 'kinder, kindergarten, kindertagesstätte hilden, hilden, garten, tagesstätte, stadt, johanniter unfall hilfe e.v. mettmann, johanniter-unfall-hilfe e.v., borderless cms, barrierefreiheit, behindertengerechtes internet, cms, content management system, goldstift, alexander heusingfeld, css', '', '47', 1);_
INSERT INTO %%prefix%%config VALUES (25, 30, 'metaAuthor', 'goldstift aheusingfeld[at]goldstift[dot]de', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (26, 30, 'metaDistributor', 'Johanniter-Unfall-Hilfe e.V. Kindertagesstätte Hilden', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (27, 30, 'metaRevisitAfter', '60 days', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (28, 30, 'welcomemessage', 'Johanniter Kindertagesstätte Hilden', '', '47', 1);_
INSERT INTO %%prefix%%config VALUES (29, 30, 'copyright_linktext', 'Â© Copyright 2007 Johanniter Kindertagesstätte Hilden', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (30, 30, 'welcomemail_subject', 'Willkommen auf kita-tucherweg.de', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (111, 31, 'rss_no_of_entries', '10', 'Anzahl der Eintraege im RSS-Feed', '45', 1);_
INSERT INTO %%prefix%%config VALUES (110, 28, 'dict_max_trans_length', '80', 'Maximale Anzeigel&auml;nge der &Uuml;bersetzungstexte in der W&ouml;rterbuchtabelle im Administrationsbereich', '45', 1);_
INSERT INTO %%prefix%%config VALUES (109, 24, 'max_list_entries', '25', 'Gibt die allgemein für alle Listen die maximale Anzahl an Zeilen an.', '45', 1);_
INSERT INTO %%prefix%%config VALUES (67, 33, 'content_per_page', '5', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (71, 32, 'no_of_latestentries', '6', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (72, 32, 'preview_length', '250', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (73, 24, 'badwords', 'fick, fuck, drecks, arsch, wixer, vixer, fixer, hurensohn, cheiss, cheiß, fotze, foze, hure, nutte, möse, moese, porn, pussy, cum, sperm, viagra', '', '47', 1);_
INSERT INTO %%prefix%%config VALUES (74, 24, 'badwords_replace', '***', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (75, 29, 'default_css', 'kita', 'Standardmaessig zu ladendes Layout', '46', 1);_
INSERT INTO %%prefix%%config VALUES (78, 24, 'date_format', 'd.m.Y H:i:s', 'd = day/ Tag\r\nm = month/ Monat\r\ny = year/ Jahr\r\nH = hour/ Stunde\r\ni = minute\r\ns = second/ Sekunde', '46', 1);_
INSERT INTO %%prefix%%config VALUES (79, 24, 'allowed_tags', '<strong></strong> <i></i> <u></u> <p></p> <span></span> <div></div> <br /><br> <code></code> <q></q> <cite></cite>', 'Default: <strong></strong><i></i><u></u><p></p><span></span><div></div><br /><li></li><ul></ul>', '46', 1);_
INSERT INTO %%prefix%%config VALUES (124, 30, 'page_subtitle', '', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (81, 24, 'showTop5UserOnly', '0', 'Top5 nur fuer angemeldete User zeigen?', '43', 1);_
INSERT INTO %%prefix%%config VALUES (82, 33, 'PublishEndDate', '2019-12-31 23:59:59', 'Standard-''Publish_end''-Datum. Dies ist der Vorbelegungswert für das Datum, an dem ein Artikel nicht mehr im Frontend angezeigt werden soll.', '46', 1);_
INSERT INTO %%prefix%%config VALUES (83, 30, 'metaRobots', 'ALL, INDEX, FOLLOW', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (84, 30, 'metaRating', '', 'Meta-Site-Rating', '46', 1);_
INSERT INTO %%prefix%%config VALUES (85, 30, 'metaCharset', 'utf-8', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (88, 24, 'showEmptyCategory', '0', 'Sollen leere Rubriken überhaupt angezeigt werden?', '43', 1);_
INSERT INTO %%prefix%%config VALUES (89, 24, 'showValidInfos', '1', 'Zeige Buttons Valid XHTML, Valid CSS, cc508 und so weiter', '43', 1);_
INSERT INTO %%prefix%%config VALUES (90, 30, 'intropage_details', '', '', '47', 1);_
INSERT INTO %%prefix%%config VALUES (112, 37, 'user_sessionTimeout', '90', 'Zeit in Minuten, die Benutzer inaktiv sein k&ouml;nnen, bevor sie automatisch abgemeldet werden', '45', 1);_
INSERT INTO %%prefix%%config VALUES (69, 27, 'images_per_page', '3', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (93, 24, 'upload_dir', 'upload_kita/', '', '47', 1);_
INSERT INTO %%prefix%%config VALUES (94, 24, 'upload_max_filesize', '2000', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (95, 24, 'login_locktime', '3600', 'Sperrzeit eines Benutzerkontos in Sekunden', '45', 1);_
INSERT INTO %%prefix%%config VALUES (96, 24, 'login_max_tries', '5', 'Maximale fehlerhafte Loginversuche bevor ads Konto gesperrt', '45', 1);_
INSERT INTO %%prefix%%config VALUES (97, 24, 'default_form_enctype', 'application/x-www-form-urlencoded', 'Default value for encryption type in html forms', '46', 1);_
INSERT INTO %%prefix%%config VALUES (98, 24, 'showSystemMenu', '1', '', '43', 1);_
INSERT INTO %%prefix%%config VALUES (99, 29, 'default_fs_css', 'default', 'Options are:\r\n- default\r\n- medium\r\n- large', '46', 1);_
INSERT INTO %%prefix%%config VALUES (100, 30, 'page_title', 'TESTSYSTEM Johanniter Kindertagesstätte Hilden', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (101, 24, 'default_cat_id', '99', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (103, 31, 'rss_title', 'Johanniter Kindertagesstätte Hilden - RSS Feed', 'Titel des RSS-Feeds', '46', 1);_
INSERT INTO %%prefix%%config VALUES (104, 31, 'rss_description', 'Die aktuellsten Beiträge der Internetseite der Johanniter Kindertagesstätte Hilden', 'Beschreibung des RSS-Feeds', '47', 1);_
INSERT INTO %%prefix%%config VALUES (105, 30, 'meta_desc_length', '200', 'Lï¿½nge der MetaDescription in Zeichen', '45', 0);_
INSERT INTO %%prefix%%config VALUES (106, 26, 'default_modname', 'system', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (107, 26, 'default_modfunc', 'show', '', '46', 1);_
INSERT INTO %%prefix%%config VALUES (108, 26, 'default_modoid', '1', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (113, 24, 'connector', '.::.', 'Verbindungsstück z.B. beim Seitentitel', '46', 1);_
INSERT INTO %%prefix%%config VALUES (114, 37, 'defaultUserId', '2', '', '45', 0);_
INSERT INTO %%prefix%%config VALUES (115, 27, 'max_object_list_entries', '15', '', '45', 1);_
INSERT INTO %%prefix%%config VALUES (116, 27, 'default_jpeg_quality', '80', 'Default Quality for resize in % (percent)', '45', 1);_
INSERT INTO %%prefix%%config VALUES (117, 24, 'notice_display_level', '1,2,3,4,5', 'The severity a message must have to be displayed in system messages \r\nDEBUG       = 0\r\nINFO          = 1\r\nWARNING  = 2\r\nFAILURE    = 3\r\nERROR      = 4\r\nCRITICAL  = 5\r\n', '46', 1);_
INSERT INTO %%prefix%%config VALUES (118, 24, 'notice_log_level', '0,1,2,3,4,5', 'The severity a message must have to be logged to the database\r\nDEBUG       = 0\r\nINFO          = 1\r\nWARNING  = 2\r\nFAILURE    = 3\r\nERROR      = 4\r\nCRITICAL  = 5\r\n', '46', 1);_
INSERT INTO %%prefix%%config VALUES (119, 24, 'select_field_max_no_of_chars', '50', 'maximum length of selectfield option-tags content', '45', 1);_
INSERT INTO %%prefix%%config VALUES (121, 24, 'bugreportEmail', 'bugs@borderlesscms.de', 'Mail address to which bug reports shall be sent', '46', 1);_
INSERT INTO %%prefix%%config VALUES (122, 24, 'bugreportNoOfPriorRequest', '3', 'Number of prior request that are sent with a bug report', '45', 1);_
INSERT INTO %%prefix%%config VALUES (126, 24, 'db_revision', '187', '', '46', 0);_
INSERT INTO %%prefix%%config VALUES (152, 24, 'imprintCategoryName', 'impressum', 'Name of imprint section. Used in meta header information on each page.', '46', 1);_
INSERT INTO %%prefix%%config VALUES (153, 24, 'contactCategoryName', 'kontakt', 'Name of contact section. Used in meta header information on each page.', '46', 1);_

CREATE TABLE _%%prefix%%config__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=154 ;_
INSERT INTO _%%prefix%%config__seq VALUES (153);_



CREATE TABLE %%prefix%%fieldtypes (
  fieldtype_id int(11) unsigned NOT NULL auto_increment,
  fieldtypename varchar(100) NOT NULL default '',
  form_tag varchar(30) NOT NULL default '',
  form_elementtype varchar(40) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  attributes varchar(255) character set utf8 collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (fieldtype_id)
) AUTO_INCREMENT=4 ;_

INSERT INTO %%prefix%%fieldtypes VALUES (1, 'Textfeld (einzeilig)', 'text', '', NULL);_
INSERT INTO %%prefix%%fieldtypes VALUES (2, 'Textfeld (mehrzeilig)', 'textarea', '', NULL);_
INSERT INTO %%prefix%%fieldtypes VALUES (3, 'Bild', 'img', '', NULL);_

--
-- Tabellenstruktur für Tabelle `%%prefix%%grouprightassoc`
--

CREATE TABLE %%prefix%%grouprightassoc (
  ID_RR_ZO int(11) unsigned NOT NULL auto_increment,
  FK_ROLLE int(11) unsigned NOT NULL default '0',
  FK_RECHT int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (ID_RR_ZO)
) AUTO_INCREMENT=1501 ;_

INSERT INTO %%prefix%%grouprightassoc VALUES (1463, 3, 72);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1133, 2, 12);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1132, 2, 3);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1462, 3, 69);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1497, 6, 8);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1496, 6, 74);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1495, 6, 37);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1441, 4, 81);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1440, 4, 71);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1439, 4, 72);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1438, 4, 9);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1437, 4, 66);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1436, 4, 7);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1435, 4, 1);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1434, 4, 8);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1433, 4, 4);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1432, 4, 2);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1461, 3, 49);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1257, 7, 72);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1494, 6, 72);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1431, 4, 55);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1430, 4, 54);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1429, 4, 58);_
INSERT INTO %%prefix%%grouprightassoc VALUES (55, 1, 12);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1493, 6, 66);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1492, 6, 41);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1491, 6, 47);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1428, 4, 56);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1490, 6, 71);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1489, 6, 63);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1488, 6, 22);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1487, 6, 20);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1486, 6, 19);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1485, 6, 1);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1427, 4, 57);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1426, 4, 59);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1425, 4, 53);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1424, 4, 12);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1256, 7, 71);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1255, 7, 49);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1254, 7, 9);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1253, 7, 7);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1252, 7, 12);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1423, 4, 60);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1484, 6, 3);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1483, 6, 31);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1422, 4, 51);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1460, 3, 52);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1421, 4, 50);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1420, 4, 3);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1419, 4, 11);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1131, 2, 7);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1418, 4, 52);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1417, 4, 37);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1416, 4, 35);_
INSERT INTO %%prefix%%grouprightassoc VALUES (68, 19, 5);_
INSERT INTO %%prefix%%grouprightassoc VALUES (69, 19, 32);_
INSERT INTO %%prefix%%grouprightassoc VALUES (70, 19, 33);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1482, 6, 28);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1481, 6, 52);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1480, 6, 40);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1258, 12, 73);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1415, 4, 46);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1446, 20, 69);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1479, 6, 39);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1478, 6, 11);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1459, 3, 1);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1445, 20, 12);_
INSERT INTO %%prefix%%grouprightassoc VALUES (82, 1, 7);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1130, 2, 49);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1458, 3, 11);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1457, 3, 12);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1251, 7, 11);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1250, 7, 19);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1414, 4, 26);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1413, 4, 23);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1412, 4, 49);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1411, 4, 44);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1410, 4, 40);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1409, 4, 43);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1408, 4, 39);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1444, 20, 7);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1407, 4, 42);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1456, 3, 3);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1477, 6, 50);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1476, 6, 12);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1475, 6, 23);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1474, 6, 53);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1455, 3, 19);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1454, 3, 7);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1453, 3, 40);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1249, 7, 20);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1248, 7, 3);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1247, 7, 1);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1246, 7, 31);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1245, 7, 46);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1244, 7, 55);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1243, 7, 54);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1242, 7, 56);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1241, 7, 57);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1240, 7, 53);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1452, 3, 53);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1239, 7, 40);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1451, 3, 57);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1406, 4, 41);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1405, 4, 47);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1404, 4, 19);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1403, 4, 20);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1402, 4, 22);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1401, 4, 65);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1400, 4, 62);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1399, 4, 64);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1398, 4, 61);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1397, 4, 63);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1396, 4, 69);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1443, 20, 40);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1450, 3, 55);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1473, 6, 57);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1238, 7, 39);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1134, 2, 69);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1395, 4, 68);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1394, 4, 70);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1449, 3, 54);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1448, 3, 71);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1237, 7, 52);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1236, 7, 69);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1393, 4, 67);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1472, 6, 56);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1471, 6, 58);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1470, 6, 54);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1469, 6, 55);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1468, 6, 9);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1467, 6, 35);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1466, 6, 2);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1465, 6, 7);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1464, 6, 69);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1392, 4, 74);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1442, 4, 82);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1447, 20, 71);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1498, 6, 82);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1499, 6, 81);_
INSERT INTO %%prefix%%grouprightassoc VALUES (1500, 6, 49);_



CREATE TABLE %%prefix%%history (
  history_id int(11) unsigned NOT NULL,
  version float unsigned NOT NULL default '1',
  content_id int(11) unsigned NOT NULL default '0',
  fk_cat int(11) unsigned NOT NULL default '0',
  fk_editor_id int(11) unsigned NOT NULL default '0',
  editdate datetime NOT NULL default '0000-00-00 00:00:00',
  lang varchar(5) NOT NULL default 'de',
  heading varchar(80) NOT NULL default '',
  description text NOT NULL,
  layout_id int(11) unsigned NOT NULL default '0',
  contenttext text NOT NULL,
  publish_begin datetime default '0000-00-00 00:00:00',
  publish_end datetime default '2099-12-31 23:59:59',
  prev_img_id int(11) NOT NULL default '0',
  prev_img_float varchar(10) NOT NULL default 'no_float',
  status_id int(11) NOT NULL default '0',
  redirect_url varchar(255) default NULL,
  meta_keywords varchar(255) default NULL,
  techname varchar(80) NOT NULL default '',
  PRIMARY KEY  (history_id)
);_

CREATE TABLE _%%prefix%%history__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=1 ;_
INSERT INTO _%%prefix%%history__seq VALUES (0);_




--
-- Tabellenstruktur für Tabelle `%%prefix%%last_transactions`
--

CREATE TABLE %%prefix%%last_transactions (
  action_id int(11) unsigned NOT NULL auto_increment,
  session_id varchar(50) NOT NULL default '',
  sql_stmt text NOT NULL,
  timestmp int(14) NOT NULL default '0',
  PRIMARY KEY  (action_id),
  KEY timestmp (timestmp),
  KEY sql_stmt (sql_stmt(60))
) AUTO_INCREMENT=1 ;_


CREATE TABLE %%prefix%%layout_fieldtype_zo (
  layout_id int(11) unsigned NOT NULL default '0',
  fieldtype_id int(11) NOT NULL default '0',
  ordering_num smallint(4) unsigned NOT NULL default '0',
  preset_value text NOT NULL,
  tech_title varchar(100) NOT NULL default '',
  readonly enum('0','1') NOT NULL default '0',
  required enum('0','1') NOT NULL default '0',
  rules text NOT NULL,
  PRIMARY KEY  (layout_id,ordering_num)
);_

INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (1, 1, 1, 'Bitte Überschrift eingeben', 'heading', '0', '1', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (1, 2, 2, 'Geben Sie hier bitte Ihren Text ein', 'fliesstext', '0', '0', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (2, 1, 1, 'Bitte Überschrift eingeben', 'heading', '0', '1', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (2, 2, 2, 'Bildbeschreibung eingeben', 'bild2_text', '0', '0', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (2, 3, 3, '1', 'bild', '0', '0', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (2, 2, 4, 'Geben Sie hier bitte Ihren Text ein', 'fliesstext', '0', '0', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (2, 2, 5, 'Geben Sie hier bitte Ihren Text ein', 'fliesstext1', '0', '0', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (3, 2, 1, 'Geben Sie hier bitte Ihren Text ein', 'fliesstext', '0', '1', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (4, 1, 1, 'Bitte Überschrift eingeben', 'heading', '0', '1', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (4, 2, 2, 'Bildbeschreibung eingeben', 'bild_text', '0', '0', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (4, 3, 3, '1', 'bild', '0', '0', '');_
INSERT INTO %%prefix%%layout_fieldtype_zo VALUES (4, 2, 4, 'Geben Sie hier bitte Ihren Text ein', 'fliesstext', '0', '0', '');_

CREATE TABLE %%prefix%%layoutpresets (
  layout_id int(11) unsigned NOT NULL auto_increment,
  layoutname varchar(100) NOT NULL default '',
  filename varchar(100) NOT NULL default '',
  description text NOT NULL,
  PRIMARY KEY  (layout_id)
) AUTO_INCREMENT=5 ;_
INSERT INTO %%prefix%%layoutpresets VALUES (1, 'Layout1 - &Uuml;berschrift+Text', 'fzg_article1', '&Uuml;berschrift + einspaltiger Flie&szlig;text');_
INSERT INTO %%prefix%%layoutpresets VALUES (2, 'Layout2 - &Uuml;berschrift, 2spaltiger Text + Bild', 'fzg_article2', '&Uuml;berschrift, Bild mit Bildbeschreibung (links), Flie&szlig;text neben Bild (rechts) und Flie&szlig;text unterhalb von Bild und Flie&szlig;text1');_
INSERT INTO %%prefix%%layoutpresets VALUES (3, 'Layout3 - Nur Text', 'fzg_article_text_only', 'einspaltiger Flie&szlig;text ohne &Uuml;berschrift oder &Auml;hnliches');_
INSERT INTO %%prefix%%layoutpresets VALUES (4, 'Layout4 - &Uuml;berschrift, Text, Bild, Text', 'fzg_startseite', 'Layout f&uum;r die Startseite: &Uuml;berschrift, Einleitung, gro&szlig;es Bild, Flie&szlig;text');_

CREATE TABLE %%prefix%%plg_art_cat_conf (
  cat_id int(11) unsigned NOT NULL default '0',
  add_right int(11) unsigned NOT NULL default '0',
  edit_right int(11) unsigned NOT NULL default '0',
  edit_own_right int(11) unsigned NOT NULL default '0',
  change_status_right int(11) unsigned NOT NULL default '0',
  del_right int(11) unsigned NOT NULL default '0',
  no_of_articles_per_page smallint(5) unsigned NOT NULL default '5',
  content_order_by varchar(40) NOT NULL default '',
  sort_direction int(11) unsigned NOT NULL default '0',
  comments_sort_direction int(11) unsigned NOT NULL default '42',
  no_of_comment_per_page smallint(5) unsigned NOT NULL default '0',
  hide_comments_on_show tinyint(1) unsigned NOT NULL default '0',
  user_id int(11) unsigned NOT NULL default '0',
  change_date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (cat_id)
);_



CREATE TABLE %%prefix%%plg_articles (
  content_id int(11) unsigned NOT NULL,
  fk_cat int(11) unsigned NOT NULL default '0',
  heading varchar(80) default NULL,
  contenttext text,
  fk_creator int(11) unsigned NOT NULL default '0',
  created datetime default '0000-00-00 00:00:00',
  description text,
  version float unsigned NOT NULL default '1',
  lang varchar(5) NOT NULL default 'de',
  layout_id int(11) unsigned NOT NULL default '1',
  status_id smallint(3) unsigned NOT NULL default '0',
  publish_begin datetime default '0000-00-00 00:00:00',
  publish_end datetime default '2099-12-31 23:59:59',
  hits int(11) unsigned NOT NULL default '0',
  prev_img_id int(11) NOT NULL default '0',
  prev_img_float varchar(10) NOT NULL default 'no_float',
  redirect_url varchar(255) default NULL,
  meta_keywords varchar(255) default NULL,
  techname varchar(80) NOT NULL default '',
  PRIMARY KEY  (content_id),
  KEY (created),
  KEY for_top5 (fk_creator, fk_cat, status_id, publish_begin, publish_end)
) ;_
CREATE TABLE _%%prefix%%plg_articles__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=1 ;_
INSERT INTO _%%prefix%%plg_articles__seq VALUES (0);_


CREATE TABLE %%prefix%%plg_cat (
  cat_id int(11) unsigned NOT NULL,
  techname varchar(50) NOT NULL default '',
  categorylink_title varchar(255) default NULL,
  icon_src varchar(40) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  categoryname varchar(50) default NULL,
  root_id int(11) unsigned NOT NULL default '0',
  lft int(8) unsigned NOT NULL default '0',
  rgt int(8) unsigned NOT NULL default '0',
  user_only tinyint(1) unsigned NOT NULL default '0',
  fk_view_right int(11) unsigned NOT NULL default '40',
  fk_edit_right int(11) unsigned NOT NULL default '39',
  fk_delete_right int(11) unsigned NOT NULL default '41',
  fk_plg_conf_right int(11) unsigned NOT NULL default '60',
  viewable4all tinyint(1) unsigned NOT NULL default '1',
  writeable4all tinyint(1) unsigned NOT NULL default '1',
  commentable tinyint(1) unsigned NOT NULL default '1',
  show_cat_desc tinyint(1) unsigned NOT NULL default '1',
  show_pathway tinyint(1) unsigned NOT NULL default '1',
  show_opt_plugins tinyint(1) unsigned NOT NULL default '1',
  use_ssl tinyint(1) unsigned NOT NULL default '0',
  fk_type_id int(11) unsigned NOT NULL default '0',
  description text,
  accesskey char(1) default NULL,
  meta_description varchar(200) default NULL,
  meta_keywords varchar(255) default NULL,
  additional_css text,
  status_id mediumint(4) unsigned NOT NULL default '0',
  publishing_date datetime NOT NULL default '0000-00-00 00:00:00',
  edit_date datetime NOT NULL default '0000-00-00 00:00:00',
  editor_id int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (cat_id),
  UNIQUE KEY techname (techname),
  KEY lr (lft,rgt),
  KEY root_id (root_id),
  KEY treelist (lft),
  KEY status_id (status_id),
  KEY treelist2 (lft,status_id),
  KEY list_small_tree (lft, status_id, user_only)
);_

INSERT INTO %%prefix%%plg_cat VALUES (49, '__user__', NULL, '', '__user__', 0, 1, 12, 0, 40, 39, 41, 60, 0, 1, 1, 1, 1, 0, 0, 0, '', NULL, NULL, '', '', 0, '2006-01-18 14:01:35', '0000-00-00 00:00:00', 0);_
INSERT INTO %%prefix%%plg_cat VALUES (31, 'logout', '', 'gfx/silk/door_in.png', 'Logout', 49, 2, 3, 1, 49, 43, 42, 60, 0, 0, 0, 0, 0, 0, 0, 27, '', 'Q', '', '', '', 100, '2006-03-01 16:33:24', '2006-09-25 14:42:05', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (6, 'profile', 'Bearbeiten Sie hier ihr Benutzerprofil. Hier können Sie Ihre Daten und persönlichen Systemeinstellungen ändern.', 'gfx/silk/vcard_edit.png', 'Profil bearbeiten', 49, 4, 5, 1, 49, 43, 42, 60, 0, 0, 0, 1, 1, 0, 0, 28, '', 'p', 'Bearbeiten Sie hier ihr Benutzerprofil. Hier können Sie Ihre Daten und persönlichen Systemeinstellungen ändern.', 'benutzerprofil, benutzer profile, profile, settings, einstellungen', '', 100, '2006-03-03 12:05:20', '2006-12-07 21:59:07', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (52, 'files', '', 'gfx/silk/images.png', 'Dateien', 49, 6, 7, 0, 72, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 24, '', '', '', '', '', 100, '2006-05-18 00:18:52', '2007-03-22 23:57:54', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (135, 'admmenu', '', 'gfx/silk/eye.png', 'Administration', 49, 8, 9, 1, 44, 43, 42, 43, 0, 0, 0, 1, 1, 0, 0, 43, '', 'A', '', 'administration, admin menu', '', 100, '2007-03-22 20:07:00', '2007-03-22 22:32:40', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (28, 'login', '', 'gfx/silk/key.png', 'Login', 49, 10, 11, 0, 40, 43, 42, 60, 1, 0, 0, 1, 1, 0, 0, 26, '', 'l', '', '', '', 100, '2006-03-01 16:31:44', '2006-09-25 14:43:13', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (2, '__admin__', '', '', '__admin__', 0, 13, 44, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 11, '', '', '', '', '', 100, '2006-07-14 20:06:00', '2006-07-14 21:52:04', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (130, 'overview', '', 'gfx/silk/house.png', 'Übersicht', 2, 14, 21, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 11, '', '', '', '', '', 100, '2006-09-24 20:06:00', '2006-12-30 00:45:31', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (128, 'content', '', 'gfx/silk/page_copy.png', 'Artikel', 2, 15, 16, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 2, '', '', '', '', '', 100, '2006-07-16 20:06:00', '2006-12-30 00:44:38', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (34, 'menu', 'test', 'gfx/silk/application_double.png', 'Rubriken', 2, 17, 18, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 33, '', '', '', '', '', 100, '2005-01-01 10:00:00', '2006-12-29 11:56:09', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (102, 'menu_move', '', 'gfx/silk/application_go.png', 'Rubriken verschieben', 34, 19, 20, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 36, '', '', '', '', '', 100, '2006-05-01 20:06:00', '2006-12-29 11:55:42', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (75, 'user', 'Benutzerverwaltung - Benutzer, Gruppen und Rechte pflegen', 'gfx/silk/user.png', 'Benutzerverwaltung', 2, 22, 27, 0, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 34, 'teste mal die beschreibung', '', '', '', '', 100, '2006-04-29 15:01:25', '2006-12-29 11:52:32', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (1, 'gruppen', 'Hier können Sie Einstellungen der System- und Benutzergruppen pflegen', 'gfx/silk/group.png', 'Gruppen', 75, 23, 24, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 39, '', '', '', '', '', 100, '2006-07-10 20:06:00', '2006-12-29 11:52:40', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (122, 'systemrechte', 'Bearbeiten Sie hier die Systemrechte', 'gfx/silk/lock.png', 'Systemrechte', 75, 25, 26, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 38, '', '', '', '', '', 100, '2006-06-30 20:06:00', '2006-12-29 11:53:27', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (97, 'plugs', 'Hier können die im System vorhandenen Plugins bearbeitet werden', 'gfx/silk/plugin.png', 'Plugins', 2, 28, 33, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 35, '', '', '', '', '', 100, '2006-05-01 01:09:53', '2006-12-29 22:03:40', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (104, 'plugins_bearbeiten', '', 'gfx/silk/plugin_edit.png', 'Plugins bearbeiten', 97, 29, 30, 1, 44, 43, 42, 60, 0, 0, 0, 1, 1, 0, 0, 37, 'Hier können neue Module, Hauptseiten- und Komponentenmodule angelegt werden.', '', 'Hier können neue Module, Hauptseiten- und Komponentenmodule angelegt werden.', '', '', 100, '2001-01-01 00:59:59', '2006-12-29 22:03:54', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (129, 'formmanager', '', 'gfx/silk/application_form.png', 'FormularManager', 97, 31, 32, 0, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 11, '', '', '', '', '', 100, '2006-07-16 20:06:00', '2006-12-31 23:17:07', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (127, 'settings', '', 'gfx/silk/cog.png', 'Einstellungen', 2, 34, 43, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 11, '', '', '', '', '', 100, '2006-07-15 20:06:00', '2006-12-29 22:02:17', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (131, 'config', 'Hier können die Systemeinstellungen bearbeitet werden', 'gfx/silk/wrench.png', 'Systemkonfiguration', 127, 35, 36, 1, 63, 64, 65, 64, 0, 0, 0, 0, 1, 0, 0, 40, '', '', 'Fehler melden', 'error, bug, send message, report bug, fehler melden, fehlermeldung', '', 100, '2006-12-16 20:06:00', '2006-12-29 11:58:10', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (133, 'syslog', '', 'gfx/silk/report.png', 'Systemprotokoll', 127, 37, 38, 1, 44, 43, 42, 43, 0, 0, 0, 0, 1, 0, 0, 42, '', '', '', '', '', 100, '2007-01-01 00:59:59', '2007-01-21 00:01:12', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (132, 'requestlog', '', 'gfx/silk/printer.png', 'Anfrageprotokoll', 127, 39, 40, 0, 44, 43, 42, 43, 0, 0, 0, 0, 1, 0, 0, 41, '', '', '', 'anfrageprotokoll, request log', '', 100, '2006-12-18 20:06:00', '2007-03-22 22:34:33', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (103, 'dicitonary', '', 'gfx/silk/book.png', 'Wörterbuch', 127, 41, 42, 1, 44, 43, 42, 60, 0, 0, 0, 0, 1, 0, 0, 9, '', '', '', '', '', 100, '2006-05-02 23:06:00', '2006-12-29 11:58:38', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (47, '__system__', NULL, '', '__system__', 0, 45, 52, 0, 40, 39, 41, 60, 1, 0, 0, 1, 1, 0, 0, 0, NULL, NULL, NULL, '', NULL, 0, '2006-01-13 00:00:00', '0000-00-00 00:00:00', 0);_
INSERT INTO %%prefix%%plg_cat VALUES (99, 'home', 'Dieser Link bringt Sie zur Startseite', '', 'Startseite', 47, 46, 47, 0, 40, 39, 41, 60, 1, 0, 0, 1, 1, 1, 0, 11, '', '0', 'Startseite', 'home, main, start, knaller, aufregend, spannend, neu, ereignis', '', 100, '2006-05-01 17:27:14', '2006-05-28 23:55:12', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (3, 'kontakt', '', '', 'Kontakt', 47, 48, 49, 0, 40, 39, 41, 60, 1, 1, 0, 1, 1, 0, 0, 11, '', '', '', '', '', 100, '2006-08-24 00:00:00', '2007-03-22 21:53:18', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (4, 'impressum', 'Dieser Link bringt Sie zum Impressum dieser Seite', '', 'Impressum', 47, 50, 51, 0, 40, 39, 41, 60, 1, 0, 0, 1, 1, 0, 0, 11, '', '9', '', 'impressum, imprint, rechtliches, hinweis, urheber', '', 100, '2005-12-01 00:00:00', '2006-05-01 17:17:19', 1);_
INSERT INTO %%prefix%%plg_cat VALUES (48, '__main__', NULL, '', '__main__', 0, 53, 56, 0, 40, 39, 41, 60, 1, 0, 0, 1, 1, 0, 0, 0, '', NULL, NULL, '', '', 0, '2006-01-13 00:22:28', '0000-00-00 00:00:00', 0);_
INSERT INTO %%prefix%%plg_cat VALUES (10, 'aktuell', 'Aktuelle Meldungen', '', 'Aktuell', 48, 54, 55, 0, 40, 39, 41, 60, 1, 1, 1, 1, 1, 0, 0, 10, 'In dieser Rubrik finden Sie alle aktuellen Termine. Sie werden nach Datum aufsteigend sortiert, so dass oben, am Anfang der Liste, immer der nächste Termin steht. \r\nAlte Termine verschwinden automatisch aus der Liste.', '', 'In dieser Rubrik finden Sie alle aktuellen Termine.', '', 'div#articles h3.heading {font:normal normal 1.4em/ 1.6em tahoma,geneva,arial,helvetica,sans-serif;display:inline; }\r\ndiv.contentlist {margin:2em;}\r\n.contentlist_info {display:inline;}\r\ndiv.contentlist div.pubend { display:inline; }\r\ndiv.contentlist div.version {display:none;}\r\ndiv.contentlist div.sec_row {display:inline; margin:0em;}\r\ndiv.contentlist_description {display:none}', 100, '2006-05-18 09:00:00', '2007-01-02 21:02:02', 1);_

CREATE TABLE _%%prefix%%plg_cat__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=140 ;_
INSERT INTO _%%prefix%%plg_cat__seq VALUES (139);_



CREATE TABLE %%prefix%%plg_comps (
  mc_id int(11) unsigned NOT NULL default '0',
  fk_module int(11) unsigned NOT NULL default '0',
  fk_type int(11) unsigned NOT NULL default '0',
  compname varchar(40) default NULL,
  func varchar(40) NOT NULL default '',
  status_id int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (mc_id)
);_

INSERT INTO %%prefix%%plg_comps VALUES (1, 8, 300, 'font_size_switch', 'display', 100);_
INSERT INTO %%prefix%%plg_comps VALUES (2, 7, 200, 'objects', 'load', 100);_
INSERT INTO %%prefix%%plg_comps VALUES (3, 5, 200, 'dictionary', 'translate', 100);_
INSERT INTO %%prefix%%plg_comps VALUES (5, 15, 200, 'requestlog', 'log', 100);_

CREATE TABLE _%%prefix%%cat_plg_comp__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=7 ;_
INSERT INTO _%%prefix%%cat_plg_comp__seq VALUES (6);_


CREATE TABLE %%prefix%%plg_dict (
  dict_id int(11) unsigned NOT NULL default '0',
  deftrans varchar(255) NOT NULL default '',
  type_classify_id int(11) unsigned NOT NULL default '0',
  de text,
  en text,
  PRIMARY KEY  (dict_id),
  UNIQUE KEY deftrans (deftrans)
);_

INSERT INTO %%prefix%%plg_dict VALUES (5, 'sr.Hits', 35, 'Aufrufe', 'hits');_
INSERT INTO %%prefix%%plg_dict VALUES (7, 'de', 35, 'Deutsch', 'german');_
INSERT INTO %%prefix%%plg_dict VALUES (8, 'en', 35, 'Englisch', 'english');_
INSERT INTO %%prefix%%plg_dict VALUES (9, 'all', 35, 'Alle', 'all');_
INSERT INTO %%prefix%%plg_dict VALUES (10, 'sr.Author', 35, 'Autor', 'author');_
INSERT INTO %%prefix%%plg_dict VALUES (11, 'sr.NoOfComment', 35, 'Anzahl der Kommentare', 'number of comments');_
INSERT INTO %%prefix%%plg_dict VALUES (12, 'sr.PublishBegin', 35, 'Veröffentlichungsbeginn', 'publication start');_
INSERT INTO %%prefix%%plg_dict VALUES (13, 'sr.PublishEnd', 35, 'Veröffentlichungsende', 'publication end');_
INSERT INTO %%prefix%%plg_dict VALUES (14, 'comments', 35, 'Kommentare', 'comments');_
INSERT INTO %%prefix%%plg_dict VALUES (15, 'noArticles', 35, 'In dieser Rubrik gibt es bis jetzt keine Artikel.', 'There are no articles in this category yet.');_
INSERT INTO %%prefix%%plg_dict VALUES (16, 'sr.CreationDate', 35, 'Erstellungsdatum', 'creation date');_
INSERT INTO %%prefix%%plg_dict VALUES (17, 'sr.Article', 35, 'Artikeltitel', 'article title');_
INSERT INTO %%prefix%%plg_dict VALUES (18, 'articlesurvey', 35, 'Artikelübersicht', 'article survey');_
INSERT INTO %%prefix%%plg_dict VALUES (19, 'sr.CatDesc', 35, 'Rubrikbeschreibung', 'category description');_
INSERT INTO %%prefix%%plg_dict VALUES (21, 'continue', 35, 'weiter', 'continue');_
INSERT INTO %%prefix%%plg_dict VALUES (22, 'NoAccessRight', 35, 'ZUGRIFF VERWEIGERT - Sie haben keine Zugriffsberechtigung', 'ACCESS DENIED - You have no access right');_
INSERT INTO %%prefix%%plg_dict VALUES (93, 'back', 35, 'zurück', 'back');_
INSERT INTO %%prefix%%plg_dict VALUES (94, 'rows', 35, 'Zeilen', 'rows');_
INSERT INTO %%prefix%%plg_dict VALUES (24, 'r.SYSTEMLOG_TRUNCATE', 35, 'Systemprotokoll zurücksetzen/ leeren', 'truncate system logfile');_
INSERT INTO %%prefix%%plg_dict VALUES (25, 'more_w_brackets', 35, '[mehr]', '[more]');_
INSERT INTO %%prefix%%plg_dict VALUES (26, 'sr.Comment', 35, 'Kommentar:', 'comment:');_
INSERT INTO %%prefix%%plg_dict VALUES (27, 'cont.WriteArticle', 35, 'Neuer Beitrag', 'new article');_
INSERT INTO %%prefix%%plg_dict VALUES (28, 'username', 35, 'Benutzername', 'username');_
INSERT INTO %%prefix%%plg_dict VALUES (29, 'password', 35, 'Passwort', 'password');_
INSERT INTO %%prefix%%plg_dict VALUES (30, 'forgot_password', 35, 'Passwort vergessen?', 'password forgotten?');_
INSERT INTO %%prefix%%plg_dict VALUES (31, 'register_now', 35, 'Jetzt Registrieren', 'register now');_
INSERT INTO %%prefix%%plg_dict VALUES (32, 'login', 35, 'Redaktionslogin', 'editor login');_
INSERT INTO %%prefix%%plg_dict VALUES (33, 'backend_login', 35, 'Administrationsbereich', 'Backend login');_
INSERT INTO %%prefix%%plg_dict VALUES (34, 'h.user_agreement', 35, 'Benutzungsbedingungen', 'Usage agreement');_
INSERT INTO %%prefix%%plg_dict VALUES (40, 'moveUp', 35, 'nach oben bewegen', 'move upwards');_
INSERT INTO %%prefix%%plg_dict VALUES (41, 'moveDown', 35, 'nach unten bewegen', 'move downwards');_
INSERT INTO %%prefix%%plg_dict VALUES (42, 'moveLeft', 35, 'nach Links bewegen', 'move left');_
INSERT INTO %%prefix%%plg_dict VALUES (43, 'moveRight', 35, 'nach Rechts bewegen', 'move right');_
INSERT INTO %%prefix%%plg_dict VALUES (44, 'catAlreadyLast', 35, 'ACHTUNG: Diese Rubrik ist bereits der letzte Knoten auf dieser Ebene!', 'ATTENTION: This category is already the last node on the current level!');_
INSERT INTO %%prefix%%plg_dict VALUES (45, 'catAlreadyFirst', 35, 'ACHTUNG: Die Rubrik ist bereits der erste Knoten auf dieser Ebene!', 'ATTENTION: This category is already the first node on the current level!');_
INSERT INTO %%prefix%%plg_dict VALUES (89, 'catAlreadyTop', 35, 'ACHTUNG: Diese Rubrik ist bereits auf der obersten Ebene der Hierarchie!', 'ATTENTION: This category is already on the top level of the hierarchy!');_
INSERT INTO %%prefix%%plg_dict VALUES (46, 'h.MainMenuName', 35, 'Hauptmenü', 'main menu');_
INSERT INTO %%prefix%%plg_dict VALUES (47, 'h.CommentForm', 35, 'Kommentar-Formular', 'comment form');_
INSERT INTO %%prefix%%plg_dict VALUES (48, 'h.systemMsg', 35, 'Systemnachricht', 'systemmessage');_
INSERT INTO %%prefix%%plg_dict VALUES (49, 'dataInsertSuccess', 35, 'Daten erfolgreich eingefügt!', 'Data successfully inserted!');_
INSERT INTO %%prefix%%plg_dict VALUES (50, 'dataInsertFailed', 35, 'FEHLER: Daten konnten nicht eingefügt werden!', 'ERROR: Data could not be inserted!');_
INSERT INTO %%prefix%%plg_dict VALUES (54, 'h.ContentForm', 35, 'Beitrags-Formular', 'Content form');_
INSERT INTO %%prefix%%plg_dict VALUES (57, 'user_agreement', 35, '<span>Die Administratoren dieser Plattform bemühen sich, Beiträge mit\\r\\nfragwürdigem Inhalt so schnell wie möglich zu bearbeiten bzw. zu löschen,\\r\\naber es ist nicht möglich, jede einzelne Nachricht zu überprüfen.\\r\\nSie bestätigen mit dem Absenden dieser Einverständniserklärung und der\\r\\nRegistrierung auf dieser Seite, dass Sie die nachfolgenden Konditionen gelesen und\\r\\nals bindend akzeptiert haben:</span>\\r\\n\\r\\njeder Beitrag in diesem Forum spiegelt die Meinung des Urhebers und nicht die der Betreiber\\r\\noder Administratoren wider\\r\\ndie Administratoren, Moderatoren und Betreiber dieser Plattform können nicht für die Inhalte anderer Beiträge als ihrer eigenen\\r\\nBeiträge verantwortlich gemacht werden\\r\\nSie verpflichten sich, keine beleidigenden, obszönen, vulgären, verleumdenden,\\r\\ngewaltverherrlichenden oder aus anderen Gründen strafbaren Inhalte auf dieser\\r\\nSeite zu veröffentlichen\\r\\nSie verpflichten sich weiterhin durch Ihre Beiträge nicht gegen die\\r\\nNetikette\\r\\nzu verstoßen!\\r\\nSie erlauben den Betreibern,\\r\\nAdministratoren und Moderatoren dieser Seite das Recht ein, Beiträge nach\\r\\neigenem Ermessen zu entfernen, zu bearbeiten, zu verschieben oder zu sperren.\\r\\nSie stimmen zu, dass die im Rahmen der Registrierung erhobenen Daten in einer\\r\\nDatenbank gespeichert werden. (Diese Daten dienen lediglich der Authentifizierung Ihrer Person bei der Anmeldung und Ihren Aktionen auf der Plattform, sowie zur Kommunikation zwischen Ihnen und den Betreibern der Plattform. Diese Daten werden keinem Dritten zugänglich gemacht!)\\r\\n<span>\\r\\nVerstöße gegen diese Regeln führen zu sofortiger und\\r\\npermanenter Sperrung! Die Betreiber dieser Seite behalten sich vor,\\r\\nVerbindungsdaten u. ä. an die strafverfolgenden Behörden\\r\\nweiterzugeben. </span>\\r\\n\\r\\n<span>Dieses System verwendet Cookies, um Informationen auf deinem Computer zu\\r\\nspeichern. Diese Cookies enthalten keine der oben angegebenen Informationen,\\r\\nsondern dienen ausschließlich deinem Komfort. Deine Mail-Adresse wird nur zur\\r\\nBestätigung der Registrierung und ggf. zum Versand eines neuen Passwortes\\r\\nverwendet.</span>\\r\\n\\r\\n<span>Durch das Abschließen der Registrierung stimmst du diesen\\r\\nNutzungsbedingungen zu.</span>', 'no text yet');_
INSERT INTO %%prefix%%plg_dict VALUES (58, 'h.SystemMenuName', 35, 'Systemmenü', 'system menu');_
INSERT INTO %%prefix%%plg_dict VALUES (60, 'h.moveCat', 35, 'Rubriken verschieben', 'Move categories');_
INSERT INTO %%prefix%%plg_dict VALUES (62, 'cont.EditArticle', 35, 'Beitrag editieren', 'edit article');_
INSERT INTO %%prefix%%plg_dict VALUES (63, 'h.CommentHeader', 35, 'Kommentare', 'Comments');_
INSERT INTO %%prefix%%plg_dict VALUES (64, 'om.newWidth', 35, 'Neue Breite: ', 'New Width: ');_
INSERT INTO %%prefix%%plg_dict VALUES (65, 'om.newHeight', 35, 'Neue Höhe: ', 'New Height: ');_
INSERT INTO %%prefix%%plg_dict VALUES (66, 'om.changeImgSize', 35, 'Bildgröße anpassen', 'adjust image size');_
INSERT INTO %%prefix%%plg_dict VALUES (67, 'om.workSmallImgSize', 35, 'Bildgröße des Vorschaubildes bearbeiten', 'edit small image size');_
INSERT INTO %%prefix%%plg_dict VALUES (68, 'om.workObjectSize', 35, 'Objektgröße bearbeiten', 'Edit object size');_
INSERT INTO %%prefix%%plg_dict VALUES (69, 'insert', 35, 'einfügen', 'insert');_
INSERT INTO %%prefix%%plg_dict VALUES (70, 'om.imgAutoCreated', 35, 'Dieses Vorschau-Bild wurde durch ihre Eingaben automatisch erstellt.', 'This image has been automatically created from your input.');_
INSERT INTO %%prefix%%plg_dict VALUES (71, 'om.objList', 35, 'Objektliste', 'Object list');_
INSERT INTO %%prefix%%plg_dict VALUES (72, 'choose', 35, 'auswählen', 'choose');_
INSERT INTO %%prefix%%plg_dict VALUES (76, 'sr.Desc', 35, 'Beschreibung', 'description');_
INSERT INTO %%prefix%%plg_dict VALUES (77, 'om.Object', 35, 'Objekt', 'Object');_
INSERT INTO %%prefix%%plg_dict VALUES (78, 'om.ImageSizeOk', 35, 'Ist die Größe des Bildes in Ordnung?', 'Is the size of the image ok?');_
INSERT INTO %%prefix%%plg_dict VALUES (81, 'qf.required', 35, 'markiert Pflichtfelder', 'denotes required fields');_
INSERT INTO %%prefix%%plg_dict VALUES (82, 'qf.requiredTT', 35, 'Pflichtfeld', 'required field');_
INSERT INTO %%prefix%%plg_dict VALUES (101, 'user.username', 35, 'Benutzername', 'username');_
INSERT INTO %%prefix%%plg_dict VALUES (84, 'yes', 35, 'ja', 'yes');_
INSERT INTO %%prefix%%plg_dict VALUES (85, 'no', 35, 'nein', 'no');_
INSERT INTO %%prefix%%plg_dict VALUES (86, 'h.UserMenuName', 35, 'Benutzermenü', 'Users menu');_
INSERT INTO %%prefix%%plg_dict VALUES (87, 'currentlyOpen', 35, 'Aktuell geöffnet', 'currently opened');_
INSERT INTO %%prefix%%plg_dict VALUES (88, 'RightNotInDb', 35, 'FEHLER: Angegebenes Recht \"%%1%%\" konnte nicht in Datenbank gefunden werden.<br/>', 'ERROR: Right \\"%%1%%\\" could not be found in database.');_
INSERT INTO %%prefix%%plg_dict VALUES (91, 'submit', 35, 'Abschicken', 'Submit');_
INSERT INTO %%prefix%%plg_dict VALUES (92, 'dict.addTranslation', 35, 'Übersetzung hinzufügen', 'Add translation');_
INSERT INTO %%prefix%%plg_dict VALUES (95, 'further', 35, 'weiter', 'further');_
INSERT INTO %%prefix%%plg_dict VALUES (35, 'h.CategoryEdit', 35, 'Rubrik bearbeiten', 'Edit category');_
INSERT INTO %%prefix%%plg_dict VALUES (96, 'h.UserProfileEdit', 35, 'Benutzerprofil bearbeiten', 'Edit user profile');_
INSERT INTO %%prefix%%plg_dict VALUES (102, 'user.pword', 35, 'Passwort', 'password');_
INSERT INTO %%prefix%%plg_dict VALUES (103, 'user.pword_again', 35, 'Passwort wiederholen', 'password again');_
INSERT INTO %%prefix%%plg_dict VALUES (104, 'user.passwort', 35, 'Altes Passwort', 'old password');_
INSERT INTO %%prefix%%plg_dict VALUES (105, 'user.vorname', 35, 'Vorname', 'Firstname');_
INSERT INTO %%prefix%%plg_dict VALUES (106, 'user.nachname', 35, 'Nachname', 'Lastname');_
INSERT INTO %%prefix%%plg_dict VALUES (107, 'user.about_me', 35, 'Über mich/ Selbstdarstellung', 'about me');_
INSERT INTO %%prefix%%plg_dict VALUES (108, 'user.homepage', 35, 'Internetseite', 'Homepage');_
INSERT INTO %%prefix%%plg_dict VALUES (109, 'user.company', 35, 'Firma', 'Company');_
INSERT INTO %%prefix%%plg_dict VALUES (110, 'user.address', 35, 'Adresse', 'Address');_
INSERT INTO %%prefix%%plg_dict VALUES (111, 'user.postzip', 35, 'Postleitzahl', 'Postal Code');_
INSERT INTO %%prefix%%plg_dict VALUES (112, 'user.city', 35, 'Stadt/ Ort', 'City');_
INSERT INTO %%prefix%%plg_dict VALUES (113, 'user.country', 35, 'Land', 'State/ Country');_
INSERT INTO %%prefix%%plg_dict VALUES (114, 'user.fk_fav_menu', 35, 'Startrubrik nach Login', 'Starting category after login');_
INSERT INTO %%prefix%%plg_dict VALUES (115, 'user.fav_layout', 35, 'Favorisiertes Seitenlayout', 'Favourite layout');_
INSERT INTO %%prefix%%plg_dict VALUES (116, 'user.last_ip', 35, 'letztbekannte IP-Adresse', 'Last known IP-address');_
INSERT INTO %%prefix%%plg_dict VALUES (117, 'user.last_login', 35, 'Datum des letzten Logins', 'Date of last login');_
INSERT INTO %%prefix%%plg_dict VALUES (118, 'user.akt_login', 35, 'Datum des jetzigen Logins', 'Current login date');_
INSERT INTO %%prefix%%plg_dict VALUES (119, 'user.root_flag', 35, 'Berechtigungsprüfung ausschalten? (root flag)', 'evade authorization check? (root flag)');_
INSERT INTO %%prefix%%plg_dict VALUES (120, 'user.fk_zusatzrecht', 35, 'Zusätzliches Einzelrecht, das unabhängig von den Gruppenrechten ist', 'Additional right which is indepent from the group rights');_
INSERT INTO %%prefix%%plg_dict VALUES (121, 'user.login_tries', 35, 'Gescheiterte Login-Versuche seit dem letzten Login', 'Failed login tries since last login');_
INSERT INTO %%prefix%%plg_dict VALUES (122, 'user.time2login', 35, 'Zeitpunkt ab dem der Account wieder für einen Login freigeschaltet ist (Account-Sperrung)', 'Timestamp as of which a login process can take place again (account blocking)');_
INSERT INTO %%prefix%%plg_dict VALUES (123, 'user.fk_aenderer', 35, 'Profil zuletzt geändert von', 'Profile last changed by');_
INSERT INTO %%prefix%%plg_dict VALUES (124, 'user.change_date', 35, 'Profil zuletzt geändert am', 'Profile last changed at');_
INSERT INTO %%prefix%%plg_dict VALUES (125, 'user.fk_anleger', 35, 'Profil angelegt von', 'Profile created by');_
INSERT INTO %%prefix%%plg_dict VALUES (126, 'user.create_date', 35, 'Profil angelegt am', 'Profile created at');_
INSERT INTO %%prefix%%plg_dict VALUES (127, 'user_only', 35, 'Nur für angemeldete Benutzer', 'Only for logged in users');_
INSERT INTO %%prefix%%plg_dict VALUES (128, 'commentable', 35, 'kommentierbar', 'commentable');_
INSERT INTO %%prefix%%plg_dict VALUES (129, 'sr.Version', 35, 'Version', 'version');_
INSERT INTO %%prefix%%plg_dict VALUES (131, 'srInfo', 35, 'Zusätzliche Beschreibung für Screenreader', 'Additional screenreader information');_
INSERT INTO %%prefix%%plg_dict VALUES (133, 'articleHistoryOf', 35, 'Artikel-Historie von ', 'Article History of ');_
INSERT INTO %%prefix%%plg_dict VALUES (134, 'articleHistory', 35, 'Historie des Artikels', 'History of this article');_
INSERT INTO %%prefix%%plg_dict VALUES (135, 'h.UserProfileOf', 35, 'Benutzerprofil von ', 'Userprofile of ');_
INSERT INTO %%prefix%%plg_dict VALUES (136, 'edit', 35, 'bearbeiten', 'edit');_
INSERT INTO %%prefix%%plg_dict VALUES (137, 'dictionary', 35, 'Wörterbuch', 'dictionary');_
INSERT INTO %%prefix%%plg_dict VALUES (138, 'om.choose_object', 35, 'Objekt auswählen', 'Choose object');_
INSERT INTO %%prefix%%plg_dict VALUES (139, 'om.upload', 35, 'Objekt hochladen', 'Upload object');_
INSERT INTO %%prefix%%plg_dict VALUES (140, 'om.upload_failed', 35, 'Dateiupload ist fehlgeschlagen. Bitte versuchen Sie es erneut.', 'File upload failed! Please try again.');_
INSERT INTO %%prefix%%plg_dict VALUES (141, 'om.ratio', 35, 'Verhältnis zur Originalgröße', 'Ratio according to original size');_
INSERT INTO %%prefix%%plg_dict VALUES (142, 'om.workOrgImgSize', 35, 'Bildgröße des Originalbildes ändern', 'change size of original image');_
INSERT INTO %%prefix%%plg_dict VALUES (143, 'om.original_size', 35, 'Originalgröße: ', 'Original size: ');_
INSERT INTO %%prefix%%plg_dict VALUES (144, 'om.new_image', 35, 'Neues Bild: ', 'New image: ');_
INSERT INTO %%prefix%%plg_dict VALUES (145, 'om.resized_image', 35, 'Dies ist das Bild, das nach dem von Ihnen eingegebenen Verhältnis in der Größe verändert wurde', 'This is the image resized by the inserted ratio');_
INSERT INTO %%prefix%%plg_dict VALUES (147, 'object_id', 35, 'Objekt (Bild)', 'Object (Image)');_
INSERT INTO %%prefix%%plg_dict VALUES (149, 'dict.to_be_logged', 34, 'Log relevant', 'log relevant');_
INSERT INTO %%prefix%%plg_dict VALUES (150, 'dict.deftrans', 35, 'Standard-übersetzung', 'Default translation');_
INSERT INTO %%prefix%%plg_dict VALUES (151, 'dict.type_classify_id', 35, 'Art', 'type');_
INSERT INTO %%prefix%%plg_dict VALUES (152, 'cat.categoryname', 35, 'Rubrikname', 'category name');_
INSERT INTO %%prefix%%plg_dict VALUES (153, 'cat.techname', 35, 'Technischer Rubrikname (ohne Umlaute, Leer- und Sonderzeichen)', 'Technical category name (without spaces and special chars)');_
INSERT INTO %%prefix%%plg_dict VALUES (154, 'cat.categorylink_title', 35, 'Beschreibung zum Rubriklink (title-Tag)', 'Description of category link (title-tag)');_
INSERT INTO %%prefix%%plg_dict VALUES (155, 'cat.root_id', 35, 'Elternknoten-ID', 'Parent nodes id');_
INSERT INTO %%prefix%%plg_dict VALUES (156, 'cat.user_only', 35, 'Soll diese Rubrik nur für angemeldete Benutzer sichtbar und zugänglich sein?', 'Shall this category only be accessible to logged in users?');_
INSERT INTO %%prefix%%plg_dict VALUES (157, 'cat.viewable4all', 35, 'Soll diese Rubrik für alle Benutzer sichtbar sein?', 'Viewable for all users?');_
INSERT INTO %%prefix%%plg_dict VALUES (158, 'cat.writeable4all', 35, 'Für alle Benutzer schreibbar?', 'writable for all users?');_
INSERT INTO %%prefix%%plg_dict VALUES (159, 'cat.commentable', 35, 'Sollen die Inhalte dieser Rubrik kommentierbar sein?', 'Shall the contents of this category be commentable?');_
INSERT INTO %%prefix%%plg_dict VALUES (160, 'cat.show_cat_desc', 35, 'Sollen die Rubrikbeschreibung angezeigt werden?', 'Shall the category description be shown?');_
INSERT INTO %%prefix%%plg_dict VALUES (161, 'cat.show_pathway', 35, 'Sollen der \\"Pfad zur Rubrik\\" angezeigt werden?', 'Shall the \\"pathway to this category\\" be shown?');_
INSERT INTO %%prefix%%plg_dict VALUES (162, 'cat.show_opt_plugins', 35, 'Sollen die optionalen Plugins in dieser Rubrik angezeigt werden?', 'Shall the optional plugins be shown in this category?');_
INSERT INTO %%prefix%%plg_dict VALUES (163, 'cat.type', 35, 'Art', 'type');_
INSERT INTO %%prefix%%plg_dict VALUES (164, 'cat.content_order_by', 35, '(Nur für Art \\"Artikelliste\\") Artikel sortieren nach', '(Only for type \\"article_list\\") order articles by');_
INSERT INTO %%prefix%%plg_dict VALUES (165, 'cat.sort_direction', 35, '(Nur für Art \\"Artikelliste\\") Artikelsortierrichtung', '(Only for type \\"article_list\\") sort direction for articles');_
INSERT INTO %%prefix%%plg_dict VALUES (166, 'cat.description', 35, 'Rubrikbeschreibung', 'category description');_
INSERT INTO %%prefix%%plg_dict VALUES (167, 'cat.accesskey', 35, 'Schnellwahltaste (Accesskey)', 'accesskey');_
INSERT INTO %%prefix%%plg_dict VALUES (168, 'cat.meta_description', 35, 'Rubrikbeschreibung (für Suchmaschinen)', 'category description (for search engines)');_
INSERT INTO %%prefix%%plg_dict VALUES (169, 'cat.meta_keywords', 35, 'Suchbegriffe für diese Rubrik (für Suchmaschinen)', 'search keywords for this category (for search engines)');_
INSERT INTO %%prefix%%plg_dict VALUES (170, 'cat.additional_css', 35, 'Zusätzliche Cascading Stylesheet-Angaben (CSS)', 'Additional Cascading Stylesheet (CSS) definitions ');_
INSERT INTO %%prefix%%plg_dict VALUES (171, 'cat.status_id', 35, 'Status', 'status');_
INSERT INTO %%prefix%%plg_dict VALUES (172, 'cat.publishing_date', 35, 'Veröffentlichungsdatum', 'Publishing date');_
INSERT INTO %%prefix%%plg_dict VALUES (173, 'cat.edit_date', 35, 'Bearbeitungsdatum', 'editing date');_
INSERT INTO %%prefix%%plg_dict VALUES (174, 'cat.editor_id', 35, 'Bearbeiter', 'editor');_
INSERT INTO %%prefix%%plg_dict VALUES (175, 'sr.Pathway', 35, 'Sie sind hier: ', 'You are here: ');_
INSERT INTO %%prefix%%plg_dict VALUES (176, 'comm.thx4Comment', 35, 'Danke für Ihr Kommentar.', 'Thank your for your comment.');_
INSERT INTO %%prefix%%plg_dict VALUES (177, 'backToArticle', 35, 'Zurück zum Artikel', 'Back to the article');_
INSERT INTO %%prefix%%plg_dict VALUES (178, 'comm.insert_failed', 35, 'FEHLER: Einfügen des Kommentars ist fehlgeschlagen!', 'ERROR: Insertion of comment failed.');_
INSERT INTO %%prefix%%plg_dict VALUES (179, 'user.public_fields', 35, 'Öffentlich sichtbare Felder', 'Fields viewable to public');_
INSERT INTO %%prefix%%plg_dict VALUES (180, 'user.generally_notice_on_comment', 35, 'Generell benachrichtigen, wenn Kommentar zu Ihren Artikeln eingeht?', 'Generally notice you if comment on your articles is posted?');_
INSERT INTO %%prefix%%plg_dict VALUES (181, 'user.generally_notice_on_answer_to_comment', 35, 'Generell benachrichtigen, wenn Antwort auf Ihre Kommentare eingeht?', 'Generally notice you if answer to your comments is posted?');_
INSERT INTO %%prefix%%plg_dict VALUES (182, 'cat.confirm_text', 35, 'Möchten Sie die folgende Rubrik wirklich löschen?', 'Do you really want to delete the following category? ');_
INSERT INTO %%prefix%%plg_dict VALUES (183, 'accept', 35, 'Akzeptieren', 'accept');_
INSERT INTO %%prefix%%plg_dict VALUES (184, 'reject', 35, 'Ablehnen', 'reject');_
INSERT INTO %%prefix%%plg_dict VALUES (185, 'to_top', 35, 'nach oben', 'go to top');_
INSERT INTO %%prefix%%plg_dict VALUES (186, 'to_next_article', 35, 'zum nächsten Beitrag', 'to the next article');_
INSERT INTO %%prefix%%plg_dict VALUES (187, 'maxlength', 35, 'Maximallänge (X Zeichen): ', 'Maximum length (X chars): ');_
INSERT INTO %%prefix%%plg_dict VALUES (188, 'user.skype_username', 35, 'Skype-Benutzername', 'skype username');_
INSERT INTO %%prefix%%plg_dict VALUES (189, 'minlength', 35, 'Mindestlänge (X Zeichen)', 'Minimum length (X chars)');_
INSERT INTO %%prefix%%plg_dict VALUES (190, 'user.rules.username_regex', 35, 'Benutzername darf nur aus dne Zeichen a-z, A-Z, 0-9 und dem Unterstrich bestehen. Leer- und Sonderzeichen sind nicht erlaubt!', 'Username must only consist of the characters a-z, A-Z, 0-9 and the underscore. Spaces and special chars are not allowed!');_
INSERT INTO %%prefix%%plg_dict VALUES (191, 'user.email', 35, 'E-Mail-Adresse', 'e-mail address');_
INSERT INTO %%prefix%%plg_dict VALUES (192, 'user.telefon', 35, 'Telefonnummer', 'telephone number');_
INSERT INTO %%prefix%%plg_dict VALUES (193, 'user.fax', 35, 'Faxnummer', 'fax number');_
INSERT INTO %%prefix%%plg_dict VALUES (200, 'cat.use_ssl', 35, 'SSL (Secure Socket Layer)-Verschlüsselung verwenden?', 'Use SSL (Secure Socket Layer) encryption?');_
INSERT INTO %%prefix%%plg_dict VALUES (201, 'delete', 35, 'löschen', 'delete');_
INSERT INTO %%prefix%%plg_dict VALUES (203, 'cancel', 35, 'Abbrechen', 'Cancel');_
INSERT INTO %%prefix%%plg_dict VALUES (204, 'reset', 35, 'zurücksetzen', 'reset');_
INSERT INTO %%prefix%%plg_dict VALUES (205, 'really_delete', 35, 'Möchten Sie die Elemente mit den folgenden IDs wirklich löschen?', 'Do you really want to delete the elements with the following ids?');_
INSERT INTO %%prefix%%plg_dict VALUES (206, 'dict.h.deleteEntries', 35, 'Wörterbucheinträge löschen', 'Delete dictionary entries');_
INSERT INTO %%prefix%%plg_dict VALUES (207, 'dict.heading', 35, 'Wörterbuch bearbeiten', 'Work on Dictionary');_
INSERT INTO %%prefix%%plg_dict VALUES (210, 'cat.h.cat_move', 35, 'Rubrik verschieben', 'Move category');_
INSERT INTO %%prefix%%plg_dict VALUES (211, 'om.insert_notice', 35, 'HINWEIS: Der Dateiname darf nur die Zeichen a-z, A-Z, 0-9, Punkt (.), Unterstrich (_) und Bindestrich (-) enthalten! Außerdem ist die Uploadmenge auf 2MB begrenzt. Sollte Ihre Datei größer sein, wird der Upload automatisch abgebrochen.', 'NOTICE: The filename must only consist of the chars a-z, A-Z, 0-9, dot (.), underscore (_) and hyphen (-)! Furthermore the upload size of files is limited to 2 MB. If your file is larger than this, the upload will be cancelled automatically.');_
INSERT INTO %%prefix%%plg_dict VALUES (212, 'om.file_already_exists', 35, 'Datei existiert bereits!', 'File already exists!');_
INSERT INTO %%prefix%%plg_dict VALUES (213, 'om.change_preview_size', 35, 'Vorschaubildgröße ändern', 'Change preview size');_
INSERT INTO %%prefix%%plg_dict VALUES (214, 'om.change_original_size', 35, 'Originalbildgröße ändern', 'Change original size');_
INSERT INTO %%prefix%%plg_dict VALUES (215, 'om.h.list', 35, 'Dateiliste', 'Filelist');_
INSERT INTO %%prefix%%plg_dict VALUES (216, 'om.object_id', 35, 'ID', 'ID');_
INSERT INTO %%prefix%%plg_dict VALUES (217, 'om.object_filename', 35, 'Dateiname', 'Filename');_
INSERT INTO %%prefix%%plg_dict VALUES (218, 'om.object_folder', 35, 'Verzeichnis', 'Folder');_
INSERT INTO %%prefix%%plg_dict VALUES (239, 'om.object_shortdesc', 35, 'Kurzbeschreibung', 'Short description');_
INSERT INTO %%prefix%%plg_dict VALUES (220, 'om.object_longdesc', 35, 'Langbeschreibung', 'Long description');_
INSERT INTO %%prefix%%plg_dict VALUES (221, 'om.object_type', 35, 'Dateityp', 'Filetype');_
INSERT INTO %%prefix%%plg_dict VALUES (222, 'om.object_width', 35, 'Breite', 'Width');_
INSERT INTO %%prefix%%plg_dict VALUES (223, 'om.object_height', 35, 'Höhe', 'Height');_
INSERT INTO %%prefix%%plg_dict VALUES (224, 'om.object_smallimage_filename', 35, 'Vorschaubild-Dateiname ', 'Previewimage filename');_
INSERT INTO %%prefix%%plg_dict VALUES (225, 'om.object_origin', 35, 'Herkunft', 'Origin');_
INSERT INTO %%prefix%%plg_dict VALUES (226, 'om.object_author', 35, 'Autor', 'Author');_
INSERT INTO %%prefix%%plg_dict VALUES (227, 'om.object_created', 35, 'Erstellungsdatum', 'Creationdate');_
INSERT INTO %%prefix%%plg_dict VALUES (228, 'om.object_importdate', 35, 'Importdatum', 'Importdate');_
INSERT INTO %%prefix%%plg_dict VALUES (229, 'om.object_import_user', 35, 'Importiert von', 'Imported by');_
INSERT INTO %%prefix%%plg_dict VALUES (230, 'image', 35, 'Bild', 'Image');_
INSERT INTO %%prefix%%plg_dict VALUES (231, 'om.save_as', 35, 'Objekt speichern als (Neuer Dateiname)', 'Save object as (new filename)');_
INSERT INTO %%prefix%%plg_dict VALUES (232, 'om.delete_following_files', 35, 'Bevor Sie diesen Vorgang fortsetzen, sollten Sie die folgenden Dateien aus dem Dateisystem löschen. \r\nAnsonsten werden diese über einen automatischen Einlesevorgang wieder in die Datenbank eingepflegt. \r\n<br/>\r\nBitte löschen Sie die folgenden Dateien, bevor Sie \\"weiter\\" drücken:', 'You should now delete the following files from your filesystem. Otherwise the include routine will automatically re-add them to the database. \r\n<br/>\r\nPlease delete the following files before you press \\"continue\\":');_
INSERT INTO %%prefix%%plg_dict VALUES (234, 'logged_in_as', 35, 'Angemeldet als', 'Logged in as');_
INSERT INTO %%prefix%%plg_dict VALUES (236, 'show_hide_menues', 35, 'Menüs ein-/ausblenden', 'blend in/ out menues');_
INSERT INTO %%prefix%%plg_dict VALUES (238, 'om.file_not_exists', 35, 'Die angegebene Datei existiert nicht!', 'The denoted file does not exist!');_
INSERT INTO %%prefix%%plg_dict VALUES (237, 'om.h.deleteEntries', 35, 'Objekte löschen', 'Delete objects');_
INSERT INTO %%prefix%%plg_dict VALUES (241, 'cat.lft', 0, 'lft', 'lft');_
INSERT INTO %%prefix%%plg_dict VALUES (240, 'cat.rgt', 0, 'rgt', 'rgt');_
INSERT INTO %%prefix%%plg_dict VALUES (243, 'apply', 35, 'Übernehmen', 'Apply');_
INSERT INTO %%prefix%%plg_dict VALUES (244, 'setVersionActive', 35, 'Version aktiv setzen', 'Set version active');_
INSERT INTO %%prefix%%plg_dict VALUES (245, 'return_to_prior_page', 35, 'Nach Login zu vorheriger Seite zurückkehren?', 'Return to prior page after login?');_
INSERT INTO %%prefix%%plg_dict VALUES (246, 'to_category', 35, 'Zur übergeordneten Rubrik', 'To the parent category');_
INSERT INTO %%prefix%%plg_dict VALUES (247, 'showUserProfile', 35, 'Öffne Benutzerprofil von', 'Show user profile of ');_
INSERT INTO %%prefix%%plg_dict VALUES (248, 'entries', 35, 'Einträge', 'Entries');_
INSERT INTO %%prefix%%plg_dict VALUES (249, 'of', 35, 'von', 'of');_
INSERT INTO %%prefix%%plg_dict VALUES (256, 'rights.addRight', 35, 'Systemrechte hinzufügen', 'Add system right');_
INSERT INTO %%prefix%%plg_dict VALUES (257, 'rights.heading', 35, 'Systemrechte bearbeiten', 'Work on system rights');_
INSERT INTO %%prefix%%plg_dict VALUES (258, 'rights.h.deleteEntries', 35, 'Recht(e) löschen', 'Delete right(s)');_
INSERT INTO %%prefix%%plg_dict VALUES (259, 'rights.fk_dict_id', 35, 'Übersetzung', 'Translation');_
INSERT INTO %%prefix%%plg_dict VALUES (260, 'rights.fk_syscat_id', 35, 'Systembereich', 'System category');_
INSERT INTO %%prefix%%plg_dict VALUES (261, 'rights.rightname', 35, 'Rechtname', 'rightname');_
INSERT INTO %%prefix%%plg_dict VALUES (262, 'rights.right_id', 35, 'ID', 'ID');_
INSERT INTO %%prefix%%plg_dict VALUES (263, 'cat.fk_view_right', 35, 'Welches Recht erlaubt es einem Benutzer diese Rubrik zu sehen?', 'Which right enables a user to view this category?');_
INSERT INTO %%prefix%%plg_dict VALUES (264, 'cat.fk_edit_right', 35, 'Welches Recht erlaubt es einem Benutzer diese Rubrik zu bearbeiten?', 'Which right enables a user to edit this category?');_
INSERT INTO %%prefix%%plg_dict VALUES (265, 'cat.fk_delete_right', 35, 'Welches Recht erlaubt es einem Benutzer diese Rubrik zu löschen?', 'Which right enables a user to delete this category?');_
INSERT INTO %%prefix%%plg_dict VALUES (266, 'user.realname', 35, 'Realer Name', 'realname');_
INSERT INTO %%prefix%%plg_dict VALUES (267, 'user.h.deleteEntries', 35, 'Benutzer löschen', 'Delete users');_
INSERT INTO %%prefix%%plg_dict VALUES (148, 'text', 35, 'Text', 'text');_
INSERT INTO %%prefix%%plg_dict VALUES (269, 'groups.editUserGroupAssoc', 35, 'Benutzer Gruppe zuordnen', 'Associate users to groups');_
INSERT INTO %%prefix%%plg_dict VALUES (270, 'groups.editGroupRightAssoc', 35, 'Gruppen Rechte zuordnen', 'Associate rights to groups');_
INSERT INTO %%prefix%%plg_dict VALUES (271, 'h.CatAdd', 35, 'Rubrik hinzufügen', 'Add category');_
INSERT INTO %%prefix%%plg_dict VALUES (272, 'groups.groupname', 35, 'Gruppenname', 'Groupname');_
INSERT INTO %%prefix%%plg_dict VALUES (273, 'groups.position', 35, 'Position', 'position');_
INSERT INTO %%prefix%%plg_dict VALUES (274, 'groups.fk_grouptype_id', 35, 'Gruppentyp', 'grouptype');_
INSERT INTO %%prefix%%plg_dict VALUES (275, 'groups.fk_dict_id', 35, 'Übersetzung', 'translation');_
INSERT INTO %%prefix%%plg_dict VALUES (276, 'groups.heading', 35, 'Systemgruppen verwalten', 'Administrate system groups');_
INSERT INTO %%prefix%%plg_dict VALUES (277, 'groups.addGroup', 35, 'Systemgruppe hinzufügen', 'Add system group');_
INSERT INTO %%prefix%%plg_dict VALUES (278, 'h.AdminMenuName', 35, 'Administrationsmenü', 'Administration menu');_
INSERT INTO %%prefix%%plg_dict VALUES (279, 'cat.icon_src', 35, 'Dateipfad zum Menüicon (optional)', 'Path to a menuicon image (optional)');_
INSERT INTO %%prefix%%plg_dict VALUES (280, 'Preview', 35, 'Vorschau', 'Preview');_
INSERT INTO %%prefix%%plg_dict VALUES (281, 'user.user_id', 35, 'BenutzerID', 'User ID');_
INSERT INTO %%prefix%%plg_dict VALUES (283, 'importedBy', 35, 'Importiert von', 'Imported by');_
INSERT INTO %%prefix%%plg_dict VALUES (284, 'importedDate', 35, 'Importdatum', 'Importdate');_
INSERT INTO %%prefix%%plg_dict VALUES (285, 'user.addUser', 35, 'Benutzer hinzufügen', 'Add user');_
INSERT INTO %%prefix%%plg_dict VALUES (286, 'changeStatus', 35, 'Status ändern', 'Change status');_
INSERT INTO %%prefix%%plg_dict VALUES (287, 'save', 35, 'Speichern', 'Save');_
INSERT INTO %%prefix%%plg_dict VALUES (288, 'function deactivated', 35, 'Diese Funktion ist zur Zeit deaktiviert!', 'This function has been deactivated!');_
INSERT INTO %%prefix%%plg_dict VALUES (289, 'cont.categoryname', 35, 'Rubrik', 'category');_
INSERT INTO %%prefix%%plg_dict VALUES (290, 'cont.heading', 35, 'Überschrift', 'heading');_
INSERT INTO %%prefix%%plg_dict VALUES (291, 'cont.status_id', 35, 'Status', 'status');_
INSERT INTO %%prefix%%plg_dict VALUES (292, 'cont.publish_end', 35, 'Veröffentlichungsende', 'publishing end');_
INSERT INTO %%prefix%%plg_dict VALUES (293, 'cont.username', 35, 'Autor', 'author');_
INSERT INTO %%prefix%%plg_dict VALUES (294, 'cont.created', 35, 'Erstellungsdatum', 'creation date');_
INSERT INTO %%prefix%%plg_dict VALUES (295, 'user.app_heading', 35, 'Benutzerverwaltung', 'UserManager');_
INSERT INTO %%prefix%%plg_dict VALUES (296, 'cat.move_direction', 35, 'Bewegungsrichtung', 'Movement direction');_
INSERT INTO %%prefix%%plg_dict VALUES (297, 'in_progress', 35, 'In Bearbeitung', 'In progress');_
INSERT INTO %%prefix%%plg_dict VALUES (298, 'deactivated', 35, 'Deaktiviert', 'Deactivated');_
INSERT INTO %%prefix%%plg_dict VALUES (299, 'published', 35, 'Veröffentlicht', 'Published');_
INSERT INTO %%prefix%%plg_dict VALUES (300, 'to_be_approved', 35, 'Wartet auf Freigabe', 'To be approved');_
INSERT INTO %%prefix%%plg_dict VALUES (301, 'editor_syntax_hints', 35, '<p style=\\"font-size:1.1em\\">Die folgenden Befehle können Sie verwenden um Ihre Texte zu formatieren oder Bilder und Links einzubinden:</p>\r\n<dl>\r\n<dt>Links</dt>\r\n<dd>Normaler Link: http://ein.normaler-link.de<br/>\r\nLink mit Linktext: [http://ein.normaler-link.de Text zu diesem Link]<br/>\r\nLink auf andere Rubrik: [/rubrikname/ Text zu diesem Link]</dd>\r\n\r\n<dt>Bilder einbinden</dt>\r\n<dd>Vorschaubild aus Dateimanager mit Ausrichtung (Links vom Text) einbinden: [[FILETHUMB:MeinBild|float:left]]<br/>\r\nVorschaubild aus Dateimanager mit 10px Abstand zum Text) einbinden: [[FILETHUMB:MeinBild|margin:10px]]<br/>\r\nVorschaubild aus Dateimanager mit Ausrichtung (rechts) und Abstand einbinden: [[FILETHUMB:MeinBild|float:right; margin:10px]]<br/>\r\nEin beliebiges Bild mit Ausrichtung und Abstand einbinden: [[IMG:http://www.meine-seite.de/meinbild.jpg|Beschreibung des Bildes|float:left; margin:1em]]</dd>\r\n\r\n<dt>Text formatiert darstellen</dt>\r\n<dd>**fett gedruckter Text**<br/>\r\n''''kursiv gedruckter Text''''<br/>\r\n''''''''''kursiv und fett gedruckter Text''''''''''</dd>\r\n\r\n<dt>Unter-Überschriften einbauen</dt>\r\n<dd>==große Überschrift==<br/>\r\n===kleine Überschrift===</dd>\r\n\r\n<dt>Zitate hervorheben</dt>\r\n<dd>??ein Zitat??<br/>\r\n:::ein zitierter Text:::</dd>\r\n\r\n<dt>Quellcode hervorheben</dt>\r\n<dd>[[code][ // Quellcode - <br/>\r\n// auch mehrzeilig]]</dd>\r\n</dl>', 'The following markup can be used in textfields:<br/>\r\n<ul>\r\n<li>http://a.common-link.com</li>\r\n<li>[http://a.common-link.com with a different link text]</li>\r\n<li>[[img]/objects/getthumb.my-image-in-filemanager.html| with alternative text]</li>\r\n<li>[[img]/objects/get.my-file-in-filemanager.html| with alternative download text]</li>\r\n<li>[[img]http://my.url.com/to-an/external/image.jpg|with alternative text]</li>\r\n<li>**bold printed text**</li>\r\n<li>''''emphasied/ italic printed text''''</li>\r\n<li>''''''''''italic and bold printed text''''''''''</li>\r\n<li>==big heading==</li>\r\n<li>===small heading===</li>\r\n<li>??a cite??</li>\r\n<li>:::a quote:::</li>\r\n<li>[[code][// sourcecode - <br/>\r\n// even multiline]]</li>\r\n</ul>');_
INSERT INTO %%prefix%%plg_dict VALUES (302, 'write_article_info1', 35, '<p>Sie befinden sich nun auf der ersten Seite der Artikelerfassung. Auf dieser ersten Seiten werden sogenannte \\"Meta-Informationen\\" gepflegt. Hierbei handelt es sich um beschreibende Informationen zu dem Artikel, den Sie gleich schreiben werden.</p>\r\n<p>Es muss z.B. eine Rubrik und die Sprache ausgewählt werden in welcher der Artikel später erscheinen soll. Außerdem sollten Sie für Artikel, die in einer Artikelliste erscheinen werden, hier einen Titel und eine Beschreibung für die Listenansicht pflegen. Aber auch Schlüsselworte und eine Beschreibung für Suchmaschinen können hier hinterlegt werden.</p>\r\n<p>Für weitere Informationen werfen Sie einen Blick in unsere <a href=\\"http://www.borderlesscms.de/streber/70\\" target=\\"_blank\\">Online-Hilfe</a> zum Thema \\"Artikel schreiben\\"</p>', '<p>You are now on the first page of the article creation process. On this page the so called \\"meta informations\\" have to be specified. These are pieces of information describing teh article you are going to write.</p>\r\n<p>You e.g. have to choose the category and the language the article shall be published in. For articles published in an article list you should furthermore specify a title and a description for the list presentation of your article. And of course you can also set the search engine keywords and description for your article in this form.</p>\r\n');_
INSERT INTO %%prefix%%plg_dict VALUES (303, 'write_article_info2', 35, '<p>Sie befinden sich nun auf der zweiten Seite der Artikelerfassung. Auf dieser Seite können Sie nun Ihren Artikel schreiben.</p>\r\n<p>Für weitere Informationen werfen Sie einen Blick in unsere <a href=\\"http://www.borderlesscms.de/streber/70\\" target=\\"_blank\\">Online-Hilfe</a> zum Thema \\"Artikel schreiben\\"</p>\r\n', '<p>You are now on the first page of the article creation process. On this page you can now write your article.</p>\r\n');_
INSERT INTO %%prefix%%plg_dict VALUES (304, 'send_new_pw', 35, 'Neues Password schicken', 'Send new password');_
INSERT INTO %%prefix%%plg_dict VALUES (305, 'choose_action', 35, 'Aktion wählen:', 'Choose action:');_
INSERT INTO %%prefix%%plg_dict VALUES (306, 'NoElementSelected', 35, 'Sie haben kein Element der Liste ausgewählt! <br/>\r\nBitte wählen Sie zunächst eine oder mehrere Listenzeilen aus indem Sie das Kontrollfeld am Anfang der Zeile anklicken. Anschließend können Sie die gewünschte Aktion aus dem Auswahlfeld unterhalb der Liste ausführen.', 'No list element has been selected!<br/>\r\nPlease first select an element of the list by clicking the checkbox in front of each row. Then you can choose and execute an action from the drop-down field below the list.');_
INSERT INTO %%prefix%%plg_dict VALUES (307, 'search', 35, 'suchen', 'search');_
INSERT INTO %%prefix%%plg_dict VALUES (308, 'searchphrase', 35, 'Suchbegriff (* = <a href=\\"http://de.wikipedia.org/wiki/Wildcard_%28Informatik%29\\" target=\\"_blank\\" title=\\"Informationen zu Wildcards - Link öffnet in neuem Fenster\\">Wildcard</a>)', 'Search phrase (* = <a href=\\"http://en.wikipedia.org/wiki/Wildcard_character\\" target=\\"_blank\\" title=\\"Information on wildcards - link will open in new window\\">wildcard</a>)');_
INSERT INTO %%prefix%%plg_dict VALUES (309, 'ASC', 35, 'aufsteigend', 'ascending');_
INSERT INTO %%prefix%%plg_dict VALUES (310, 'DESC', 35, 'absteigend', 'descending');_
INSERT INTO %%prefix%%plg_dict VALUES (311, 'plugin_config', 35, 'Zur Plugin-Konfiguration', 'To plugin configuration');_
INSERT INTO %%prefix%%plg_dict VALUES (312, 'search_table_entries', 35, 'Tabelleneinträge durchsuchen', 'Search table entries');_
INSERT INTO %%prefix%%plg_dict VALUES (313, 'h.category_plugin_config', 35, 'Rubrikabhängige Plugin-Konfiguration', 'Category dependent plugin configuration');_
INSERT INTO %%prefix%%plg_dict VALUES (315, 'plg.name', 35, 'Pluginname', 'plugin name');_
INSERT INTO %%prefix%%plg_dict VALUES (316, 'plg.created', 35, 'Installationsdatum', 'installation date');_
INSERT INTO %%prefix%%plg_dict VALUES (317, 'plg.classname', 35, 'Klassenname', 'class name');_
INSERT INTO %%prefix%%plg_dict VALUES (318, 'plg.filename', 35, 'Dateiname (ohne .php)', 'filename (without .php)');_
INSERT INTO %%prefix%%plg_dict VALUES (319, 'plg.techname', 35, 'Technischer Name (ohne Sonderzeichen, Umlaute und Leerzeichen)', 'Technical name (without special chars and blanks)');_
INSERT INTO %%prefix%%plg_dict VALUES (320, 'plg.modulename', 35, 'Technischer Name (ohne Sonderzeichen, Umlaute und Leerzeichen)', 'Technical name (without special chars and blanks)');_
INSERT INTO %%prefix%%plg_dict VALUES (321, 'cat.fk_plg_conf_right', 35, 'Welches Recht erlaubt es einem Benutzer die rubrikabhängige Pluginkonfiguration zu bearbeiten?', 'Which right enables a user to edit the category dependent plugin config?');_
INSERT INTO %%prefix%%plg_dict VALUES (322, 'config.var_name', 35, 'Variable', 'Variable');_
INSERT INTO %%prefix%%plg_dict VALUES (323, 'config.var_type', 35, 'Art', 'Type');_
INSERT INTO %%prefix%%plg_dict VALUES (324, 'config.fk_section', 35, 'Bereich', 'Section');_
INSERT INTO %%prefix%%plg_dict VALUES (325, 'config.editable', 35, 'editierbar?', 'editable?');_
INSERT INTO %%prefix%%plg_dict VALUES (326, 'config.var_value', 35, 'Wert', 'Value');_
INSERT INTO %%prefix%%plg_dict VALUES (327, 'config.h.addConfigVar', 35, 'Konfigurationsvariable hinzufügen', 'Add configuration variable');_
INSERT INTO %%prefix%%plg_dict VALUES (328, 'config.heading', 35, 'Systemkonfigurationsverwaltung', 'Systemconfiguration Manager');_
INSERT INTO %%prefix%%plg_dict VALUES (329, 'config.config_id', 35, 'ID', 'ID');_
INSERT INTO %%prefix%%plg_dict VALUES (330, 'dict.dict_id', 35, 'ID', 'ID');_
INSERT INTO %%prefix%%plg_dict VALUES (331, 'config.h.edit_var', 35, 'Konfigurationsvariable editieren', 'Edit configuration variable');_
INSERT INTO %%prefix%%plg_dict VALUES (332, 'config.h.deleteEntries', 35, 'Konfigurationsvariable löschen', 'Delete configuration variable');_
INSERT INTO %%prefix%%plg_dict VALUES (333, 'config.var_description', 35, 'Beschreibung', 'Description');_
INSERT INTO %%prefix%%plg_dict VALUES (334, 'id', 35, 'ID', 'ID');_
INSERT INTO %%prefix%%plg_dict VALUES (335, 'requestlog.heading', 35, 'Einträge im Anfrageprotokoll', 'Entries of request log');_
INSERT INTO %%prefix%%plg_dict VALUES (336, 'requestlog.requestlog_id', 35, 'ID', 'ID');_
INSERT INTO %%prefix%%plg_dict VALUES (337, 'requestlog.user_id', 35, 'Benutzer', 'User');_
INSERT INTO %%prefix%%plg_dict VALUES (338, 'requestlog.uri', 35, 'URL', 'URL');_
INSERT INTO %%prefix%%plg_dict VALUES (339, 'requestlog.post', 35, '$_POST-Werte', '$_POST-values');_
INSERT INTO %%prefix%%plg_dict VALUES (340, 'requestlog.get', 35, '$_GET-Werte', '$_GET-values');_
INSERT INTO %%prefix%%plg_dict VALUES (341, 'requestlog.session', 35, '$_SESSION-Werte', '$_SESSION-values');_
INSERT INTO %%prefix%%plg_dict VALUES (342, 'requestlog.request_date', 35, 'Datum', 'Timestamp');_
INSERT INTO %%prefix%%plg_dict VALUES (343, 'requestlog.username', 35, 'Benutzer', 'User');_
INSERT INTO %%prefix%%plg_dict VALUES (344, 'requestlog.h.edit_entry', 35, 'Anfrageprotokoll bearbeiten', 'Edit request log');_
INSERT INTO %%prefix%%plg_dict VALUES (345, 'requestlog.h.deleteEntries', 35, 'Anfrageprotokoll - Einträge löschen', 'request log - delete entries');_
INSERT INTO %%prefix%%plg_dict VALUES (346, 'ReportBug', 35, 'Fehler melden', 'Report bug');_
INSERT INTO %%prefix%%plg_dict VALUES (347, 'requestlog.h.report_bug', 35, 'Fehler melden', 'Report Bug');_
INSERT INTO %%prefix%%plg_dict VALUES (348, 'requestlog.h.bug_desc', 35, 'Kurze Beschreibung des Fehlers', 'Short bug description');_
INSERT INTO %%prefix%%plg_dict VALUES (349, 'requestlog.bug_mail_beginning', 35, 'Dies ist eine automatisch generierte E-Mail. Sie wurde von einem Benutzer auf der untenstehenden Internetseite über das \\"Fehler melden\\"-Formular von Borderless CMS verschickt. \r\n\r\nSie erhalten diese E-Mail, weil Ihre E-Mail-Adresse in dieser Installation von Borderless CMS als Empfängeradresse für Fehlermeldungen angegeben ist. Sollten Sie dies nicht wünschen, teilen Sie dies dem  Administrator dieser Installation mit. Sie können hierzu einfach auf diese E-Mail hier antworten, da seine E-Mail-Adresse als Absender eingetragen wurde.\r\n-----', 'This is an automatically generated mail. It has been send to you by a user via the \\"report bug\\"-form of the Borderless CMS installation on the website mentioned below.\r\n\r\nYou receive this e-mail as your mail-address is specified as the recipient for bug reports in the Borderless CMS installation on the website mentioned below. If you do not want to recieve these e-mails in the future, notify the installations administrator. Therefore you can easily reply to this e-mail as his e-mail-address has been used as the return address.\r\n-----');_
INSERT INTO %%prefix%%plg_dict VALUES (350, 'requestlog.bug_mail_subject', 35, 'FEHLERMELDUNG - Url:', 'BUG REPORT - Url:');_
INSERT INTO %%prefix%%plg_dict VALUES (351, 'recommend_subject', 35, 'Diese Internetseite kann ich empfehlen!', 'This website I can recommend you!');_
INSERT INTO %%prefix%%plg_dict VALUES (352, 'recommend_site', 35, 'Seite empfehlen', 'Recommend this page');_
INSERT INTO %%prefix%%plg_dict VALUES (353, 'om.view_details_right', 35, 'Welches Recht erlaubt es einem Benutzer die Zusatzinformationen zu einer Datei zu sehen?', 'Which right enables a user to view the detailed information on a file?');_
INSERT INTO %%prefix%%plg_dict VALUES (354, 'om.add_right', 35, 'Welches Recht erlaubt es einem Benutzer eine Datei hochzuladen?', 'Which right enables a user to upload a file?');_
INSERT INTO %%prefix%%plg_dict VALUES (355, 'om.del_right', 35, 'Welches Recht erlaubt es einem Benutzer eine Datei zu löschen?', 'Which right enables a user to delete a file?');_
INSERT INTO %%prefix%%plg_dict VALUES (357, 'om.edit_right', 35, 'Welches Recht erlaubt es einem Benutzer eine beliebige Datei zu editieren?', 'Which right enables a user to edit an arbitrary file?');_
INSERT INTO %%prefix%%plg_dict VALUES (358, 'om.edit_own_right', 35, 'Welches Recht erlaubt es einem Benutzer eine selbst hochgeladene Datei zu editieren?', 'Which right enables a user to edit a file uploaded by himself?');_
INSERT INTO %%prefix%%plg_dict VALUES (359, 'om.change_status_right', 35, 'Welches Recht erlaubt es einem Benutzer den Status einer Datei zu ändern?', 'Which right enables a user to change the status of a file?');_
INSERT INTO %%prefix%%plg_dict VALUES (360, 'om.change_size_right', 35, 'Welches Recht erlaubt es einem Benutzer die Größe eines Vorschaubildes zu ändern?', 'Which right enables a user to change the size of a preview image?');_
INSERT INTO %%prefix%%plg_dict VALUES (361, 'om.folder', 35, 'Unterordner für Dateien in dieser Rubrik', 'Subfolder for files in this category');_
INSERT INTO %%prefix%%plg_dict VALUES (362, 'om.files_per_page', 35, 'Wieviele Dateien sollen pro Listenseite angezeigt werden?', 'How many files shall be displayed on a list page?');_
INSERT INTO %%prefix%%plg_dict VALUES (363, 'om.order_by', 35, 'Liste sortieren nach', 'Order list by');_
INSERT INTO %%prefix%%plg_dict VALUES (365, 'om.sort_direction', 35, 'Sortierreihenfolge', 'Order direction');_
INSERT INTO %%prefix%%plg_dict VALUES (366, 'om.cat_id', 35, 'ID der aktuellen Rubrik', 'Category ID');_
INSERT INTO %%prefix%%plg_dict VALUES (367, 'cont.cat_id', 35, 'ID der aktuellen Rubrik', 'Category ID');_
INSERT INTO %%prefix%%plg_dict VALUES (368, 'cont.add_right', 35, 'Welches Recht erlaubt es einem Benutzer einen Artikel zu schreiben?', 'Which right enables a user to write an article?');_
INSERT INTO %%prefix%%plg_dict VALUES (369, 'cont.del_right', 35, 'Welches Recht erlaubt es einem Benutzer einen Artikel zu löschen?', 'Which right enables a user to delete an article?');_
INSERT INTO %%prefix%%plg_dict VALUES (370, 'cont.edit_right', 35, 'Welches Recht erlaubt es einem Benutzer einen beliebigen Artikel zu editieren?', 'Which right enables a user to edit an arbitrary article?');_
INSERT INTO %%prefix%%plg_dict VALUES (371, 'cont.edit_own_right', 35, 'Welches Recht erlaubt es einem Benutzer einen selbst geschriebenen Artikel zu editieren?', 'Which right enables a user to edit an article written by himself?');_
INSERT INTO %%prefix%%plg_dict VALUES (372, 'cont.change_status_right', 35, 'Welches Recht erlaubt es einem Benutzer den Status eines Artikels zu ändern?', 'Which right enables a user to change the status of an article?');_
INSERT INTO %%prefix%%plg_dict VALUES (373, 'cont.order_by', 35, 'Liste sortieren nach', 'Order list by');_
INSERT INTO %%prefix%%plg_dict VALUES (374, 'cont.sort_direction', 35, 'Sortierreihenfolge', 'Order direction');_
INSERT INTO %%prefix%%plg_dict VALUES (375, 'cat.cat_id', 35, 'ID der Rubrik', 'Category-ID');_
INSERT INTO %%prefix%%plg_dict VALUES (376, 'cont.content_order_by', 35, 'Sortierkriterium', 'Sort key');_
INSERT INTO %%prefix%%plg_dict VALUES (377, 'syslog.heading', 35, 'Systemprotokoll', 'System log');_
INSERT INTO %%prefix%%plg_dict VALUES (378, 'syslog.h.view_entry', 35, 'Systemprotokolleintrag ansehen', 'View systemlog entry');_
INSERT INTO %%prefix%%plg_dict VALUES (379, 'syslog.h.deleteEntries', 35, 'Systemprotokolleintrag löschen', 'Delete systemlog entry');_
INSERT INTO %%prefix%%plg_dict VALUES (380, 'syslog.syslog_id', 35, 'Systemprotokolleintrag ID', 'systemlog entry ID');_
INSERT INTO %%prefix%%plg_dict VALUES (381, 'syslog.timestamp', 35, 'Zeitstempel', 'timestamp');_
INSERT INTO %%prefix%%plg_dict VALUES (382, 'syslog.syslog', 35, 'Systemprotokolleintrag', 'Systemlog entry');_
INSERT INTO %%prefix%%plg_dict VALUES (383, 'syslog.fk_session', 35, 'Benutzersitzungs ID', 'Usersession ID');_
INSERT INTO %%prefix%%plg_dict VALUES (384, 'syslog.username', 35, 'Benutzername', 'Username');_
INSERT INTO %%prefix%%plg_dict VALUES (385, 'syslog.logtype', 35, 'Protokollart', 'Logtype');_
INSERT INTO %%prefix%%plg_dict VALUES (386, 'syslog.severity', 35, 'Fehlergrad', 'Severity');_
INSERT INTO %%prefix%%plg_dict VALUES (387, 'syslog.referrer_uri', 35, 'Vorherige URI', 'Referrer URI');_
INSERT INTO %%prefix%%plg_dict VALUES (388, 'syslog.request_uri', 35, 'Angefragte URI', 'Requested URI');_
INSERT INTO %%prefix%%plg_dict VALUES (389, 'syslog.ip_address', 35, 'IP-Adresse', 'IP address');_
INSERT INTO %%prefix%%plg_dict VALUES (390, 'syslog.ref_application', 35, 'Applikation/ Methode', 'Application/ Method');_
INSERT INTO %%prefix%%plg_dict VALUES (391, 'syslog.user_agent', 35, 'Browser (User-Agent)', 'Browser (User-Agent)');_
INSERT INTO %%prefix%%plg_dict VALUES (392, 'syslog.filename', 35, 'Dateiname', 'Filename');_
INSERT INTO %%prefix%%plg_dict VALUES (393, 'syslog.linenum', 35, 'Zeilennummer', 'Linenumber');_
INSERT INTO %%prefix%%plg_dict VALUES (394, 'syslog.fk_user_id', 35, 'Benutzer ID', 'User ID');_
INSERT INTO %%prefix%%plg_dict VALUES (395, 'view', 35, 'ansehen', 'view');_
INSERT INTO %%prefix%%plg_dict VALUES (396, 'filename_no_special_chars', 35, 'Der Dateiname darf nur die Zeichen a-z, A-Z, 0-9, Punkt (.), Unterstrich (_) und Bindestrich (-) enthalten und maximal 255 Zeichen lang sein! Upload-Vorgang wurde abgebrochen!', 'The filename must only consist of the chars a-z, A-Z, 0-9, dot (.), underscore(_) and hyphen (-) and have a maximum length of 255 chars! Upload process has been cancelled!');_
INSERT INTO %%prefix%%plg_dict VALUES (397, 'cont.next_article', 35, 'Nächster Beitrag: ', 'Next article: ');_
INSERT INTO %%prefix%%plg_dict VALUES (398, 'cont.previous_article', 35, 'Voriger Beitrag: ', 'Previous article: ');_
INSERT INTO %%prefix%%plg_dict VALUES (399, 'om.load_files_from_fs', 35, 'Neue Bilder aus Dateisystem laden', 'Load new images from filesystem');_
INSERT INTO %%prefix%%plg_dict VALUES (400, 'om.load_files_from_fs_right', 35, 'Welches Recht erlaubt es einem Benutzer das Dateisystem nach neuen Dateien zu durchsuchen?', 'Which right enables a user to perform a filesystem check for new files?');_
INSERT INTO %%prefix%%plg_dict VALUES (401, 'insufficient_file_access', 35, 'ACHTUNG: Auf einige Ihrer hochgeladenen Dateien hat das System keinen Schreibzugriff. Bitte erhöhen Sie die Zugriffsrechte. In einigen Serversystemen kann es notwendig sein, dass für die hochgeladenen Dateien Lese-, Schreib- und Ausführberechtigungen für alle Benutzer (CHMOD 777) gesetzt werden.\r\nDies ist davon abhängig, ob Ihre hochgeladenen Dateien dem Benutzer gehören, der den PHP-Systemprozess ausführt. Für weitere Fragen zu diesem Thema kontaktieren Sie bitte Ihren Systemadministrator.', 'ATTENTION: The system does not have write access to some or all of your uploaded files. Please increase the access rights. For some system configurations it might be necessary to grant full access to all users (CHMOD 777) for your uploaded files. \r\nThis depends whether your uploaded files are owned by the php process owner. Please contact your system administrator if you have further questions on this topic.');_

CREATE TABLE _%%prefix%%plg_dict__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=402 ;_
INSERT INTO _%%prefix%%plg_dict__seq VALUES (401);_



CREATE TABLE %%prefix%%plg_entries (
  me_id int(11) unsigned NOT NULL default '0',
  techname varchar(20) NOT NULL default '',
  fk_module int(11) unsigned NOT NULL default '0',
  func varchar(40) NOT NULL default '',
  tablename varchar(40) NOT NULL default '',
  status_id int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (me_id),
  UNIQUE KEY techname (techname)
);_

INSERT INTO %%prefix%%plg_entries VALUES (9, 'dictionary_list', 5, 'show', 'dict', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (10, 'cont_article_catlist', 9, 'list', 'articles', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (11, 'cont_singlearticle', 9, 'single', 'articles', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (24, 'object_main', 7, 'list', 'objects', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (25, 'classification_list', 6, 'list', 'classify', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (26, 'user_login', 10, 'login', 'users', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (27, 'user_logout', 10, 'logout', 'users', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (28, 'user_profile', 10, 'profile', 'users', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (33, 'menu_list', 8, 'list', 'cat', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (34, 'user_list', 10, 'list', 'users', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (35, 'plugins_list', 11, 'list', 'plugins', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (36, 'menu_move', 8, 'move', 'cat', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (37, 'plugins_editall', 11, 'editall', 'plugins', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (38, 'rights_list', 12, 'list', 'rights', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (39, 'group_list', 13, 'list', 'groups', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (2, 'cont_list_all', 9, 'listall', 'articles', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (40, 'config', 14, 'config', 'config', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (41, 'requestlog', 15, 'list', '', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (42, 'systemlog', 16, 'list', '', 100);_
INSERT INTO %%prefix%%plg_entries VALUES (43, 'admmenu', 8, 'admin', '', 100);_

CREATE TABLE _%%prefix%%plg_entries__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=44 ;_
INSERT INTO _%%prefix%%plg_entries__seq VALUES (43);_



CREATE TABLE %%prefix%%plg_file_cat_conf (
  cat_id int(11) unsigned NOT NULL default '0',
  add_right int(11) unsigned NOT NULL default '0',
  view_details_right int(11) unsigned NOT NULL default '0',
  edit_right int(11) unsigned NOT NULL default '0',
  edit_own_right int(11) unsigned NOT NULL default '0',
  change_status_right int(11) unsigned NOT NULL default '0',
  del_right int(11) unsigned NOT NULL default '0',
  load_files_from_fs_right int(11) unsigned NOT NULL default '82',
  change_size_right int(11) unsigned NOT NULL default '0',
  folder varchar(50) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  files_per_page smallint(3) unsigned NOT NULL default '20',
  order_by varchar(40) NOT NULL default '',
  sort_direction int(11) unsigned NOT NULL default '0',
  user_id int(11) unsigned NOT NULL default '0',
  change_date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (cat_id)
);_

INSERT INTO %%prefix%%plg_file_cat_conf VALUES (52, 53, 71, 54, 55, 56, 58, 82, 57, '', 20, 'object_importdate', 42, 1, '2007-04-04 11:34:38');_



CREATE TABLE %%prefix%%plg_groups (
  group_id int(11) unsigned NOT NULL,
  groupname varchar(40) NOT NULL default '',
  fk_grouptype_id int(11) unsigned NOT NULL default '0',
  position smallint(2) unsigned NOT NULL default '1',
  fk_dict_id int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (group_id)
);_

INSERT INTO %%prefix%%plg_groups VALUES (1, 'Newbie', 6, 1, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (2, 'Benutzer', 6, 2, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (3, 'Redakteur', 6, 3, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (4, 'Systemadmin', 6, 6, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (6, 'Chefredakteur', 6, 5, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (7, 'Publisher', 6, 4, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (19, 'developer', 7, 1, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (12, 'set_rootflag', 7, 1, 0);_
INSERT INTO %%prefix%%plg_groups VALUES (20, 'not_signed_on', 6, 0, 0);_

CREATE TABLE _%%prefix%%plg_groups__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=21 ;_
INSERT INTO _%%prefix%%plg_groups__seq VALUES (20);_


CREATE TABLE %%prefix%%plg_objects (
  object_id int(11) unsigned NOT NULL,
  object_filename varchar(255) NOT NULL default '',
  object_folder varchar(255) NOT NULL default '',
  object_shortdesc varchar(255) default NULL,
  object_longdesc text,
  object_type varchar(50) default NULL,
  object_width int(5) unsigned default '0',
  object_height int(5) unsigned default '0',
  object_smallimage_filename varchar(255) default NULL,
  object_origin text,
  object_author varchar(255) default NULL,
  object_created datetime default '0000-00-00 00:00:00',
  object_importdate datetime NOT NULL default '0000-00-00 00:00:00',
  object_import_user int(11) unsigned NOT NULL default '0',
  content_id int(11) unsigned default '0',
  PRIMARY KEY  (object_id)
) ;_

CREATE TABLE _%%prefix%%plg_objects__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=1 ;_
INSERT INTO _%%prefix%%plg_objects__seq VALUES (0);_





CREATE TABLE %%prefix%%plg_plugins (
  module_id int(11) unsigned NOT NULL default '0',
  plg_name varchar(40) default NULL,
  techname varchar(20) NOT NULL default '',
  created varchar(19) NOT NULL default '',
  classname varchar(50) NOT NULL default '',
  filename varchar(50) NOT NULL default '',
  PRIMARY KEY  (module_id)
);_

INSERT INTO %%prefix%%plg_plugins VALUES (5, 'Dictionary', 'dictionary', '2006-01-11 23:36:00', 'Dictionary', 'dictionary/Dictionary');_
INSERT INTO %%prefix%%plg_plugins VALUES (6, 'Klassifikationen', 'classification', '2006-01-11 23:36:25', 'Classification', 'core/plugins/classification/Classification');_
INSERT INTO %%prefix%%plg_plugins VALUES (7, 'Dateiverwaltung', 'filemanager', '2006-01-11 23:37:28', 'FileManager', 'objects/FileManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (8, 'Rubrikverwaltung', 'categorymanager', '2006-01-27 18:06:34', 'CategoryManager', 'core/plugins/categories/CategoryManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (9, 'Artikelverwaltung', 'content', '2006-01-27 21:04:26', 'ContentManager', 'content/ContentManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (10, 'Benutzerverwaltung', 'user', '2006-01-27 21:04:40', 'UserManager', 'core/plugins/user/UserManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (11, 'Pluginverwaltung', 'pluginmanager', '2006-05-01 05:00:21', 'PluginManager', 'plugins/PluginManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (12, 'Systemrechte-Verwaltung', 'RightManager', '2006-06-30 01:17:52', 'RightManager', 'core/plugins/user/RightManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (13, 'Gruppen-Verwaltung', 'groupmanager', '2006-07-05 23:20:44', 'GroupManager', 'core/plugins/user/GroupManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (14, 'Konfigurations-Verwaltung', 'configmanager', '2006-12-16 14:43:28', 'ConfigManager', 'core/plugins/config/ConfigManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (15, 'Anfrageprotokoll anzeigen', 'requestlog', '2006-12-18 23:40:28', 'RequestLogManager', 'core/plugins/requestlog/RequestLogManager');_
INSERT INTO %%prefix%%plg_plugins VALUES (16, 'Systemprotokoll', 'systemlog', '2007-01-20 23:56:58', 'SystemLogManager', 'core/plugins/systemlog/SystemLogManager');_

CREATE TABLE _%%prefix%%plg_plugins__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=17 ;_
INSERT INTO _%%prefix%%plg_plugins__seq VALUES (16);_



CREATE TABLE %%prefix%%plg_rights (
  right_id int(11) unsigned NOT NULL default '0',
  rightname varchar(100) NOT NULL default '',
  fk_syscat_id int(11) unsigned NOT NULL default '0',
  fk_dict_id int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (right_id),
  UNIQUE KEY RECHTNAME (rightname)
);_

INSERT INTO %%prefix%%plg_rights VALUES (1, 'user_profile_update', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (2, 'user_create', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (3, 'user_view_list', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (4, 'user_delete', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (7, 'user_view', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (8, 'user_edit', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (9, 'group_right_allocation_view', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (31, 'user_group_allocation_view', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (32, 'DEBUGGINGINFO_VIEW', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (11, 'article_write', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (12, 'comment_write', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (49, 'category_view_logged_in_user_only', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (47, 'category_add', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (19, 'article_change_status', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (20, 'article_edit', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (22, 'article_delete', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (23, 'comment_delete', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (52, 'article_edit_own', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (26, 'group_delete', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (28, 'group_edit', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (30, 'user_group_allocation_edit', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (35, 'category_view_list', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (37, 'SHOW_ALL_USER_FIELDS', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (39, 'category_edit', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (40, 'category_view', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (41, 'category_delete', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (42, 'category_delete_admins_only', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (43, 'category_edit_admins_only', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (44, 'category_view_admins_only', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (46, 'history_edit', 33, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (50, 'category_create', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (51, 'category_create_admins_only', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (53, 'file_add', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (54, 'file_edit', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (55, 'file_edit_own', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (56, 'file_change_status', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (57, 'file_change_size', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (58, 'file_delete', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (59, 'file_change_cat_filefolder', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (60, 'category_edit_plg_config', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (61, 'config.edit_var', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (62, 'config.delete_var', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (63, 'config.view_list', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (64, 'config.edit_cat', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (65, 'config.delete_cat', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (66, 'category_move', 25, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (67, 'request_view_list', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (68, 'request_truncate_list', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (69, 'request_submit_bug', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (70, 'request_view_entry', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (71, 'file_view_details', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (72, 'file_view_list', 27, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (73, 'user_set_root_flag', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (74, 'user_change_additional_right', 37, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (81, 'syslog_view_entry', 24, 0);_
INSERT INTO %%prefix%%plg_rights VALUES (82, 'file_load_from_fs', 27, 0);_

CREATE TABLE _%%prefix%%plg_rights__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=83 ;_
INSERT INTO _%%prefix%%plg_rights__seq VALUES (82);_



CREATE TABLE %%prefix%%plg_users (
  user_id int(11) unsigned NOT NULL,
  username varchar(20) NOT NULL default '',
  passwort varchar(60) NOT NULL default '',
  vorname varchar(25) default NULL,
  nachname varchar(25) default NULL,
  about_me text,
  homepage varchar(255) default NULL,
  company varchar(120) default NULL,
  address varchar(80) default NULL,
  postzip varchar(8) default NULL,
  city varchar(80) default NULL,
  country varchar(80) NOT NULL default 'Deutschland',
  skype_username varchar(80) default NULL,
  email varchar(50) NOT NULL default '',
  telefon varchar(20) default NULL,
  fax varchar(30) NOT NULL default '',
  fk_fav_menu int(11) unsigned NOT NULL default '1',
  fav_layout varchar(100) NOT NULL default 'default',
  generally_notice_on_comment tinyint(1) unsigned NOT NULL default '1',
  generally_notice_on_answer_to_comment tinyint(1) unsigned NOT NULL default '1',
  public_fields text NOT NULL,
  last_ip varchar(15) default NULL,
  last_login datetime NOT NULL default '0000-00-00 00:00:00',
  akt_login datetime NOT NULL default '0000-00-00 00:00:00',
  root_flag tinyint(1) unsigned NOT NULL default '0',
  fk_zusatzrecht int(11) unsigned NOT NULL default '0',
  login_tries int(11) default NULL,
  time2login datetime NOT NULL default '0000-00-00 00:00:00',
  fk_aenderer int(11) unsigned NOT NULL default '0',
  change_date datetime NOT NULL default '0000-00-00 00:00:00',
  fk_anleger int(11) unsigned NOT NULL default '0',
  create_date datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (user_id),
  UNIQUE KEY USERNAME (username)
) ;_

INSERT INTO %%prefix%%plg_users VALUES (3, 'admin', '723c146738c6d162b54b6481e3de8b7f30c4bd92', 'Administrator', '(leer)', '', '', '', '', '', '', 'Deutschland', '', 'aheusingfeld@borderlesscms.de', '', '', 10, 'default', 1, 1, 'a:2:{i:0;s:8:\"username\";i:1;s:10:\"last_login\";}', '127.0.0.1', '2005-07-03 01:34:14', '2005-07-03 01:35:48', 0, 0, 0, '2005-07-03 00:35:48', 1, '2006-09-25 23:38:15', 1, '2005-07-01 00:00:00');_
INSERT INTO %%prefix%%plg_users VALUES (2, 'Not_logged_in', 'seg3qw465gs655673q4ghj8io89', 'NO', 'USER', NULL, NULL, NULL, NULL, NULL, NULL, 'Deutschland', NULL, 'nouser@localhost', NULL, '', 99, 'new_fzg', 1, 1, 'a:1:{i:0;s:8:\\"username\\";}', NULL, '2005-01-01 00:00:01', '2005-01-01 00:00:01', 0, 0, 1, '2005-01-01 00:00:01', 0, '2005-01-01 00:00:01', 1, '2005-06-28 14:39:00');_

CREATE TABLE _%%prefix%%plg_users__seq (
  id int(10) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=4 ;_
INSERT INTO _%%prefix%%plg_users__seq VALUES (3);_



CREATE TABLE %%prefix%%request_log (
  requestlog_id int(22) unsigned NOT NULL auto_increment,
  user_id int(11) unsigned NOT NULL default '0',
  request_date timestamp NOT NULL default CURRENT_TIMESTAMP,
  uri varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL default '',
  post text character set utf8 collate utf8_unicode_ci NOT NULL,
  get text character set utf8 collate utf8_unicode_ci NOT NULL,
  session_id text character set utf8 collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (requestlog_id)
) ;_

CREATE TABLE _%%prefix%%request_log__seq (
  id int(22) unsigned NOT NULL auto_increment,
  PRIMARY KEY  (id)
) AUTO_INCREMENT=1 ;_
INSERT INTO _%%prefix%%request_log__seq VALUES (0);_




CREATE TABLE %%prefix%%syslog (
  syslog_id int(11) unsigned NOT NULL auto_increment,
  timestmp datetime NOT NULL default '0000-00-00 00:00:00',
  syslog text NOT NULL,
  fk_session varchar(50) NOT NULL default '',
  fk_user_id int(11) unsigned NOT NULL default '0',
  logtype smallint(2) unsigned NOT NULL default '0',
  severity smallint(2) unsigned NOT NULL default '0',
  referrer_uri text NOT NULL,
  request_uri text NOT NULL,
  ip_address varchar(24) NOT NULL default '',
  ref_application varchar(40) default NULL,
  user_agent varchar(255) NOT NULL default '',
  filename varchar(255) NOT NULL default '',
  linenum varchar(255) NOT NULL default '',
  PRIMARY KEY  (syslog_id)
) AUTO_INCREMENT=1 ;_




CREATE TABLE %%prefix%%systemschluessel (
  ID_SCHLUESSEL int(11) unsigned NOT NULL auto_increment,
  SCHLUESSELTYP varchar(25) NOT NULL default '',
  AUSPRAEGUNG varchar(25) NOT NULL default '',
  PRIMARY KEY  (ID_SCHLUESSEL),
  UNIQUE KEY ( SCHLUESSELTYP )
) AUTO_INCREMENT=18 ;_

INSERT INTO %%prefix%%systemschluessel VALUES (1, 'ROLLENTYP', 'SYSTEM');_
INSERT INTO %%prefix%%systemschluessel VALUES (5, 'language', 'deutsch');_
INSERT INTO %%prefix%%systemschluessel VALUES (6, 'database', 'MySQL');_
INSERT INTO %%prefix%%systemschluessel VALUES (11, 'category_sysconfig', '');_
INSERT INTO %%prefix%%systemschluessel VALUES (12, 'status', '');_
INSERT INTO %%prefix%%systemschluessel VALUES (16, 'sort_order', '');_
INSERT INTO %%prefix%%systemschluessel VALUES (14, 'modul_typ', '');_
INSERT INTO %%prefix%%systemschluessel VALUES (15, 'dict_type', '');_
INSERT INTO %%prefix%%systemschluessel VALUES (7, 'formfieldstate', '');_
INSERT INTO %%prefix%%systemschluessel VALUES (17, 'datatype', '');_



CREATE TABLE %%prefix%%user_group_assoc (
  FK_USER int(11) unsigned NOT NULL default '0',
  FK_ROLLE int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (FK_USER,FK_ROLLE)
);_

INSERT INTO %%prefix%%user_group_assoc VALUES (2, 20);_
INSERT INTO %%prefix%%user_group_assoc VALUES (3, 4);_
INSERT INTO %%prefix%%user_group_assoc VALUES (3, 12);_

CREATE TABLE %%prefix%%usersessions (
  session_ids int(11) unsigned NOT NULL,
  sessionstring varchar(60) NOT NULL default '',
  data_string text NOT NULL,
  fk_user int(11) unsigned NOT NULL default '0',
  last_ip varchar(15) NOT NULL default '',
  starttime datetime NOT NULL default '0000-00-00 00:00:00',
  last_action datetime NOT NULL default '0000-00-00 00:00:00',
  action_uri varchar(255) NOT NULL default '',
  hash_val varchar(255) NOT NULL default '',
  PRIMARY KEY  (session_ids),
  UNIQUE KEY (hash_val),
  UNIQUE KEY SESSIONSTRING (sessionstring)
);_
