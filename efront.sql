-- MySQL dump 10.13  Distrib 5.5.35, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: efront
-- ------------------------------------------------------
-- Server version	5.5.35-0ubuntu0.12.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `benchmark`
--

DROP TABLE IF EXISTS `benchmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `benchmark` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `url` text,
  `init_time` float DEFAULT NULL,
  `script_time` float DEFAULT NULL,
  `database_time` float DEFAULT NULL,
  `smarty_time` float DEFAULT NULL,
  `total_time` float DEFAULT NULL,
  `memory_usage` float DEFAULT NULL,
  `total_queries` mediumint(8) unsigned DEFAULT NULL,
  `max_query` text,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `benchmark`
--

LOCK TABLES `benchmark` WRITE;
/*!40000 ALTER TABLE `benchmark` DISABLE KEYS */;
/*!40000 ALTER TABLE `benchmark` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmarks`
--

DROP TABLE IF EXISTS `bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmarks` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `lessons_ID` mediumint(8) unsigned DEFAULT NULL,
  `name` text,
  `url` text,
  PRIMARY KEY (`id`),
  KEY `users_LOGIN` (`users_LOGIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks`
--

LOCK TABLES `bookmarks` WRITE;
/*!40000 ALTER TABLE `bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `cache_key` char(64) NOT NULL,
  `value` longtext,
  `timestamp` int(10) unsigned NOT NULL,
  `timeout` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calendar`
--

DROP TABLE IF EXISTS `calendar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calendar` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data` text,
  `timestamp` int(10) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `users_LOGIN` varchar(100) NOT NULL,
  `foreign_ID` mediumint(8) unsigned DEFAULT '0',
  `type` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar`
--

LOCK TABLES `calendar` WRITE;
/*!40000 ALTER TABLE `calendar` DISABLE KEYS */;
/*!40000 ALTER TABLE `calendar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) unsigned NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `contents` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (2,1397060984,'29vcucmadjc9cnkp9hvt2bpto1','a:1:{s:6:\"course\";a:1:{i:2;s:1:\"2\";}}'),(3,1397106187,'ufv0fa1qnijjmklta7lfaboi30','a:1:{s:6:\"course\";a:1:{i:9;s:1:\"9\";}}'),(4,1397106190,'hu0ljffndvcp5bl1n9oo11m0n6','a:1:{s:6:\"course\";a:1:{i:9;s:1:\"9\";}}'),(5,1397108425,'tk8tc9tp118dph5j27c4ddka83','a:1:{s:6:\"course\";a:1:{i:11;s:2:\"11\";}}');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `data` text NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `content_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=393 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `completed_tests`
--

DROP TABLE IF EXISTS `completed_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `completed_tests` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) DEFAULT NULL,
  `tests_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) DEFAULT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `archive` tinyint(1) NOT NULL DEFAULT '0',
  `time_start` int(10) unsigned DEFAULT NULL,
  `time_end` int(10) unsigned DEFAULT NULL,
  `time_spent` int(10) unsigned DEFAULT NULL,
  `score` float DEFAULT NULL,
  `pending` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `users_login` (`users_LOGIN`),
  KEY `tests_ID` (`tests_ID`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`),
  KEY `archive` (`archive`),
  KEY `score` (`score`),
  KEY `pending` (`pending`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `completed_tests`
--

LOCK TABLES `completed_tests` WRITE;
/*!40000 ALTER TABLE `completed_tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `completed_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `completed_tests_blob`
--

DROP TABLE IF EXISTS `completed_tests_blob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `completed_tests_blob` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `completed_tests_ID` mediumint(8) unsigned NOT NULL,
  `test` longblob,
  PRIMARY KEY (`id`),
  KEY `ibfk_completed_tests_blob_1` (`completed_tests_ID`),
  CONSTRAINT `ibfk_completed_tests_blob_1` FOREIGN KEY (`completed_tests_ID`) REFERENCES `completed_tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `completed_tests_blob`
--

LOCK TABLES `completed_tests_blob` WRITE;
/*!40000 ALTER TABLE `completed_tests_blob` DISABLE KEYS */;
/*!40000 ALTER TABLE `completed_tests_blob` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuration`
--

LOCK TABLES `configuration` WRITE;
/*!40000 ALTER TABLE `configuration` DISABLE KEYS */;
INSERT INTO `configuration` VALUES ('activate_ldap','0'),('activation','1'),('additional_footer','Carr. Ocozocoautla - Ocuilapa \r\nNo. 1190 Barrio Cruz Blanca \r\nOcozocoautla de Espinosa, Chiapas. \r\n\r\nTel.: (01 968) 68 8 31 61'),('allow_direct_login','1'),('api','0'),('api_ip','127.0.0.1'),('autologout_time','5'),('ban_failed_logins','0'),('cache_enabled','1'),('cms_page',''),('collapse_catalog','0'),('compress_tests','0'),('constrain_access','1'),('css',''),('currency','MXN'),('currency_order','1'),('database_version','3.6.14'),('date_format','DD/MM/YYYY'),('debug_mode',''),('default_language','spanish'),('default_type','student'),('discount_period',''),('discount_start',''),('display_empty_blocks','1'),('editor_type','tinymce_new'),('eliminate_post_xss','1'),('enable_balance','1'),('enable_cart','1'),('encrypt_url','0'),('error_page','themes/default/external/default_error_page.html'),('facebook_api_key',''),('facebook_secret',''),('favicon',''),('file_black_list','php,php3,jsp,asp,cgi,pl,exe,com,bat,sh,ph3,php4,ph4,php5,ph5,phtm,phtml'),('file_encoding','UTF-8'),('file_white_list',''),('force_change_password','0'),('gz_handler','1'),('help_url','http://docs.efrontlearning.net/index.php'),('insert_group_key','1'),('ip_black_list',''),('ip_white_list','*.*.*.*'),('last_reset_certificate','1398536407'),('ldap_base_dn',''),('ldap_bind_dn',''),('ldap_cn','cn'),('ldap_l','l'),('ldap_mail','mail'),('ldap_password',''),('ldap_port','389'),('ldap_postaladdress','postaladdress'),('ldap_preferredlanguage','referredlanguage'),('ldap_protocol','3'),('ldap_server','ldap://localhost'),('ldap_telephonenumber','telephonenumber'),('ldap_uid','uid'),('lessons_directory','1'),('lesson_enroll','1'),('license_note',''),('license_server','http://keys.efrontlearning.net/list.php?version=10'),('load_videojs','0'),('location','Greece'),('lock_down','0'),('login_redirect_page','lesson_catalog'),('logo','32'),('logout_redirect','www.cesba.edu.mx'),('logo_max_height','250'),('logo_max_width','400'),('mail_activation','0'),('mapped_accounts','0'),('math_content','0'),('math_images','0'),('math_server','http://www.imathas.com/cgi-bin/mimetex.cgi'),('max_file_size','50000'),('max_online_users','2'),('max_online_users_threshold',''),('max_online_users_threshold_timestamp',''),('max_online_users_timestamp','1398358659'),('mode_allow_direct_login','1'),('mode_allow_users_to_delete_supervisor_files','1'),('mode_archive','2'),('mode_backup','1'),('mode_bookmarks','1'),('mode_calendar','1'),('mode_change_info','1'),('mode_change_pass','1'),('mode_comments','1'),('mode_configuration','1'),('mode_course_instances','1'),('mode_curriculum','2'),('mode_feedback','1'),('mode_forum','1'),('mode_func_comments','0'),('mode_func_people','0'),('mode_func_userstatus','0'),('mode_glossary','1'),('mode_groups','1'),('mode_help','1'),('mode_languages','2'),('mode_lessons_timeline','0'),('mode_logout_user','2'),('mode_maintenance','2'),('mode_messages','1'),('mode_messages_student','1'),('mode_mod_rewrite_bypass','0'),('mode_move_blocks','1'),('mode_news','1'),('mode_notifications','1'),('mode_online_users','1'),('mode_payments','1'),('mode_projects','1'),('mode_propagate_courses_to_branch_users','1'),('mode_questions_pool',''),('mode_search_user','2'),('mode_shared_glossary',''),('mode_show_complete_org_chart','1'),('mode_show_organization_chart','1'),('mode_show_unassigned_users_to_supervisors','1'),('mode_show_user_form','0'),('mode_simple_complete','1'),('mode_skillgaptests','2'),('mode_social_events','0'),('mode_statistics','1'),('mode_surveys','1'),('mode_system_timeline','0'),('mode_tests','1'),('mode_test_glossary','0'),('mode_themes','1'),('mode_tooltip','1'),('mode_user_profile','1'),('mode_user_types','1'),('mode_version_key','1'),('module_BBB_salt','29ae87201c1d23f7099f3dfb92f63578'),('module_BBB_server','http://yourserver.com/'),('module_BBB_server_version','1'),('module_security_last_check','1398536407'),('mod_rewrite_bypass','0'),('motto_on_header','0'),('multiple_logins',''),('normalize_dimensions','1'),('notifications_lock','0'),('notifications_maximum_inter_time','0'),('notifications_max_sent_messages','100'),('notifications_messages_per_time','5'),('notifications_pageloads','10'),('notifications_send_mode','0'),('onelanguage','0'),('only_ldap','0'),('password_length','6'),('password_reminder','1'),('paypalbusiness',''),('paypaldebug','0'),('paypalmode','normal'),('phplivedocx_password',''),('phplivedocx_server','https://api.livedocx.com/1.2/mailmerge.asmx?WSDL'),('phplivedocx_username',''),('pm_space',''),('registration_file','includes/webserver_registration.php'),('remember_login',''),('reset_license_note_always','0'),('show_footer','1'),('show_license_note','0'),('signup','1'),('simple_mode','0'),('site_logo','33'),('site_motto','Plataforma Virtual'),('site_name','Centro de Estudios Superiores \"Benemérito de las Ámericas\"'),('smarty_cache','1'),('smarty_cache_timeout','60'),('smtp_auth','0'),('smtp_host','localhost'),('smtp_pass',''),('smtp_port','25'),('smtp_timeout',''),('smtp_user',''),('social_modules_activated','63'),('supervisor_mail_activation','0'),('system_email','cmonterrosa@gmail.com'),('theme','4'),('time_reports','0'),('time_zone','America/Mexico_City'),('total_discount','0'),('unauthorized_page','themes/default/external/default_unauthorized_page.html'),('updater_period','100000'),('username_format','#surname# #name# (#login#)'),('username_format_resolve','1'),('username_variable','$_SERVER[\"REMOTE_USER\"]'),('use_logo','1'),('version_activated',''),('version_hosted','0'),('version_key',''),('version_type','community'),('version_upgrades',''),('version_users',''),('virtual_keyboard','1'),('webserver_auth','0'),('webserver_registration','0'),('zip_method','php');
/*!40000 ALTER TABLE `configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `data` longtext,
  `parent_content_ID` mediumint(8) unsigned DEFAULT '0',
  `lessons_ID` mediumint(8) unsigned DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  `ctg_type` varchar(255) NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `previous_content_ID` mediumint(8) unsigned DEFAULT '0',
  `options` text,
  `metadata` text,
  `scorm_version` varchar(50) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT '1',
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `linked_to` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1049 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
INSERT INTO `content` VALUES (1044,'Introducción','<p>INTRODUCCION</p>\r\n<p> </p>\r\n<div class=\"page\" title=\"Page 3\">\r\n<div class=\"layoutArea\">\r\n<div class=\"column\">\r\n<p><span>Pedagogía </span></p>\r\n<ul>\r\n<li>\r\n<p><span>-  Analizar el concepto de sistema y su importancia para la comprensión </span></p>\r\n<p><span>de la organización y funcionamiento del sistema educativo. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Analizar la red de relaciones que existen entre el sistema educativo y su </span></p>\r\n<p><span>entorno socioeconómico y la importancia que estas relaciones tienen </span></p>\r\n<p><span>para el éxito de las políticas educativas. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Presentar una visión global de la organización del sistema educativo </span></p>\r\n<p><span>dominicano. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Destacar los hechos educativos más relevantes en cada periodo de la </span></p>\r\n<p><span>historia y su importancia para la interpretación crítica de la educación </span></p>\r\n<p><span>como actividad social. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Analizar la esencia, contenido y funciones de las áreas y los sistemas </span></p>\r\n<p><span>filosóficos y su relación con la filosofía de la educación. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Establecer las relaciones entre la filosofía educativa, la política y la </span></p>\r\n<p><span>Polìtica educativa. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Analizar las características y la importancia de la investigación educativa. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Analizar el papel de los actores del hecho educativo en el aula, la </span></p>\r\n<p><span>comunidad, la religión, el país y la sociedad en general. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Elaborar el perfil del buen maestro. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Sintetizar la características y las condiciones bajo las cuales el </span></p>\r\n<p><span>aprendizaje se produce. </span></p>\r\n</li>\r\n<li>\r\n<p><span>-  Analizar el papel que juegan las diferentes instituciones educativas </span></p>\r\n<p><span>en el desarrollo de la escuela como entidad social y polìtica. </span></p>\r\n</li>\r\n</ul>\r\n<p> </p>\r\n</div>\r\n</div>\r\n</div>',0,11,1397104130,'theory',1,0,'a:10:{s:21:\"complete_unit_setting\";s:1:\"0\";s:15:\"hide_navigation\";s:1:\"0\";s:7:\"indexed\";s:1:\"0\";s:17:\"maximize_viewport\";s:1:\"0\";s:18:\"scorm_asynchronous\";i:0;s:10:\"object_ids\";s:0:\"\";s:16:\"no_before_unload\";i:0;s:14:\"reentry_action\";b:0;s:17:\"complete_question\";i:0;s:13:\"complete_time\";s:0:\"\";}','a:6:{s:5:\"title\";s:12:\"Introduccion\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/09\";s:4:\"type\";s:7:\"content\";}',NULL,1,'',NULL),(1045,'¿Qué es Pedagogía?','<p>La <b>pedagogía</b> (del griego <i>παιδιον</i> (<i>paidos</i> -niño) y <i>γωγος</i> (<i>gogos</i> -conducir)) es la ciencia que tiene como objeto de estudio a la <a href=\"http://es.wikipedia.org/wiki/Educaci%C3%B3n\" title=\"Educación\">educación</a>. Es una ciencia perteneciente al campo de las Ciencias Sociales y Humanas, y tiene como fundamento principal los estudios de <a href=\"http://es.wikipedia.org/wiki/Kant\" title=\"Kant\" class=\"mw-redirect\">Kant</a> y <a href=\"http://es.wikipedia.org/wiki/Herbart\" title=\"Herbart\" class=\"mw-redirect\">Herbart</a>. Usualmente se logra apreciar, en textos académicos y documentos universitarios oficiales, la presencia ya sea de Ciencias Sociales y Humanidades, como dos campos independientes o, como aquí se trata, de ambas en una misma categoría que no equivale a igualdad absoluta sino a lazos de comunicación y similitud etimológica.</p>\r\n<p> </p>\r\n<p>El objeto de estudio de la pedagogía es la educación, tomada ésta en el sentido general que le han atribuido diversas legislaciones internacionales, como lo referido en documentos de la <a href=\"http://es.wikipedia.org/wiki/Organizaci%C3%B3n_de_las_Naciones_Unidas_para_la_Educaci%C3%B3n_la_Ciencia_y_la_Cultura\" title=\"Organización de las Naciones Unidas para la Educación la Ciencia y la Cultura\" class=\"mw-redirect\">Organización de las Naciones Unidas para la Educación la Ciencia y la Cultura</a> (UNESCO), la <a href=\"http://es.wikipedia.org/wiki/Organizaci%C3%B3n_de_Estados_Iberoamericanos_para_la_Educaci%C3%B3n,_la_Ciencia_y_la_Cultura\" title=\"Organización de Estados Iberoamericanos para la Educación, la Ciencia y la Cultura\">Organización de Estados Iberoamericanos para la Educación, la Ciencia y la Cultura</a> (OEI) y los propios de cada país (como las leyes generales o nacionales sobre educación). También es posible encontrar la palabra formación como objeto de estudio de la Pedagogía, siendo educación y formación vocablos sinónimos en tal contexto (existe un debate que indica que son términos diferentes).</p>\r\n<p> </p>\r\n<p>La Pedagogía estudia a la educación como fenómeno complejo y multirreferencial, lo que indica que existen conocimientos provenientes de otras ciencias y disciplinas que le pueden ayudar a comprender lo que es la educación; ejemplos de ello son la <a href=\"http://es.wikipedia.org/wiki/Historia\" title=\"Historia\">historia</a>, la <a href=\"http://es.wikipedia.org/wiki/Sociolog%C3%ADa\" title=\"Sociología\">sociología</a>, la <a href=\"http://es.wikipedia.org/wiki/Psicolog%C3%ADa\" title=\"Psicología\">psicología</a> y la <a href=\"http://es.wikipedia.org/wiki/Pol%C3%ADtica\" title=\"Política\">política</a>, entre otras. En este contexto, la educación tiene como propósito incorporar a los sujetos a una sociedad determinada que posee pautas culturales propias y características; es decir, la educación es una acción que lleva implícita la intencionalidad del mejoramiento social progresivo que permita que el ser humano desarrolle todas sus potencialidades. Para una mejor comprensión de la historia de la conformación de la Pedagogía y su relación con la educación Kant y <a href=\"http://es.wikipedia.org/wiki/Durkheim\" title=\"Durkheim\" class=\"mw-redirect\">Durkheim</a> aportan elementos importantes. Kant propone la confección de una disciplina que sea científica, teórica y práctica que se base en principios y en la experimentación y que además reflexione sobre prácticas concretas. Durkheim al referirse a la educación expresa que es materia de la Pedagogía y es indispensable construir un saber por medio de la implementación de reglas metodológicas, postura positivista, que sea garante del carácter científico de dicho conocimiento.</p>',0,11,1397106629,'theory',1,1047,'a:10:{s:21:\"complete_unit_setting\";s:1:\"0\";s:15:\"hide_navigation\";s:1:\"0\";s:7:\"indexed\";s:1:\"0\";s:17:\"maximize_viewport\";s:1:\"0\";s:18:\"scorm_asynchronous\";i:0;s:10:\"object_ids\";s:0:\"\";s:16:\"no_before_unload\";i:0;s:14:\"reentry_action\";b:0;s:17:\"complete_question\";i:0;s:13:\"complete_time\";s:0:\"\";}','a:6:{s:5:\"title\";s:21:\"¿Qué es Pedagogía?\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:7:\"content\";}',NULL,1,'',NULL),(1046,'Conceptos básicos','<div align=\"justify\">Según los libros básicos que introducen a las ciencias de la educación, al partir de un estudio etimológico, se puede afirmar que la palabra pedagogía procede del pensamiento griego, y hace mención, por una parte, al acto de dirigir o instruir a los niños, y por otra a los cuidados que resultan de la educación adquirida por éstos.</div>\r\n<p>La pedagogía va más allá del campo estrictamente educativo, concebido éste como heteroeducación intencional, ya que la pedagogía lleva a la persona hasta la autoeducación continua.</p>\r\n<p> </p>\r\n<p>Es decir, a través de la acción pedagógica, el sujeto logra servirse de su capacidad de aprendizaje, y consigue, por último, prescindir de la asistencia externa, que en un primer momento constituyó su fuente de motivación hacia el crecimiento y desenvolvimiento de sus facultades potenciales.</p>\r\n<p>Se dice que la pedagogía es una ciencia, ya que consiste en un conjunto sistemático de conocimiento que hacen referencia a un objeto determinado. Se comprende, de esta manera, que la pedagogía como ciencia, debe proceder por análisis, al mismo tiempo que debe mostrar de manera concreta su campo de interés y estudio, los métodos de los que hace uso para alcanzar su meta específica, y los resultados que finalmente logra.</p>',0,12,1397106753,'theory',1,0,'a:10:{s:21:\"complete_unit_setting\";s:1:\"0\";s:15:\"hide_navigation\";s:1:\"0\";s:7:\"indexed\";s:1:\"0\";s:17:\"maximize_viewport\";s:1:\"0\";s:18:\"scorm_asynchronous\";i:0;s:10:\"object_ids\";s:0:\"\";s:16:\"no_before_unload\";i:0;s:14:\"reentry_action\";b:0;s:17:\"complete_question\";i:0;s:13:\"complete_time\";s:0:\"\";}','a:6:{s:5:\"title\";s:18:\"Conceptos básicos\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:7:\"content\";}',NULL,1,'',NULL),(1047,'Primera Evaluación','',1044,11,1397110070,'feedback',1,1044,NULL,'a:6:{s:5:\"title\";s:19:\"Primera Evaluación\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:7:\"content\";}',NULL,1,'',NULL),(1048,'Unidad II','<p>esta unid compe </p>\r\n<p>sdfsd</p>\r\n<p>sdf</p>\r\n<p>sdf</p>\r\n<p>sdf</p>\r\n<p> </p>\r\n<p><b>México</b><small>(<a href=\"http://commons.wikimedia.org/wiki/File:Speaker_Icon.svg\" class=\"image\"><img alt=\"Speaker Icon.svg\" src=\"http://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Speaker_Icon.svg/13px-Speaker_Icon.svg.png\" width=\"13\" height=\"13\" srcset=\"//upload.wikimedia.org/wikipedia/commons/thumb/2/21/Speaker_Icon.svg/20px-Speaker_Icon.svg.png 1.5x, //upload.wikimedia.org/wikipedia/commons/thumb/2/21/Speaker_Icon.svg/26px-Speaker_Icon.svg.png 2x\" /></a> <a href=\"http://upload.wikimedia.org/wikipedia/commons/5/5b/Mexico.ogg\" class=\"internal\" title=\"Mexico.ogg\">escuchar</a>)</small> (oficialmente llamado <b>Estados Unidos Mexicanos</b>)<sup id=\"cite_ref-11\" class=\"reference\"><a href=\"http://es.wikipedia.org/wiki/M%C3%A9xico#cite_note-11\">11</a></sup> es un país situado en la parte meridional de <a href=\"http://es.wikipedia.org/wiki/Am%C3%A9rica_del_Norte\" title=\"América del Norte\">América del Norte</a>. Limita al norte con los <a href=\"http://es.wikipedia.org/wiki/Estados_Unidos_de_Am%C3%A9rica\" title=\"Estados Unidos de América\" class=\"mw-redirect\">Estados Unidos de América</a>, al sureste con <a href=\"http://es.wikipedia.org/wiki/Belice\" title=\"Belice\">Belice</a> y<a href=\"http://es.wikipedia.org/wiki/Guatemala\" title=\"Guatemala\">Guatemala</a>, al oeste con el <a href=\"http://es.wikipedia.org/wiki/Oc%C3%A9ano_Pac%C3%ADfico\" title=\"Océano Pacífico\">océano Pacífico</a> y al este con el <a href=\"http://es.wikipedia.org/wiki/Golfo_de_M%C3%A9xico\" title=\"Golfo de México\">golfo de México</a> y el <a href=\"http://es.wikipedia.org/wiki/Mar_Caribe\" title=\"Mar Caribe\">mar Caribe</a>. Es el <a href=\"http://es.wikipedia.org/wiki/Anexo:Pa%C3%ADses_por_superficie\" title=\"Anexo:Países por superficie\">décimo cuarto</a> país más extenso del mundo, con una superficie cercana a los 2 millones de <a href=\"http://es.wikipedia.org/wiki/Kil%C3%B3metro_cuadrado\" title=\"Kilómetro cuadrado\">km²</a>. Es el <a href=\"http://es.wikipedia.org/wiki/Anexo:Pa%C3%ADses_por_poblaci%C3%B3n\" title=\"Anexo:Países por población\">undécimo país</a>más poblado del mundo, con una población que a mediados de <a href=\"http://es.wikipedia.org/wiki/2013\" title=\"2013\">2013</a> ronda los 118 millones de personas,<sup id=\"cite_ref-12\" class=\"reference\"><a href=\"http://es.wikipedia.org/wiki/M%C3%A9xico#cite_note-12\">12</a></sup> <sup id=\"cite_ref-uno_6-1\" class=\"reference\"><a href=\"http://es.wikipedia.org/wiki/M%C3%A9xico#cite_note-uno-6\">6</a></sup> <sup id=\"cite_ref-dos_7-1\" class=\"reference\"><a href=\"http://es.wikipedia.org/wiki/M%C3%A9xico#cite_note-dos-7\">7</a></sup>la mayoría de las cuales tienen como lengua materna el <a href=\"http://es.wikipedia.org/wiki/Idioma_espa%C3%B1ol\" title=\"Idioma español\">español</a>, al que el estado reconoce como lengua nacional junto a <a href=\"http://es.wikipedia.org/wiki/Lenguas_de_M%C3%A9xico\" title=\"Lenguas de México\">67 lenguas indígenas propias de la nación</a>.<sup id=\"cite_ref-13\" class=\"reference\"><a href=\"http://es.wikipedia.org/wiki/M%C3%A9xico#cite_note-13\">13</a></sup></p>\r\n<p>La presencia humana en México se remonta a 30 mil años antes del presente. Después de miles de años de desarrollo cultural, surgieron en el territorio mexicano las culturas <a href=\"http://es.wikipedia.org/wiki/Mesoam%C3%A9rica\" title=\"Mesoamérica\">mesoamericanas</a>, <a href=\"http://es.wikipedia.org/wiki/Aridoam%C3%A9rica\" title=\"Aridoamérica\">aridoamericanas</a> y<a href=\"http://es.wikipedia.org/wiki/Oasisam%C3%A9rica\" title=\"Oasisamérica\">oasisamericanas</a>. Tras casi 300 años de dominación española, México inició la lucha por su independencia política en <a href=\"http://es.wikipedia.org/wiki/1810\" title=\"1810\">1810</a>. Posteriormente, durante cerca de un siglo el país se vio envuelto en una serie de guerras internas e invasiones extranjeras que tuvieron repercusiones en todos los ámbitos de la vida de los mexicanos. Durante buena parte del <a href=\"http://es.wikipedia.org/wiki/Siglo_XX\" title=\"Siglo XX\">siglo XX</a> (principalmente la primera mitad) tuvo lugar un período de gran crecimiento económico en el marco de una política domi</p>',0,11,1397152555,'theory',1,1045,'a:10:{s:21:\"complete_unit_setting\";s:1:\"0\";s:15:\"hide_navigation\";s:1:\"0\";s:7:\"indexed\";s:1:\"0\";s:17:\"maximize_viewport\";s:1:\"0\";s:18:\"scorm_asynchronous\";i:0;s:10:\"object_ids\";s:0:\"\";s:16:\"no_before_unload\";i:0;s:14:\"reentry_action\";b:0;s:17:\"complete_question\";i:0;s:13:\"complete_time\";s:0:\"\";}','a:6:{s:5:\"title\";s:9:\"Unidad II\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:7:\"content\";}',NULL,1,'',NULL);
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(150) NOT NULL,
  `max_uses` int(10) unsigned NOT NULL DEFAULT '0',
  `max_user_uses` int(10) unsigned NOT NULL DEFAULT '0',
  `duration` int(10) unsigned NOT NULL DEFAULT '30',
  `discount` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `from_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `archive` int(10) unsigned DEFAULT '0',
  `created` int(10) unsigned DEFAULT NULL,
  `start_date` int(10) unsigned DEFAULT NULL,
  `end_date` int(10) unsigned DEFAULT NULL,
  `options` text,
  `metadata` text,
  `description` text,
  `info` text,
  `price` float DEFAULT '0',
  `show_catalog` tinyint(1) NOT NULL DEFAULT '1',
  `publish` tinyint(1) DEFAULT '1',
  `directions_ID` mediumint(8) unsigned DEFAULT NULL,
  `languages_NAME` varchar(50) NOT NULL,
  `reset` tinyint(1) NOT NULL DEFAULT '0',
  `certificate_expiration` int(10) unsigned DEFAULT NULL,
  `reset_interval` int(10) unsigned DEFAULT NULL,
  `max_users` int(10) unsigned DEFAULT NULL,
  `rules` text,
  `instance_source` mediumint(8) unsigned DEFAULT '0',
  `supervisor_LOGIN` varchar(100) DEFAULT NULL,
  `depends_on` mediumint(8) unsigned DEFAULT '0',
  `ceu` int(10) unsigned DEFAULT NULL,
  `creator_LOGIN` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `instance_source` (`instance_source`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (9,'Metodología General',1,0,1397106149,0,0,'a:11:{s:18:\"recurring_duration\";i:0;s:13:\"auto_complete\";i:1;s:16:\"auto_certificate\";i:0;s:11:\"certificate\";s:0:\"\";s:18:\"certificate_tpl_id\";i:0;s:22:\"certificate_tpl_id_rtf\";i:0;s:25:\"certificate_export_method\";s:3:\"xml\";s:8:\"duration\";N;s:14:\"training_hours\";N;s:10:\"start_date\";s:0:\"\";s:8:\"end_date\";s:0:\"\";}','a:7:{s:5:\"title\";s:43:\"Introducción las Ciencias de la Educación\";s:7:\"creator\";s:24:\"Administrator S. (admin)\";s:9:\"publisher\";s:24:\"Administrator S. (admin)\";s:11:\"contributor\";s:24:\"Administrator S. (admin)\";s:4:\"date\";s:10:\"2014/04/10\";s:8:\"language\";s:8:\"Español\";s:4:\"type\";s:6:\"course\";}','','a:0:{}',0,1,1,9,'spanish',0,0,0,0,'a:0:{}',0,NULL,0,1,'admin'),(10,'Temas Selectos de Pedagogía',1,0,1397106503,0,0,'a:11:{s:18:\"recurring_duration\";i:0;s:13:\"auto_complete\";i:1;s:16:\"auto_certificate\";i:0;s:11:\"certificate\";s:0:\"\";s:18:\"certificate_tpl_id\";i:0;s:22:\"certificate_tpl_id_rtf\";i:0;s:25:\"certificate_export_method\";s:3:\"xml\";s:8:\"duration\";N;s:14:\"training_hours\";N;s:10:\"start_date\";s:0:\"\";s:8:\"end_date\";s:0:\"\";}','a:7:{s:5:\"title\";s:28:\"Temas Selectos de Pedagogía\";s:7:\"creator\";s:24:\"Administrator S. (admin)\";s:9:\"publisher\";s:24:\"Administrator S. (admin)\";s:11:\"contributor\";s:24:\"Administrator S. (admin)\";s:4:\"date\";s:10:\"2014/04/10\";s:8:\"language\";s:8:\"Español\";s:4:\"type\";s:6:\"course\";}','','a:0:{}',0,1,1,9,'spanish',0,0,0,0,'a:0:{}',0,NULL,0,1,'admin'),(11,'Introducción al Derecho',1,0,1397108187,0,0,'a:11:{s:18:\"recurring_duration\";i:0;s:13:\"auto_complete\";i:1;s:16:\"auto_certificate\";i:0;s:11:\"certificate\";s:0:\"\";s:18:\"certificate_tpl_id\";i:0;s:22:\"certificate_tpl_id_rtf\";i:0;s:25:\"certificate_export_method\";s:3:\"xml\";s:8:\"duration\";N;s:14:\"training_hours\";N;s:10:\"start_date\";s:0:\"\";s:8:\"end_date\";s:0:\"\";}','a:7:{s:5:\"title\";s:24:\"Introducción al Derecho\";s:7:\"creator\";s:24:\"Administrator S. (admin)\";s:9:\"publisher\";s:24:\"Administrator S. (admin)\";s:11:\"contributor\";s:24:\"Administrator S. (admin)\";s:4:\"date\";s:10:\"2014/04/10\";s:8:\"language\";s:8:\"Español\";s:4:\"type\";s:6:\"course\";}','','a:0:{}',0,1,1,20,'spanish',0,0,0,0,'a:0:{}',0,NULL,0,1,'admin');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses_to_groups`
--

DROP TABLE IF EXISTS `courses_to_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses_to_groups` (
  `courses_ID` mediumint(8) unsigned NOT NULL,
  `user_type` varchar(50) DEFAULT 'student',
  `groups_ID` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`courses_ID`,`groups_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses_to_groups`
--

LOCK TABLES `courses_to_groups` WRITE;
/*!40000 ALTER TABLE `courses_to_groups` DISABLE KEYS */;
INSERT INTO `courses_to_groups` VALUES (9,'student',1),(10,'student',1),(11,'student',1);
/*!40000 ALTER TABLE `courses_to_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directions`
--

DROP TABLE IF EXISTS `directions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `directions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `parent_direction_ID` mediumint(8) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `directions`
--

LOCK TABLES `directions` WRITE;
/*!40000 ALTER TABLE `directions` DISABLE KEYS */;
INSERT INTO `directions` VALUES (7,'Licenciaturas',1,0),(8,'Maestrías',1,0),(9,'Ciencias de la Educación',1,7),(10,'Contaduría Pública',1,7),(11,'Administración',1,7),(12,'Derecho',1,7),(13,'Derecho Penal',1,8),(14,'Administración(Dirección de Negocios)',1,8),(15,'Pedagogía',1,8),(16,'Metodología General',1,18),(17,'Temas Selectos de Pedagogía',1,18),(18,'Primer Cuatrimestre',1,9),(19,'Segundo Cuatrimestre',1,9),(20,'Primer Cuatrimestre',1,13),(21,'Introducción al Derecho',1,20),(22,'Jurisprudencia',1,20);
/*!40000 ALTER TABLE `directions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `done_questions`
--

DROP TABLE IF EXISTS `done_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `done_questions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `done_tests_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `questions_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `answer` text,
  `score` float DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `done_questions`
--

LOCK TABLES `done_questions` WRITE;
/*!40000 ALTER TABLE `done_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `done_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `done_tests`
--

DROP TABLE IF EXISTS `done_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `done_tests` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `tests_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  `score` float DEFAULT '0',
  `comments` text,
  `duration` mediumint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `done_tests`
--

LOCK TABLES `done_tests` WRITE;
/*!40000 ALTER TABLE `done_tests` DISABLE KEYS */;
/*!40000 ALTER TABLE `done_tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_notifications`
--

DROP TABLE IF EXISTS `event_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_notifications` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `event_type` int(11) NOT NULL,
  `after_time` int(10) NOT NULL DEFAULT '0',
  `send_conditions` text,
  `send_recipients` int(1) DEFAULT '1',
  `subject` varchar(255) NOT NULL,
  `message` text,
  `active` tinyint(1) DEFAULT '1',
  `html_message` tinyint(1) DEFAULT '0',
  `send_immediately` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_notifications`
--

LOCK TABLES `event_notifications` WRITE;
/*!40000 ALTER TABLE `event_notifications` DISABLE KEYS */;
INSERT INTO `event_notifications` VALUES (1,4,0,'a:0:{}',1,'Recover your password','Dear user ###users_name###,<br><br>This is an automated email sent from ###host_name### because you asked to recover your password. Please click the confirmation link below:.<br><br>###host_name###/index.php?ctg=reset_pwd&login=###users_login###&id=###md5(###users_login###)###<br><br>Alternatively, you may copy the link and paste it in your browser&#039;s address field.<br>Clicking on the link will confirm that your email address is valid so a new password can be sent to you. <br>For further information you may contact the system administrator through the following URL: ###host_name###/index.php?ctg=contact <br><br>With kind regards<br>---<br>The administration group<br>###site_name###<br>###site_motto###<br>This is an automated email sent from the address: ###host_name### on ###date###<br><br>',1,0,1),(2,7,0,'a:0:{}',1,'Recover your password','Dear user ###users_name###,<br><br>This is an automated email sent from ###host_name### with your new account password. <br>Your new password is: <br><br>###new_password###<br>\n                              <br>For further information you may contact the system administrator through the following URL: ###host_name###/index.php?ctg=contact <br><br>With kind regards<br>---<br>The administration group<br>###site_name###<br>###site_motto###<br>This is an automated email sent from the address: ###host_name### on ###date###',1,0,1),(3,6,0,'a:0:{}',1,'Account activation email','Dear user ###users_name###,<br><br>Welcome to our eLearning platform.! <br>Please, follow link below to activate your account:<br>###host_name###/index.php?account=###users_login###&key=###timestamp###<br><br><br>This is an automated email sent from the address: ###host_name### on ###date###<br>For further information you may contact the system administrator through the following URL: ###host_name###/index.php?ctg=contact <br><br>With kind regards<br>---<br>The administration group<br>###site_name###<br>###site_motto###<br>',1,0,1),(4,5,0,'a:0:{}',1,'Registration email','Dear user ###users_name###,<br><br>Welcome to our eLearning platform. <br>Your account was successfully created with the following personal information:<br><br>Login: ###users_login###<br>First name: ###users_name###<br>Last name: ###users_surname###<br>Email address: ###users_email###<br>Language: ###users_language###<br>Comments: ###users_comments###<br><br>For further information you may contact the system administrator through the following URL: ###host_name###/index.php?ctg=contact <br><br>With kind regards<br>---<br>The administration group<br>###site_name###<br>###site_motto###<br>',1,0,1);
/*!40000 ALTER TABLE `event_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `users_name` varchar(255) NOT NULL,
  `users_surname` varchar(255) NOT NULL,
  `timestamp` int(10) NOT NULL,
  `type` int(11) NOT NULL,
  `lessons_ID` varchar(255) DEFAULT NULL,
  `lessons_name` varchar(255) DEFAULT NULL,
  `entity_ID` varchar(255) DEFAULT NULL,
  `entity_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_LOGIN` (`users_LOGIN`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (7,'admin','System','Administrator',1396549675,2,NULL,NULL,NULL,NULL),(8,'admin','System','Administrator',1396549677,2,NULL,NULL,NULL,NULL),(9,'admin','System','Administrator',1396550751,2,NULL,NULL,NULL,NULL),(10,'admin','System','Administrator',1396552015,2,NULL,NULL,NULL,NULL),(11,'admin','System','Administrator',1396552112,2,NULL,NULL,NULL,NULL),(12,'admin','System','Administrator',1396552191,2,NULL,NULL,NULL,NULL),(13,'cmonterrosa','Carlos ','Monterrosa',1396552326,1,NULL,NULL,NULL,'mientras'),(14,'cmonterrosa','Carlos ','Monterrosa',1396552327,151,'0',NULL,'16',NULL),(20,'admin','System','Administrator',1396556161,2,NULL,NULL,NULL,NULL),(21,'admin','System','Administrator',1396557191,2,NULL,NULL,NULL,NULL),(22,'jvazquez','Jesus','Vázquez',1396557923,1,NULL,NULL,NULL,'mientras'),(23,'jvazquez','Jesus','Vázquez',1396557924,151,'0',NULL,'17',NULL),(26,'jvazquez','Jesus','Vázquez',1396557945,2,NULL,NULL,NULL,NULL),(27,'jvazquez','Jesus','Vázquez',1396558060,2,NULL,NULL,NULL,NULL),(28,'admin','System','Administrator',1396558655,2,NULL,NULL,NULL,NULL),(31,'jvazquez','Jesus','Vázquez',1396558927,2,NULL,NULL,NULL,NULL),(38,'cmonterrosa','Carlos ','Monterrosa',1397101079,2,NULL,NULL,NULL,NULL),(39,'jvazquez','Jesus','Vázquez',1397101130,2,NULL,NULL,NULL,NULL),(42,'admin','System','Administrator',1397101626,2,NULL,NULL,NULL,NULL),(67,'cmonterrosa','Carlos ','Monterrosa',1397102024,53,'5','Mercadotecnia',NULL,NULL),(68,'jvazquez','Jesus','Vázquez',1397102024,53,'5','Mercadotecnia',NULL,NULL),(69,'jvazquez','Jesus','Vázquez',1397102027,53,'4','Administración de Pequeñas y Medianas Empresas',NULL,NULL),(70,'jvazquez','Jesus','Vázquez',1397102033,53,'3','Introducción al Derecho',NULL,NULL),(71,'professor','Default','Professor',1397102038,53,'1','¿Cómo ser un programador?',NULL,NULL),(72,'student','Default','Student',1397102038,53,'1','¿Cómo ser un programador?',NULL,NULL),(73,'cmonterrosa','Carlos ','Monterrosa',1397102042,53,'2','Plataforma',NULL,NULL),(74,'professor','Default','Professor',1397102042,53,'2','Plataforma',NULL,NULL),(75,'student','Default','Student',1397102042,53,'2','Plataforma',NULL,NULL),(76,'cmonterrosa','Carlos ','Monterrosa',1397102046,53,'6','Metodología General',NULL,NULL),(77,'jvazquez','Jesus','Vázquez',1397102046,53,'6','Metodología General',NULL,NULL),(78,'jvazquez','Jesus','Vázquez',1397102517,51,'7','Metodología General',NULL,NULL),(79,'cmonterrosa','Carlos ','Monterrosa',1397102518,50,'7','Metodología General',NULL,NULL),(80,'admin','System','Administrator',1397102614,2,NULL,NULL,NULL,NULL),(81,'mulloa','Moisés','Ulloa',1397102904,1,NULL,NULL,NULL,'mientras'),(82,'mulloa','Moisés','Ulloa',1397102904,151,'0',NULL,'18',NULL),(83,'mulloa','Moisés','Ulloa',1397102919,2,NULL,NULL,NULL,NULL),(84,'admin','System','Administrator',1397102983,2,NULL,NULL,NULL,NULL),(85,'mulloa','Moisés','Ulloa',1397103041,2,NULL,NULL,NULL,NULL),(86,'cmonterrosa','Carlos ','Monterrosa',1397103697,25,'11','Unidad 1',NULL,NULL),(87,'jvazquez','Jesus','Vázquez',1397103697,26,'11','Unidad 1',NULL,NULL),(88,'jvazquez','Jesus','Vázquez',1397103741,2,NULL,NULL,NULL,NULL),(89,'jvazquez','Jesus','Vázquez',1397104130,100,'11','Unidad 1','1044','Introduccion'),(90,'admin','System','Administrator',1397105112,2,NULL,NULL,NULL,NULL),(91,'cmonterrosa','Carlos ','Monterrosa',1397105128,28,'11','Unidad 1',NULL,NULL),(92,'jvazquez','Jesus','Vázquez',1397105128,28,'11','Unidad 1',NULL,NULL),(93,'cmonterrosa','Carlos ','Monterrosa',1397105128,53,'7','Metodología General',NULL,NULL),(94,'jvazquez','Jesus','Vázquez',1397105128,53,'7','Metodología General',NULL,NULL),(95,'admin','System','Administrator',1397105513,2,NULL,NULL,NULL,NULL),(96,'admin','System','Administrator',1397105962,2,NULL,NULL,NULL,NULL),(97,'jvazquez','Jesus','Vázquez',1397106530,51,'9','Metodología General',NULL,NULL),(98,'cmonterrosa','Carlos ','Monterrosa',1397106531,50,'9','Metodología General',NULL,NULL),(99,'jvazquez','Jesus','Vázquez',1397106542,2,NULL,NULL,NULL,NULL),(100,'jvazquez','Jesus','Vázquez',1397106629,100,'11','Introducción ','1045','¿Qué es Pedagogía?'),(101,'jvazquez','Jesus','Vázquez',1397106653,101,'11','Introducción ','1045','¿Qué es Pedagogía?'),(102,'jvazquez','Jesus','Vázquez',1397106696,101,'11','Introducción ','1044','Introducción'),(103,'jvazquez','Jesus','Vázquez',1397106754,100,'12','Conceptos básicos','1046','Conceptos básicos'),(104,'admin','System','Administrator',1397106822,2,NULL,NULL,NULL,NULL),(105,'admin','System','Administrator',1397106942,2,NULL,NULL,NULL,NULL),(106,'admin','System','Administrator',1397107058,2,NULL,NULL,NULL,NULL),(107,'cmonterrosa','Carlos ','Monterrosa',1397107209,2,NULL,NULL,NULL,NULL),(108,'cmonterrosa','Carlos ','Monterrosa',1397107236,27,'11','Introducción ',NULL,NULL),(109,'cmonterrosa','Carlos ','Monterrosa',1397107261,103,'11','Introducción ','1045','¿Qué es Pedagogía?'),(110,'cmonterrosa','Carlos ','Monterrosa',1397107268,103,'11','Introducción ','1044','Introducción'),(111,'jvazquez','Jesus','Vázquez',1397107381,2,NULL,NULL,NULL,NULL),(112,'cmonterrosa','Carlos ','Monterrosa',1397107574,2,NULL,NULL,NULL,NULL),(113,'admin','System','Administrator',1397107699,2,NULL,NULL,NULL,NULL),(114,'cmonterrosa','Carlos ','Monterrosa',1397108192,50,'11','Introducción al Derecho',NULL,NULL),(115,'jvazquez','Jesus','Vázquez',1397108388,51,'10','Temas Selectos de Pedagogía',NULL,NULL),(116,'jvazquez','Jesus','Vázquez',1397108388,51,'11','Introducción al Derecho',NULL,NULL),(117,'jvazquez','Jesus','Vázquez',1397108438,2,NULL,NULL,NULL,NULL),(118,'admin','System','Administrator',1397108536,2,NULL,NULL,NULL,NULL),(119,'admin','System','Administrator',1397108794,175,NULL,NULL,'1','¡No te quedes sin inscribirte!'),(120,'admin','System','Administrator',1397109059,2,NULL,NULL,NULL,NULL),(121,'cmonterrosa','Carlos ','Monterrosa',1397109532,2,NULL,NULL,NULL,NULL),(122,'cmonterrosa','Carlos ','Monterrosa',1397109591,27,'12','Conceptos básicos',NULL,NULL),(123,'cmonterrosa','Carlos ','Monterrosa',1397109609,103,'12','Conceptos básicos','1046','Conceptos básicos'),(124,'cmonterrosa','Carlos ','Monterrosa',1397109609,29,'12','Conceptos básicos',NULL,NULL),(125,'jvazquez','Jesus','Vázquez',1397109686,2,NULL,NULL,NULL,NULL),(126,'admin','System','Administrator',1397109799,2,NULL,NULL,NULL,NULL),(127,'jvazquez','Jesus','Vázquez',1397110022,2,NULL,NULL,NULL,NULL),(128,'jvazquez','Jesus','Vázquez',1397110070,100,'11','Introducción ','1047','Primera Evaluación'),(129,'jvazquez','Jesus','Vázquez',1397110070,101,'11','Introducción ','1045','¿Qué es Pedagogía?'),(130,'jvazquez','Jesus','Vázquez',1397110070,75,'11','Introducción ',NULL,NULL),(131,'cmonterrosa','Carlos ','Monterrosa',1397110400,45,'11','Introducción ',NULL,NULL),(132,'cmonterrosa','Carlos ','Monterrosa',1397110401,45,'12','Conceptos básicos',NULL,NULL),(133,'cmonterrosa','Carlos ','Monterrosa',1397110401,65,'9','Metodología General',NULL,NULL),(134,'admin','System','Administrator',1397110445,2,NULL,NULL,NULL,NULL),(135,'mulloa','Moisés','Ulloa',1397151661,2,NULL,NULL,NULL,NULL),(136,'mulloa','Moisés','Ulloa',1397152002,2,NULL,NULL,NULL,NULL),(137,'mulloa','Moisés','Ulloa',1397152132,32,'13','Introducción al Derecho','1','la convención'),(138,'mulloa','Moisés','Ulloa',1397152132,38,'13','Introducción al Derecho','1','la convención'),(139,'jvazquez','Jesus','Vázquez',1397152426,2,NULL,NULL,NULL,NULL),(140,'jvazquez','Jesus','Vázquez',1397152555,100,'11','Introducción ','1048','Unidad II'),(141,'cmonterrosa','Carlos ','Monterrosa',1397152666,2,NULL,NULL,NULL,NULL),(142,'cmonterrosa','Carlos ','Monterrosa',1397152687,27,'11','Introducción ',NULL,NULL),(143,'cmonterrosa','Carlos ','Monterrosa',1397152688,29,'11','Introducción ',NULL,NULL),(144,'cmonterrosa','Carlos ','Monterrosa',1397152702,103,'11','Introducción ','1044','Introducción'),(145,'cmonterrosa','Carlos ','Monterrosa',1397152777,27,'12','Conceptos básicos',NULL,NULL),(146,'cmonterrosa','Carlos ','Monterrosa',1397153717,103,'12','Conceptos básicos','1046','Conceptos básicos'),(147,'cmonterrosa','Carlos ','Monterrosa',1397153717,29,'12','Conceptos básicos',NULL,NULL),(148,'cmonterrosa','Carlos ','Monterrosa',1397153717,54,'9','Metodología General',NULL,NULL),(149,'admin','System','Administrator',1398358658,2,NULL,NULL,NULL,NULL),(150,'jvazquez','Jesus','Vázquez',1398358736,151,'0',NULL,'34',NULL),(151,'jvazquez','Jesus','Vázquez',1398358741,151,'0',NULL,'35',NULL),(152,'jvazquez','Jesus','Vázquez',1398359162,151,'0',NULL,'36',NULL),(153,'jvazquez','Jesus','Vázquez',1398359237,2,NULL,NULL,NULL,NULL),(154,'admin','System','Administrator',1398359301,2,NULL,NULL,NULL,NULL),(155,'cmonterrosa','Carlos ','Monterrosa',1398359399,151,'0',NULL,'37',NULL),(156,'cmonterrosa','Carlos ','Monterrosa',1398359716,151,'0',NULL,'38',NULL),(157,'cmonterrosa','Carlos ','Monterrosa',1398359717,151,'0',NULL,'39',NULL),(158,'mulloa','Moisés','Ulloa',1398359748,151,'0',NULL,'40',NULL),(159,'mulloa','Moisés','Ulloa',1398359815,151,'0',NULL,'41',NULL),(160,'mulloa','Moisés','Ulloa',1398359848,151,'0',NULL,'42',NULL),(161,'mulloa','Moisés','Ulloa',1398359967,151,'0',NULL,'43',NULL),(162,'mulloa','Moisés','Ulloa',1398360092,2,NULL,NULL,NULL,NULL),(163,'mulloa','Moisés','Ulloa',1398360916,2,NULL,NULL,NULL,NULL),(164,'jvazquez','Jesus','Vázquez',1398360956,2,NULL,NULL,NULL,NULL),(165,'mulloa','Moisés','Ulloa',1398439480,2,NULL,NULL,NULL,NULL),(166,'admin','System','Administrator',1398536405,2,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_configuration`
--

DROP TABLE IF EXISTS `f_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_configuration` (
  `name` varchar(100) NOT NULL,
  `value` varchar(150) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_configuration`
--

LOCK TABLES `f_configuration` WRITE;
/*!40000 ALTER TABLE `f_configuration` DISABLE KEYS */;
/*!40000 ALTER TABLE `f_configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_folders`
--

DROP TABLE IF EXISTS `f_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_folders` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`users_LOGIN`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_folders`
--

LOCK TABLES `f_folders` WRITE;
/*!40000 ALTER TABLE `f_folders` DISABLE KEYS */;
INSERT INTO `f_folders` VALUES (1,'Incoming','admin',0),(2,'Sent','admin',0),(3,'Drafts','admin',0),(4,'Incoming','professor',0),(5,'Sent','professor',0),(6,'Drafts','professor',0),(7,'Incoming','student',0),(8,'Sent','student',0),(9,'Drafts','student',0),(31,'Incoming','jvazquez',0),(32,'Sent','jvazquez',0),(33,'Drafts','jvazquez',0),(34,'Incoming','cmonterrosa',0),(35,'Sent','cmonterrosa',0),(36,'Drafts','cmonterrosa',0),(37,'Incoming','mulloa',0),(38,'Sent','mulloa',0),(39,'Drafts','mulloa',0);
/*!40000 ALTER TABLE `f_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_forums`
--

DROP TABLE IF EXISTS `f_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_forums` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `lessons_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `users_LOGIN` varchar(100) NOT NULL,
  `comments` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_forums`
--

LOCK TABLES `f_forums` WRITE;
/*!40000 ALTER TABLE `f_forums` DISABLE KEYS */;
INSERT INTO `f_forums` VALUES (11,'Introducción ',11,0,1,'mulloa',''),(12,'Conceptos básicos',12,0,1,'admin',''),(13,'Introducción al Derecho',13,0,1,'admin',''),(14,'Jurisprudencia',14,0,1,'admin','');
/*!40000 ALTER TABLE `f_forums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_messages`
--

DROP TABLE IF EXISTS `f_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_messages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `f_topics_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `replyto` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_messages`
--

LOCK TABLES `f_messages` WRITE;
/*!40000 ALTER TABLE `f_messages` DISABLE KEYS */;
INSERT INTO `f_messages` VALUES (1,1,'la convención','<p>comentar donde será el lugar</p>',1397152131,'mulloa',0,0);
/*!40000 ALTER TABLE `f_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_personal_messages`
--

DROP TABLE IF EXISTS `f_personal_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_personal_messages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `recipient` text,
  `sender` varchar(100) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `attachments` text,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `bcc` tinyint(1) NOT NULL DEFAULT '0',
  `f_folders_ID` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pm_index` (`users_LOGIN`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_personal_messages`
--

LOCK TABLES `f_personal_messages` WRITE;
/*!40000 ALTER TABLE `f_personal_messages` DISABLE KEYS */;
INSERT INTO `f_personal_messages` VALUES (1,'jvazquez','jvazquez','cmonterrosa',1397107341,NULL,'Profe','Tengo dudas sobre la tarea XXXXXXX',0,31,1,0),(2,'cmonterrosa','jvazquez','cmonterrosa',1397107341,NULL,'Profe','Tengo dudas sobre la tarea XXXXXXX',0,35,0,0),(4,'cmonterrosa','jvazquez','cmonterrosa',1397107342,NULL,'Profe','Tengo dudas sobre la tarea XXXXXXX',0,35,0,0),(5,'cmonterrosa','cmonterrosa','jvazquez',1397107431,NULL,'Re: Profe','<p>------------------ Mensaje original ------------------ Tengo dudas sobre la tarea XXXXXXX</p>\r\n<p> </p>\r\n<p> </p>\r\n<p>Claro que te ayudaré, buscame mañana despues de clases.</p>\r\n<p> </p>\r\n<p>Saludos</p>\r\n<p> </p>\r\n<p>Buenas noches</p>',0,34,1,0),(6,'jvazquez','cmonterrosa','jvazquez',1397107431,NULL,'Re: Profe','<p>------------------ Mensaje original ------------------ Tengo dudas sobre la tarea XXXXXXX</p>\r\n<p> </p>\r\n<p> </p>\r\n<p>Claro que te ayudaré, buscame mañana despues de clases.</p>\r\n<p> </p>\r\n<p>Saludos</p>\r\n<p> </p>\r\n<p>Buenas noches</p>',0,32,0,0),(7,'cmonterrosa','cmonterrosa','jvazquez',1397107465,'27','Re: Profe','<p>------------------ Mensaje original ------------------ Tengo dudas sobre la tarea XXXXXXX</p>\r\n<p> </p>\r\n<p>Gracias hasta mañana..</p>',0,34,1,0),(8,'jvazquez','cmonterrosa','jvazquez',1397107465,'26','Re: Profe','<p>------------------ Mensaje original ------------------ Tengo dudas sobre la tarea XXXXXXX</p>\r\n<p> </p>\r\n<p>Gracias hasta mañana..</p>',0,32,0,0),(9,'jvazquez','jvazquez','cmonterrosa',1397152842,NULL,'duda de maestría','mi duda es ….',0,31,1,0),(10,'cmonterrosa','jvazquez','cmonterrosa',1397152842,NULL,'duda de maestría','mi duda es ….',0,35,0,0);
/*!40000 ALTER TABLE `f_personal_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_poll`
--

DROP TABLE IF EXISTS `f_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_poll` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `question` text NOT NULL,
  `options` text NOT NULL,
  `timestamp_created` int(10) unsigned NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `f_forums_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `timestamp_start` int(10) unsigned NOT NULL,
  `timestamp_end` int(10) unsigned NOT NULL,
  `views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sticky` tinyint(1) NOT NULL DEFAULT '0',
  `comments` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_poll`
--

LOCK TABLES `f_poll` WRITE;
/*!40000 ALTER TABLE `f_poll` DISABLE KEYS */;
/*!40000 ALTER TABLE `f_poll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_topics`
--

DROP TABLE IF EXISTS `f_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_topics` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `f_forums_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `views` mediumint(8) unsigned DEFAULT '0',
  `viewed_by` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sticky` tinyint(1) DEFAULT '0',
  `comments` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_topics`
--

LOCK TABLES `f_topics` WRITE;
/*!40000 ALTER TABLE `f_topics` DISABLE KEYS */;
INSERT INTO `f_topics` VALUES (1,13,1397152131,'la convención','mulloa',0,NULL,1,0,NULL);
/*!40000 ALTER TABLE `f_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `f_users_to_polls`
--

DROP TABLE IF EXISTS `f_users_to_polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `f_users_to_polls` (
  `f_poll_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(100) NOT NULL,
  `vote` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`f_poll_ID`,`users_LOGIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `f_users_to_polls`
--

LOCK TABLES `f_users_to_polls` WRITE;
/*!40000 ALTER TABLE `f_users_to_polls` DISABLE KEYS */;
/*!40000 ALTER TABLE `f_users_to_polls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `path` text NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `description` text,
  `groups_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `access` smallint(3) unsigned NOT NULL DEFAULT '755',
  `shared` mediumint(8) unsigned DEFAULT '0',
  `metadata` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (3,'upload/professor/temp/16_exported.zip','professor',1365429312,NULL,0,755,0,'a:6:{s:5:\"title\";s:15:\"16_exported.zip\";s:7:\"creator\";s:17:\"Default Professor\";s:9:\"publisher\";s:17:\"Default Professor\";s:11:\"contributor\";s:17:\"Default Professor\";s:4:\"date\";s:10:\"2013/04/08\";s:4:\"type\";s:4:\"file\";}'),(4,'upload/professor/temp/17_exported.zip','professor',1365429312,NULL,0,755,0,'a:6:{s:5:\"title\";s:15:\"17_exported.zip\";s:7:\"creator\";s:17:\"Default Professor\";s:9:\"publisher\";s:17:\"Default Professor\";s:11:\"contributor\";s:17:\"Default Professor\";s:4:\"date\";s:10:\"2013/04/08\";s:4:\"type\";s:4:\"file\";}'),(5,'upload/professor/temp/18_exported.zip','professor',1365429312,NULL,0,755,0,'a:6:{s:5:\"title\";s:15:\"18_exported.zip\";s:7:\"creator\";s:17:\"Default Professor\";s:9:\"publisher\";s:17:\"Default Professor\";s:11:\"contributor\";s:17:\"Default Professor\";s:4:\"date\";s:10:\"2013/04/08\";s:4:\"type\";s:4:\"file\";}'),(7,'www/content/lessons/6/SCORM_2004_4ED_v1_1_TR_20090814.pdf','professor',1365429312,NULL,0,755,0,'a:6:{s:5:\"title\";s:35:\"SCORM_2004_4ED_v1_1_TR_20090814.pdf\";s:7:\"creator\";s:17:\"Default Professor\";s:9:\"publisher\";s:17:\"Default Professor\";s:11:\"contributor\";s:17:\"Default Professor\";s:4:\"date\";s:10:\"2013/04/08\";s:4:\"type\";s:4:\"file\";}'),(9,'www/content/lessons/6/defaultCA4K2R11.jpg','professor',1365429312,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"defaultCA4K2R11.jpg\";s:7:\"creator\";s:17:\"Default Professor\";s:9:\"publisher\";s:17:\"Default Professor\";s:11:\"contributor\";s:17:\"Default Professor\";s:4:\"date\";s:10:\"2013/04/08\";s:4:\"type\";s:4:\"file\";}'),(14,'www/themes/default/images/logo/cesba.jpg','admin',1396550447,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"cesba.jpg\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/03\";s:4:\"type\";s:4:\"file\";}'),(15,'www/themes/default/images/logo/cesba.jpg','admin',1396552057,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"cesba.jpg\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/03\";s:4:\"type\";s:4:\"file\";}'),(16,'www/themes/default/images/avatars/system_avatars/user1.png','admin',1396552326,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"user1.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/03\";s:4:\"type\";s:4:\"file\";}'),(17,'www/themes/default/images/avatars/system_avatars/sportscar.png','admin',1396557924,NULL,0,755,0,'a:6:{s:5:\"title\";s:13:\"sportscar.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/03\";s:4:\"type\";s:4:\"file\";}'),(18,'www/themes/default/images/avatars/system_avatars/angel.png','admin',1397102904,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"angel.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(19,'www/content/lessons/11/materiales/EDU-101.pdf','jvazquez',1397104359,NULL,0,755,0,'a:6:{s:5:\"title\";s:11:\"EDU-101.pdf\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/09\";s:4:\"type\";s:4:\"file\";}'),(20,'www/themes/default/images/logo/logo.png','admin',1397105543,NULL,0,755,0,'a:6:{s:5:\"title\";s:8:\"logo.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(21,'www/themes/default/images/logo/COMPAGINADO_400.png','admin',1397106983,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_400.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(22,'www/themes/default/images/logo/COMPAGINADO_800.png','admin',1397107022,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_800.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(23,'www/themes/default/images/logo/COMPAGINADO_400.png','admin',1397107097,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_400.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(24,'www/themes/default/images/logo/COMPAGINADO_800.png','admin',1397107119,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_800.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(25,'www/themes/default/images/logo/COMPAGINADO_800.png','admin',1397107147,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_800.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(26,'upload/jvazquez/message_attachments/Sent/1397107465/logo.png','jvazquez',1397107465,NULL,0,755,0,'a:6:{s:5:\"title\";s:8:\"logo.png\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(27,'upload/cmonterrosa/message_attachments/Incoming/1397107465/logo.png','jvazquez',1397107465,NULL,0,755,0,'a:6:{s:5:\"title\";s:8:\"logo.png\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(28,'backups/temp/lessons/11/materiales/EDU-101.pdf','admin',1397108668,NULL,0,755,0,'a:6:{s:5:\"title\";s:11:\"EDU-101.pdf\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/09\";s:4:\"type\";s:4:\"file\";}'),(29,'backups/temp/upload/jvazquez/message_attachments/Sent/1397107465/logo.png','admin',1397108668,NULL,0,755,0,'a:6:{s:5:\"title\";s:8:\"logo.png\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(30,'backups/temp/upload/cmonterrosa/message_attachments/Incoming/1397107465/logo.png','admin',1397108668,NULL,0,755,0,'a:6:{s:5:\"title\";s:8:\"logo.png\";s:7:\"creator\";s:14:\"Jesus Vázquez\";s:9:\"publisher\";s:14:\"Jesus Vázquez\";s:11:\"contributor\";s:14:\"Jesus Vázquez\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(31,'www/themes/default/images/logo/COMPAGINADO_800.png','admin',1397110480,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_800.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(32,'www/themes/default/images/logo/COMPAGINADO_800.png','admin',1397110504,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_800.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(33,'www/themes/default/images/logo/COMPAGINADO_800.png','admin',1397110604,NULL,0,755,0,'a:6:{s:5:\"title\";s:19:\"COMPAGINADO_800.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/10\";s:4:\"type\";s:4:\"file\";}'),(34,'www/themes/default/images/avatars/system_avatars/sportscar.png','admin',1398358736,NULL,0,755,0,'a:6:{s:5:\"title\";s:13:\"sportscar.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(35,'www/themes/default/images/avatars/system_avatars/sportscar.png','admin',1398358741,NULL,0,755,0,'a:6:{s:5:\"title\";s:13:\"sportscar.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(36,'www/themes/default/images/avatars/system_avatars/sportscar.png','admin',1398359162,NULL,0,755,0,'a:6:{s:5:\"title\";s:13:\"sportscar.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(37,'www/themes/default/images/avatars/system_avatars/user1.png','admin',1398359399,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"user1.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(38,'www/themes/default/images/avatars/system_avatars/user1.png','admin',1398359716,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"user1.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(39,'www/themes/default/images/avatars/system_avatars/user1.png','admin',1398359717,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"user1.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(40,'www/themes/default/images/avatars/system_avatars/angel.png','admin',1398359747,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"angel.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(41,'www/themes/default/images/avatars/system_avatars/angel.png','admin',1398359815,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"angel.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(42,'www/themes/default/images/avatars/system_avatars/angel.png','admin',1398359848,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"angel.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}'),(43,'www/themes/default/images/avatars/system_avatars/angel.png','admin',1398359967,NULL,0,755,0,'a:6:{s:5:\"title\";s:9:\"angel.png\";s:7:\"creator\";s:20:\"System Administrator\";s:9:\"publisher\";s:20:\"System Administrator\";s:11:\"contributor\";s:20:\"System Administrator\";s:4:\"date\";s:10:\"2014/04/24\";s:4:\"type\";s:4:\"file\";}');
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glossary`
--

DROP TABLE IF EXISTS `glossary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glossary` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `lessons_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `info` text,
  `type` varchar(20) NOT NULL DEFAULT 'general',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glossary`
--

LOCK TABLES `glossary` WRITE;
/*!40000 ALTER TABLE `glossary` DISABLE KEYS */;
/*!40000 ALTER TABLE `glossary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `dynamic` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(10) unsigned DEFAULT NULL,
  `user_types_ID` varchar(50) DEFAULT '0',
  `languages_NAME` varchar(50) DEFAULT NULL,
  `users_active` tinyint(1) DEFAULT '0',
  `assign_profile_to_new` tinyint(1) DEFAULT '0',
  `unique_key` varchar(255) DEFAULT '',
  `is_default` tinyint(1) DEFAULT '0',
  `self_enroll` tinyint(1) DEFAULT '0',
  `key_max_usage` mediumint(8) unsigned DEFAULT '0',
  `key_current_usage` mediumint(8) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'lcc2013','Ciencias de la Educación 2013',1,0,NULL,'0',NULL,0,0,'',NULL,NULL,0,0);
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `translation` varchar(50) DEFAULT NULL,
  `rtl` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'albanian',0,'Shqipe',0),(2,'arabic',0,'العربية',0),(3,'brazilian',0,'Brasileira',0),(4,'bulgarian',0,'Български',0),(5,'catalan',0,'Català',0),(6,'chinese_simplified',0,'中国简化',0),(7,'chinese_traditional',0,'中國傳統',0),(8,'croatian',0,'Hrvatski',0),(9,'czech',0,'Česky',0),(10,'danish',0,'Dansk',0),(11,'dutch',0,'Nederlands',0),(12,'greek',0,'Eλληνικά',0),(13,'english',0,'English',0),(14,'filipino',0,'Filipino',0),(15,'finnish',0,'Suomi',0),(16,'french',0,'Français',0),(17,'galician',0,'Galego',0),(18,'georgian',0,'ქართული',0),(19,'german',0,'Deutsch',0),(20,'hebrew',0,'עברית',0),(21,'hindi',0,'हिन्दी',0),(22,'hungarian',0,'Magyar',0),(23,'indonesian',0,'Indonesia',0),(24,'italian',0,'Italiano',0),(25,'japanese',0,'日本語',0),(26,'korean',0,'한국어',0),(27,'latin_american',0,'Latinoamérica',0),(28,'latvian',0,'Latviešu',0),(29,'lithuanian',0,'Lietuviškai',0),(30,'norwegian',0,'Norsk',0),(31,'persian',0,'فارسی',0),(32,'polish',0,'Polski',0),(33,'portuguese',0,'Português',0),(34,'romanian',0,'Română',0),(35,'russian',0,'Pусский',0),(36,'serbian',0,'Српски',0),(37,'slovak',0,'Slovenčina',0),(38,'slovenian',0,'Slovenski',0),(39,'spanish',1,'Español',0),(40,'swedish',0,'Svenska',0),(41,'thai',0,'ไทย',0),(42,'turkish',0,'Türkçe',0),(43,'ukrainian',0,'Українське',0),(44,'vietnamese',0,'Việt',0);
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_conditions`
--

DROP TABLE IF EXISTS `lesson_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesson_conditions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `lessons_ID` mediumint(8) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `options` text,
  `relation` varchar(255) NOT NULL DEFAULT 'and',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_conditions`
--

LOCK TABLES `lesson_conditions` WRITE;
/*!40000 ALTER TABLE `lesson_conditions` DISABLE KEYS */;
INSERT INTO `lesson_conditions` VALUES (12,12,'all_units',NULL,'and'),(13,13,'all_units',NULL,'and'),(14,14,'all_units',NULL,'and'),(15,11,'all_tests',NULL,'and');
/*!40000 ALTER TABLE `lesson_conditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `directions_ID` mediumint(8) unsigned DEFAULT '0',
  `info` text,
  `price` float DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `show_catalog` tinyint(1) NOT NULL DEFAULT '1',
  `duration` int(10) DEFAULT '0',
  `access_limit` int(10) DEFAULT '0',
  `options` text,
  `languages_NAME` varchar(50) NOT NULL,
  `metadata` text,
  `course_only` tinyint(1) DEFAULT '0',
  `certificate` text,
  `from_timestamp` int(10) unsigned DEFAULT NULL,
  `to_timestamp` int(10) unsigned DEFAULT NULL,
  `shift` tinyint(1) DEFAULT '0',
  `publish` tinyint(1) DEFAULT '1',
  `share_folder` int(10) DEFAULT '0',
  `created` int(10) unsigned DEFAULT NULL,
  `max_users` int(10) unsigned DEFAULT NULL,
  `archive` int(10) unsigned DEFAULT '0',
  `instance_source` mediumint(8) unsigned DEFAULT '0',
  `originating_course` mediumint(8) unsigned DEFAULT '0',
  `creator_LOGIN` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (11,'Introducción ',16,'a:2:{s:19:\"general_description\";s:30:\"Introducción a la Pedagogía \";s:10:\"other_info\";s:56:\"Se requiere un promedio de 70 % para aprobar la materia \";}',0,1,1,0,0,'a:39:{s:6:\"theory\";i:1;s:8:\"examples\";i:1;s:8:\"projects\";i:1;s:5:\"tests\";i:0;s:6:\"survey\";i:1;s:5:\"rules\";i:1;s:5:\"forum\";i:0;s:8:\"comments\";i:0;s:4:\"news\";i:0;s:6:\"online\";i:1;s:5:\"scorm\";i:0;s:3:\"ims\";i:1;s:6:\"tincan\";i:1;s:15:\"dynamic_periods\";i:0;s:15:\"digital_library\";i:1;s:8:\"calendar\";i:0;s:11:\"new_content\";i:1;s:8:\"glossary\";i:1;s:7:\"reports\";i:1;s:8:\"tracking\";i:1;s:13:\"auto_complete\";i:1;s:12:\"content_tree\";i:1;s:11:\"lesson_info\";i:1;s:11:\"bookmarking\";i:0;s:14:\"content_report\";i:0;s:13:\"print_content\";i:0;s:12:\"start_resume\";i:1;s:15:\"show_percentage\";i:1;s:14:\"show_right_bar\";i:1;s:13:\"show_left_bar\";i:0;s:19:\"show_student_cpanel\";i:1;s:18:\"recurring_duration\";i:0;s:18:\"show_content_tools\";i:1;s:14:\"show_dashboard\";i:1;s:19:\"show_horizontal_bar\";i:1;s:17:\"default_positions\";s:0:\"\";s:8:\"feedback\";i:1;s:13:\"smart_content\";i:1;s:6:\"timers\";i:0;}','spanish','a:7:{s:5:\"title\";s:8:\"Unidad 1\";s:7:\"creator\";s:17:\"Ulloa M. (mulloa)\";s:9:\"publisher\";s:17:\"Ulloa M. (mulloa)\";s:11:\"contributor\";s:17:\"Ulloa M. (mulloa)\";s:4:\"date\";s:10:\"2014/04/09\";s:8:\"language\";s:8:\"Español\";s:4:\"type\";s:6:\"lesson\";}',1,'',0,0,0,1,0,1397103373,NULL,0,0,0,'mulloa'),(12,'Conceptos básicos',16,'a:0:{}',0,1,1,0,0,'a:0:{}','spanish','a:7:{s:5:\"title\";s:18:\"Conceptos básicos\";s:7:\"creator\";s:24:\"Administrator S. (admin)\";s:9:\"publisher\";s:24:\"Administrator S. (admin)\";s:11:\"contributor\";s:24:\"Administrator S. (admin)\";s:4:\"date\";s:10:\"2014/04/10\";s:8:\"language\";s:8:\"Español\";s:4:\"type\";s:6:\"lesson\";}',1,'',0,0,0,1,0,1397106385,0,0,0,0,'admin'),(13,'Introducción al Derecho',20,'a:0:{}',0,1,1,0,0,'a:39:{s:6:\"theory\";i:1;s:8:\"examples\";i:1;s:8:\"projects\";i:1;s:5:\"tests\";i:1;s:6:\"survey\";i:1;s:5:\"rules\";i:1;s:5:\"forum\";i:1;s:8:\"comments\";i:1;s:4:\"news\";i:1;s:6:\"online\";i:1;s:5:\"scorm\";i:1;s:3:\"ims\";i:1;s:6:\"tincan\";i:1;s:15:\"dynamic_periods\";i:0;s:15:\"digital_library\";i:1;s:8:\"calendar\";i:1;s:11:\"new_content\";i:1;s:8:\"glossary\";i:1;s:7:\"reports\";i:1;s:8:\"tracking\";i:1;s:13:\"auto_complete\";i:1;s:12:\"content_tree\";i:1;s:11:\"lesson_info\";i:1;s:11:\"bookmarking\";i:1;s:14:\"content_report\";i:0;s:13:\"print_content\";i:1;s:12:\"start_resume\";i:1;s:15:\"show_percentage\";i:1;s:14:\"show_right_bar\";i:1;s:13:\"show_left_bar\";i:0;s:19:\"show_student_cpanel\";i:1;s:18:\"recurring_duration\";i:0;s:18:\"show_content_tools\";i:1;s:14:\"show_dashboard\";i:1;s:19:\"show_horizontal_bar\";i:1;s:17:\"default_positions\";s:0:\"\";s:8:\"feedback\";i:1;s:13:\"smart_content\";i:1;s:6:\"timers\";i:0;}','spanish','a:7:{s:5:\"title\";s:24:\"Introducción al Derecho\";s:7:\"creator\";s:24:\"Administrator S. (admin)\";s:9:\"publisher\";s:24:\"Administrator S. (admin)\";s:11:\"contributor\";s:24:\"Administrator S. (admin)\";s:4:\"date\";s:10:\"2014/04/10\";s:8:\"language\";s:8:\"Español\";s:4:\"type\";s:6:\"lesson\";}',1,'',0,0,0,1,0,1397108217,NULL,0,0,0,'admin'),(14,'Jurisprudencia',20,'a:0:{}',0,1,1,0,0,'a:39:{s:6:\"theory\";i:1;s:8:\"examples\";i:1;s:8:\"projects\";i:1;s:5:\"tests\";i:1;s:6:\"survey\";i:1;s:5:\"rules\";i:1;s:5:\"forum\";i:1;s:8:\"comments\";i:1;s:4:\"news\";i:1;s:6:\"online\";i:1;s:5:\"scorm\";i:1;s:3:\"ims\";i:1;s:6:\"tincan\";i:1;s:15:\"dynamic_periods\";i:0;s:15:\"digital_library\";i:1;s:8:\"calendar\";i:1;s:11:\"new_content\";i:1;s:8:\"glossary\";i:1;s:7:\"reports\";i:1;s:8:\"tracking\";i:1;s:13:\"auto_complete\";i:1;s:12:\"content_tree\";i:1;s:11:\"lesson_info\";i:1;s:11:\"bookmarking\";i:1;s:14:\"content_report\";i:0;s:13:\"print_content\";i:1;s:12:\"start_resume\";i:1;s:15:\"show_percentage\";i:1;s:14:\"show_right_bar\";i:1;s:13:\"show_left_bar\";i:0;s:19:\"show_student_cpanel\";i:1;s:18:\"recurring_duration\";i:0;s:18:\"show_content_tools\";i:1;s:14:\"show_dashboard\";i:1;s:19:\"show_horizontal_bar\";i:1;s:17:\"default_positions\";s:0:\"\";s:8:\"feedback\";i:1;s:13:\"smart_content\";i:1;s:6:\"timers\";i:0;}','spanish','a:7:{s:5:\"title\";s:14:\"Jurisprudencia\";s:7:\"creator\";s:24:\"Administrator S. (admin)\";s:9:\"publisher\";s:24:\"Administrator S. (admin)\";s:11:\"contributor\";s:24:\"Administrator S. (admin)\";s:4:\"date\";s:10:\"2014/04/10\";s:8:\"language\";s:8:\"Español\";s:4:\"type\";s:6:\"lesson\";}',1,'',0,0,0,1,0,1397108234,NULL,0,0,0,'admin');
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons_timeline_topics`
--

DROP TABLE IF EXISTS `lessons_timeline_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons_timeline_topics` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `lessons_ID` mediumint(8) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons_timeline_topics`
--

LOCK TABLES `lessons_timeline_topics` WRITE;
/*!40000 ALTER TABLE `lessons_timeline_topics` DISABLE KEYS */;
/*!40000 ALTER TABLE `lessons_timeline_topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons_timeline_topics_data`
--

DROP TABLE IF EXISTS `lessons_timeline_topics_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons_timeline_topics_data` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `topics_ID` mediumint(8) unsigned NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons_timeline_topics_data`
--

LOCK TABLES `lessons_timeline_topics_data` WRITE;
/*!40000 ALTER TABLE `lessons_timeline_topics_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `lessons_timeline_topics_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons_to_courses`
--

DROP TABLE IF EXISTS `lessons_to_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons_to_courses` (
  `courses_ID` mediumint(8) unsigned NOT NULL,
  `lessons_ID` mediumint(8) unsigned NOT NULL,
  `previous_lessons_ID` mediumint(8) unsigned DEFAULT '0',
  `start_date` int(10) unsigned DEFAULT NULL,
  `end_date` int(10) unsigned DEFAULT NULL,
  `start_period` int(10) unsigned DEFAULT NULL,
  `end_period` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`lessons_ID`,`courses_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons_to_courses`
--

LOCK TABLES `lessons_to_courses` WRITE;
/*!40000 ALTER TABLE `lessons_to_courses` DISABLE KEYS */;
INSERT INTO `lessons_to_courses` VALUES (9,11,0,NULL,NULL,NULL,NULL),(9,12,11,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `lessons_to_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons_to_groups`
--

DROP TABLE IF EXISTS `lessons_to_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons_to_groups` (
  `lessons_ID` mediumint(8) unsigned NOT NULL,
  `user_type` varchar(50) DEFAULT 'student',
  `groups_ID` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`lessons_ID`,`groups_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons_to_groups`
--

LOCK TABLES `lessons_to_groups` WRITE;
/*!40000 ALTER TABLE `lessons_to_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `lessons_to_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `comments` varchar(32) NOT NULL DEFAULT '0',
  `session_ip` char(8) NOT NULL DEFAULT '0',
  `lessons_ID` mediumint(8) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `users_LOGIN` (`users_LOGIN`),
  KEY `action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (12,'admin',1396549675,'login','mthuksr87ni18etkghb8b11dq5','7f000001',0),(13,'admin',1396551882,'logout','0','7f000001',0),(14,'admin',1396552014,'login','ute1386rps0av1u467rgvbb447','7f000001',0),(15,'admin',1396552061,'logout','0','7f000001',0),(16,'admin',1396552112,'login','r7qno0thhnr12crvddion14c55','7f000001',0),(17,'admin',1396552181,'logout','0','7f000001',0),(18,'admin',1396552191,'login','itn74fhec8a2383fc3fg1203g2','7f000001',0),(19,'admin',1396556110,'logout','0','7f000001',0),(20,'admin',1396556161,'login','09bjs46dlu8qr4tp6o36ik33c2','7f000001',0),(21,'admin',1396557159,'logout','0','7f000001',0),(22,'admin',1396557191,'login','a5pfqou4nrh9q4p3up53bsobr5','7f000001',0),(23,'admin',1396557937,'logout','0','7f000001',0),(24,'jvazquez',1396557945,'login','hab7tgq7iovqp6athk9953p957','7f000001',0),(25,'jvazquez',1396558048,'logout','0','7f000001',0),(26,'jvazquez',1396558060,'login','87od51v9n77fgvjg5uuv2cpoc3','7f000001',0),(27,'jvazquez',1396558649,'logout','0','7f000001',0),(28,'admin',1396558655,'login','kcds4023hks2ngeno5ac294pu7','7f000001',0),(29,'admin',1396558921,'logout','0','7f000001',0),(30,'jvazquez',1396558927,'login','d0hqmp39qmsuhh17fk12eqif76','7f000001',0),(31,'jvazquez',1396559540,'logout','0','7f000001',0),(32,'cmonterrosa',1397101079,'login','tsdr3h9iqejfsg2uivab43ju47','c0a80417',0),(33,'cmonterrosa',1397101118,'logout','0','c0a80417',0),(34,'jvazquez',1397101129,'login','1dplef35ah12523u0qp5i3c8j6','c0a80417',0),(35,'jvazquez',1397101399,'logout','0','c0a80417',0),(36,'admin',1397101626,'login','6utav9qqqmnck8sr766i7f39m1','c0a80417',0),(37,'admin',1397102526,'logout','0','c0a80417',0),(38,'admin',1397102614,'login','pis5q30qbmbgiorscmja52o4a5','c0a80417',0),(39,'admin',1397102911,'logout','0','c0a80417',0),(40,'mulloa',1397102919,'login','enna4kk102malp9tdk2jpnv6a4','c0a80417',0),(41,'mulloa',1397102981,'logout','0','c0a80417',0),(42,'admin',1397102983,'login','al3kbe90t1epus998elcbmgca2','c0a80417',0),(43,'admin',1397103025,'logout','0','c0a80417',0),(44,'mulloa',1397103040,'login','613m4abmhn8sk2fgbqrrk1md75','c0a80417',0),(45,'mulloa',1397103707,'logout','0','c0a80417',0),(46,'jvazquez',1397103741,'login','0fh67suoju99lefphpr0tvn7c4','c0a80417',0),(47,'jvazquez',1397105108,'logout','0','c0a80417',0),(48,'admin',1397105112,'login','1sjn3cp6945nj188v9h13jfdl2','c0a80417',0),(49,'admin',1397105227,'logout','0','c0a80417',0),(50,'admin',1397105513,'login','i0v0o31absht0bghchq7bmgja2','c0a80417',0),(51,'admin',1397105567,'logout','0','c0a80417',0),(52,'admin',1397105962,'login','5juua1ob2lq7bok6md9sde13g1','c0a80417',0),(53,'admin',1397106165,'logout','0','c0a80417',0),(54,'admin',1397106203,'login','ud2ftmmgsv9m8c2k3tr9opvqk6','c0a80417',0),(55,'admin',1397106533,'logout','0','c0a80417',0),(56,'jvazquez',1397106542,'login','vfn6pbsajlgkru7n2sd49r82a3','c0a80417',0),(57,'jvazquez',1397106802,'logout','0','c0a80417',0),(58,'admin',1397106822,'login','bq9uurembuq0a0ecdur0j59895','c0a80417',0),(59,'admin',1397106924,'logout','0','c0a80417',0),(60,'admin',1397106942,'login','3iu3g0kjatfrau25ae7o9o6go0','c0a80417',0),(61,'admin',1397107032,'logout','0','c0a80417',0),(62,'admin',1397107058,'login','oequsrdqj33hp7euo18p7000d3','c0a80417',0),(63,'admin',1397107200,'logout','0','c0a80417',0),(64,'cmonterrosa',1397107209,'login','2hgohdqgggaiitmpgl88bjous5','c0a80417',0),(65,'cmonterrosa',1397107369,'logout','0','c0a80417',0),(66,'jvazquez',1397107381,'login','dk2n1vvgetjtmoijg7najkikr7','c0a80417',0),(67,'jvazquez',1397107563,'logout','0','c0a80417',0),(68,'cmonterrosa',1397107574,'login','vnsibl1em7f2bi2772ruism340','c0a80417',0),(69,'cmonterrosa',1397107697,'logout','0','c0a80417',0),(70,'admin',1397107699,'login','4f6dtp4o96p406nvhtbpv87gd5','c0a80417',0),(71,'admin',1397108391,'logout','0','c0a80417',0),(72,'jvazquez',1397108437,'login','cd0sbrkhktic0151ckf8pum8j6','c0a80417',0),(73,'jvazquez',1397108525,'logout','0','c0a80417',0),(74,'admin',1397108536,'login','peo6q47atofqsg2i9ab5far6u5','c0a80417',0),(75,'admin',1397109022,'logout','0','c0a80417',0),(76,'admin',1397109059,'login','4fbll1uq7rbfsvdhkhbkproqi1','c0a80417',0),(77,'admin',1397109499,'logout','0','c0a80417',0),(78,'cmonterrosa',1397109532,'login','r1cjnu8djpm6hi52kh4h9rp7b2','c0a80417',0),(79,'cmonterrosa',1397109651,'logout','0','c0a80417',0),(80,'jvazquez',1397109685,'login','erg776hhnigogjf1810es0trn6','c0a80417',0),(81,'jvazquez',1397109792,'logout','0','c0a80417',0),(82,'admin',1397109799,'login','b2ps8dftsng9glcsl8k6oml317','c0a80417',0),(83,'admin',1397110002,'logout','0','c0a80417',0),(84,'jvazquez',1397110022,'login','o8j90sgokamlleadd0nddr49t0','c0a80417',0),(85,'jvazquez',1397110411,'logout','0','c0a80417',0),(86,'admin',1397110445,'login','vhvgjnp2inju2qpo7i5kd4gj13','c0a80417',0),(87,'admin',1397110614,'logout','0','c0a80417',0),(88,'mulloa',1397151661,'login','aq0u2os6raqqmiocgn7b2gm8o6','c0a80417',0),(89,'mulloa',1397151785,'logout','0','c0a80417',0),(90,'mulloa',1397152002,'login','mso44eo0cb9o87tvjdv3r09jo1','c0a80417',0),(91,'mulloa',1397152396,'logout','0','c0a80417',0),(92,'jvazquez',1397152426,'login','65q0o83999hbl7kmmokhk99ag2','c0a80417',0),(93,'jvazquez',1397152652,'logout','0','c0a80417',0),(94,'cmonterrosa',1397152666,'login','s059f6210enp9b991gupb7u7n5','c0a80417',0),(95,'admin',1398358658,'login','9qg4apmc7vgpr55o0k328ke373','c0a80417',0),(96,'cmonterrosa',1398358659,'logout','0','c0a80417',0),(97,'admin',1398359173,'logout','0','c0a80417',0),(98,'jvazquez',1398359237,'login','a3bmuapd8f0d72o1ppnnmuao14','c0a80417',0),(99,'jvazquez',1398359245,'logout','0','c0a80417',0),(100,'admin',1398359301,'login','qrleqmv8g7jo78a9f39dophlh4','c0a80417',0),(101,'admin',1398360071,'logout','0','c0a80417',0),(102,'mulloa',1398360092,'login','35thf5i0jq55ghm85a4btrgcr2','c0a80417',0),(103,'mulloa',1398360916,'logout','0','c0a80417',0),(104,'mulloa',1398360916,'login','salbd8f8mvkbd97thb479qfcm3','c0a80417',0),(105,'jvazquez',1398360956,'login','a1fn8la8ros37s3edidjvkene0','c0a80417',0),(106,'mulloa',1398439480,'logout','0','c0a80417',0),(107,'mulloa',1398439480,'login','n4auil2jks6to461hb9uqj0v25','c0a80417',0),(108,'jvazquez',1398439480,'logout','0','c0a80417',0),(109,'admin',1398536405,'login','vnaq6e3mdjri2cuo9bt480khj4','c0a80417',0),(110,'mulloa',1398536405,'logout','0','c0a80417',0),(111,'admin',1398536439,'logout','0','c0a80417',0);
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_bbb`
--

DROP TABLE IF EXISTS `module_bbb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_bbb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `lessons_ID` int(11) NOT NULL,
  `confKey` varchar(255) NOT NULL,
  `durationHours` int(1) NOT NULL,
  `durationMinutes` int(2) DEFAULT NULL,
  `confType` tinyint(1) DEFAULT '0',
  `maxParts` int(3) DEFAULT '20',
  `maxMics` int(3) DEFAULT '20',
  `lobby` tinyint(1) DEFAULT '0',
  `status` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_bbb`
--

LOCK TABLES `module_bbb` WRITE;
/*!40000 ALTER TABLE `module_bbb` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_bbb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_bbb_users_to_meeting`
--

DROP TABLE IF EXISTS `module_bbb_users_to_meeting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_bbb_users_to_meeting` (
  `users_LOGIN` varchar(255) NOT NULL,
  `meeting_ID` int(11) NOT NULL,
  KEY `users_LOGIN` (`users_LOGIN`,`meeting_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_bbb_users_to_meeting`
--

LOCK TABLES `module_bbb_users_to_meeting` WRITE;
/*!40000 ALTER TABLE `module_bbb_users_to_meeting` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_bbb_users_to_meeting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_billboard`
--

DROP TABLE IF EXISTS `module_billboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_billboard` (
  `lessons_ID` int(11) NOT NULL,
  `data` longtext,
  PRIMARY KEY (`lessons_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_billboard`
--

LOCK TABLES `module_billboard` WRITE;
/*!40000 ALTER TABLE `module_billboard` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_billboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_blogs`
--

DROP TABLE IF EXISTS `module_blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `lessons_ID` int(11) NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(255) NOT NULL,
  `description` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `registered` tinyint(1) NOT NULL DEFAULT '1',
  `timestamp` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_blogs`
--

LOCK TABLES `module_blogs` WRITE;
/*!40000 ALTER TABLE `module_blogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_blogs_articles`
--

DROP TABLE IF EXISTS `module_blogs_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_blogs_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `blogs_ID` int(11) NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(255) NOT NULL,
  `timestamp` varchar(10) NOT NULL,
  `data` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_blogs_articles`
--

LOCK TABLES `module_blogs_articles` WRITE;
/*!40000 ALTER TABLE `module_blogs_articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_blogs_articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_blogs_comments`
--

DROP TABLE IF EXISTS `module_blogs_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_blogs_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blogs_articles_ID` int(11) NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(255) NOT NULL,
  `timestamp` varchar(10) NOT NULL,
  `data` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_blogs_comments`
--

LOCK TABLES `module_blogs_comments` WRITE;
/*!40000 ALTER TABLE `module_blogs_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_blogs_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_blogs_users`
--

DROP TABLE IF EXISTS `module_blogs_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_blogs_users` (
  `blogs_ID` int(11) NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(255) NOT NULL,
  PRIMARY KEY (`users_LOGIN`,`blogs_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_blogs_users`
--

LOCK TABLES `module_blogs_users` WRITE;
/*!40000 ALTER TABLE `module_blogs_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_blogs_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_chat`
--

DROP TABLE IF EXISTS `module_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_chat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_user` varchar(255) NOT NULL DEFAULT '',
  `to_user` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `isLesson` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_chat`
--

LOCK TABLES `module_chat` WRITE;
/*!40000 ALTER TABLE `module_chat` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_chat_config`
--

DROP TABLE IF EXISTS `module_chat_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_chat_config` (
  `status` int(11) NOT NULL DEFAULT '1',
  `chatHeartbeatTime` int(11) NOT NULL DEFAULT '1500',
  `refresh_rate` int(11) NOT NULL DEFAULT '60000'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_chat_config`
--

LOCK TABLES `module_chat_config` WRITE;
/*!40000 ALTER TABLE `module_chat_config` DISABLE KEYS */;
INSERT INTO `module_chat_config` VALUES (1,2000,30000);
/*!40000 ALTER TABLE `module_chat_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_chat_users`
--

DROP TABLE IF EXISTS `module_chat_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_chat_users` (
  `username` varchar(100) NOT NULL,
  `timestamp_` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_chat_users`
--

LOCK TABLES `module_chat_users` WRITE;
/*!40000 ALTER TABLE `module_chat_users` DISABLE KEYS */;
INSERT INTO `module_chat_users` VALUES ('admin','2014-04-03 18:48:40'),('jvazquez','2014-04-03 20:45:46'),('cmonterrosa','2014-04-10 03:37:59'),('mulloa','2014-04-10 04:08:41');
/*!40000 ALTER TABLE `module_chat_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_crossword_users`
--

DROP TABLE IF EXISTS `module_crossword_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_crossword_users` (
  `users_LOGIN` varchar(100) NOT NULL,
  `content_ID` mediumint(11) NOT NULL,
  `crosslists_ID` mediumint(11) NOT NULL DEFAULT '0',
  `success` mediumint(11) NOT NULL DEFAULT '0',
  `points` varchar(50) NOT NULL,
  `totallength` varchar(50) NOT NULL,
  `wordtime` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_crossword_users`
--

LOCK TABLES `module_crossword_users` WRITE;
/*!40000 ALTER TABLE `module_crossword_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_crossword_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_crossword_words`
--

DROP TABLE IF EXISTS `module_crossword_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_crossword_words` (
  `content_ID` int(10) unsigned NOT NULL,
  `crosslists` text,
  `options` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_crossword_words`
--

LOCK TABLES `module_crossword_words` WRITE;
/*!40000 ALTER TABLE `module_crossword_words` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_crossword_words` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_faq`
--

DROP TABLE IF EXISTS `module_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_faq` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lessons_ID` int(11) NOT NULL,
  `unit_ID` int(11) DEFAULT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_faq`
--

LOCK TABLES `module_faq` WRITE;
/*!40000 ALTER TABLE `module_faq` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_faq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_flashcards_decks`
--

DROP TABLE IF EXISTS `module_flashcards_decks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_flashcards_decks` (
  `content_ID` int(10) unsigned NOT NULL,
  `cards` text,
  `options` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_flashcards_decks`
--

LOCK TABLES `module_flashcards_decks` WRITE;
/*!40000 ALTER TABLE `module_flashcards_decks` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_flashcards_decks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_flashcards_users_to_cards`
--

DROP TABLE IF EXISTS `module_flashcards_users_to_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_flashcards_users_to_cards` (
  `users_LOGIN` varchar(100) NOT NULL,
  `content_ID` mediumint(11) NOT NULL,
  `cards_ID` mediumint(11) NOT NULL,
  `success` mediumint(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_flashcards_users_to_cards`
--

LOCK TABLES `module_flashcards_users_to_cards` WRITE;
/*!40000 ALTER TABLE `module_flashcards_users_to_cards` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_flashcards_users_to_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_gradebook_grades`
--

DROP TABLE IF EXISTS `module_gradebook_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_gradebook_grades` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `oid` int(11) NOT NULL,
  `grade` float NOT NULL,
  `users_LOGIN` varchar(255) NOT NULL,
  PRIMARY KEY (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_gradebook_grades`
--

LOCK TABLES `module_gradebook_grades` WRITE;
/*!40000 ALTER TABLE `module_gradebook_grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_gradebook_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_gradebook_objects`
--

DROP TABLE IF EXISTS `module_gradebook_objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_gradebook_objects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `weight` int(2) NOT NULL,
  `refers_to_type` varchar(50) NOT NULL,
  `refers_to_id` int(11) NOT NULL,
  `lessons_ID` int(11) NOT NULL,
  `creator` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_gradebook_objects`
--

LOCK TABLES `module_gradebook_objects` WRITE;
/*!40000 ALTER TABLE `module_gradebook_objects` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_gradebook_objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_gradebook_ranges`
--

DROP TABLE IF EXISTS `module_gradebook_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_gradebook_ranges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `range_from` int(3) NOT NULL,
  `range_to` int(3) NOT NULL,
  `grade` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_gradebook_ranges`
--

LOCK TABLES `module_gradebook_ranges` WRITE;
/*!40000 ALTER TABLE `module_gradebook_ranges` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_gradebook_ranges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_gradebook_users`
--

DROP TABLE IF EXISTS `module_gradebook_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_gradebook_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(255) NOT NULL,
  `lessons_ID` int(11) NOT NULL,
  `score` float NOT NULL,
  `grade` varchar(50) NOT NULL,
  `publish` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_gradebook_users`
--

LOCK TABLES `module_gradebook_users` WRITE;
/*!40000 ALTER TABLE `module_gradebook_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_gradebook_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_journal_entries`
--

DROP TABLE IF EXISTS `module_journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_journal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_body` text NOT NULL,
  `entry_date` datetime NOT NULL,
  `lessons_ID` int(11) NOT NULL,
  `users_LOGIN` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_journal_entries`
--

LOCK TABLES `module_journal_entries` WRITE;
/*!40000 ALTER TABLE `module_journal_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_journal_rules`
--

DROP TABLE IF EXISTS `module_journal_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_journal_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_journal_rules`
--

LOCK TABLES `module_journal_rules` WRITE;
/*!40000 ALTER TABLE `module_journal_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_journal_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_journal_settings`
--

DROP TABLE IF EXISTS `module_journal_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_journal_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_journal_settings`
--

LOCK TABLES `module_journal_settings` WRITE;
/*!40000 ALTER TABLE `module_journal_settings` DISABLE KEYS */;
INSERT INTO `module_journal_settings` VALUES (1,'export',1),(2,'preview',1);
/*!40000 ALTER TABLE `module_journal_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_links`
--

DROP TABLE IF EXISTS `module_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lessons_ID` int(11) NOT NULL,
  `display` varchar(500) NOT NULL,
  `link` varchar(500) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_links`
--

LOCK TABLES `module_links` WRITE;
/*!40000 ALTER TABLE `module_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_outlook_invitation`
--

DROP TABLE IF EXISTS `module_outlook_invitation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_outlook_invitation` (
  `courses_ID` int(11) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `duration` int(10) unsigned NOT NULL,
  `description` text,
  `location` text,
  `subject` varchar(255) DEFAULT 'Invitation to attend training',
  `sequence` int(11) DEFAULT '0',
  PRIMARY KEY (`courses_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_outlook_invitation`
--

LOCK TABLES `module_outlook_invitation` WRITE;
/*!40000 ALTER TABLE `module_outlook_invitation` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_outlook_invitation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_quote`
--

DROP TABLE IF EXISTS `module_quote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_quote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lessons_ID` int(11) NOT NULL,
  `quote` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_quote`
--

LOCK TABLES `module_quote` WRITE;
/*!40000 ALTER TABLE `module_quote` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_quote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_rss_feeds`
--

DROP TABLE IF EXISTS `module_rss_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_rss_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `url` text NOT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `only_summary` int(11) DEFAULT '0',
  `lessons_ID` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_rss_feeds`
--

LOCK TABLES `module_rss_feeds` WRITE;
/*!40000 ALTER TABLE `module_rss_feeds` DISABLE KEYS */;
INSERT INTO `module_rss_feeds` VALUES (1,'eFront news','http://www.efrontlearning.net/product/efront-news?format=feed&type=rss&install=1',1,0,-1);
/*!40000 ALTER TABLE `module_rss_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_rss_provider`
--

DROP TABLE IF EXISTS `module_rss_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_rss_provider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mode` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `lessons_ID` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_rss_provider`
--

LOCK TABLES `module_rss_provider` WRITE;
/*!40000 ALTER TABLE `module_rss_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_rss_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_workbook_answers`
--

DROP TABLE IF EXISTS `module_workbook_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_workbook_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `html_solved` text,
  `users_LOGIN` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_workbook_answers`
--

LOCK TABLES `module_workbook_answers` WRITE;
/*!40000 ALTER TABLE `module_workbook_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_workbook_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_workbook_autosave`
--

DROP TABLE IF EXISTS `module_workbook_autosave`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_workbook_autosave` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `autosave_text` longtext NOT NULL,
  `users_LOGIN` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_workbook_autosave`
--

LOCK TABLES `module_workbook_autosave` WRITE;
/*!40000 ALTER TABLE `module_workbook_autosave` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_workbook_autosave` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_workbook_items`
--

DROP TABLE IF EXISTS `module_workbook_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_workbook_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_title` varchar(255) DEFAULT NULL,
  `item_text` text,
  `item_question` int(11) NOT NULL,
  `question_text` longtext,
  `check_answer` tinyint(1) NOT NULL,
  `lessons_ID` int(11) NOT NULL,
  `unique_ID` varchar(50) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_workbook_items`
--

LOCK TABLES `module_workbook_items` WRITE;
/*!40000 ALTER TABLE `module_workbook_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_workbook_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_workbook_progress`
--

DROP TABLE IF EXISTS `module_workbook_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_workbook_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lessons_ID` int(11) NOT NULL,
  `users_LOGIN` varchar(255) NOT NULL,
  `progress` float(5,2) NOT NULL,
  `non_optional` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_workbook_progress`
--

LOCK TABLES `module_workbook_progress` WRITE;
/*!40000 ALTER TABLE `module_workbook_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_workbook_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_workbook_publish`
--

DROP TABLE IF EXISTS `module_workbook_publish`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_workbook_publish` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lessons_ID` int(11) NOT NULL,
  `publish` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_workbook_publish`
--

LOCK TABLES `module_workbook_publish` WRITE;
/*!40000 ALTER TABLE `module_workbook_publish` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_workbook_publish` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_workbook_settings`
--

DROP TABLE IF EXISTS `module_workbook_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_workbook_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lessons_ID` int(11) NOT NULL,
  `lesson_name` varchar(255) NOT NULL,
  `allow_print` tinyint(1) NOT NULL DEFAULT '1',
  `allow_export` tinyint(1) NOT NULL DEFAULT '1',
  `edit_answers` tinyint(1) NOT NULL DEFAULT '1',
  `unit_to_complete` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_workbook_settings`
--

LOCK TABLES `module_workbook_settings` WRITE;
/*!40000 ALTER TABLE `module_workbook_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_workbook_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_youtube`
--

DROP TABLE IF EXISTS `module_youtube`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_youtube` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lessons_ID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_youtube`
--

LOCK TABLES `module_youtube` WRITE;
/*!40000 ALTER TABLE `module_youtube` DISABLE KEYS */;
/*!40000 ALTER TABLE `module_youtube` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `className` varchar(150) NOT NULL,
  `db_file` varchar(255) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `title` varchar(150) NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `description` text,
  `position` varchar(150) NOT NULL,
  `menu` varchar(255) DEFAULT NULL,
  `mandatory` varchar(255) DEFAULT NULL,
  `permissions` varchar(32) NOT NULL DEFAULT 'administrator',
  PRIMARY KEY (`className`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES ('module_administrator_tools','','module_administrator_tools',0,'Administrator tools','Periklis Venakis','1.10','This module is a collection of administrator tools','module_administrator_tools',NULL,NULL,'administrator'),('module_bbb','','module_bbb',0,'BigBlueButton conference tool','Panagiotis Athanasopoulos','1.2','\n		This module is used to connect eFront with BigBlueButton for lesson conferencing using the included API.\n	','module_bbb',NULL,NULL,'administrator,professor,student'),('module_billboard','','module_billboard',0,'Billboard','Nick Baltas','1.3','\n		This module is used to create and display a billboard for eFront lessons.\n	','module_billboard',NULL,NULL,'professor,student'),('module_blogs','','module_blogs',0,'Blogs','Michael Makrigiannakis','1.1','This module is used to place blogs functionality in eFront lessons','module_blogs',NULL,NULL,'student,professor,administrator'),('module_bootstrap','','module_bootstrap',0,'Module Bootstap','Periklis Venakis','1.0','Bootstrap module quickly sets up a new module by putting together the minimum code required','module_bootstrap',NULL,NULL,'administrator'),('module_chat','','module_chat',1,'Chat Module ','Christos Xanthos','1.1','eFront integrated Chat Bar','module_chat',NULL,NULL,'administrator,professor,student'),('module_crossword','','module_crossword',0,'Crossword','skippybosco','1.3','Create Crossword Puzzles from Empty Space Questions','module_crossword',NULL,NULL,'student,professor,administrator'),('module_export_unit','','module_export_unit',0,'Export unit','Periklis Venakis','1.0','A module that exports units to HTML','module_export_unit',NULL,NULL,'professor'),('module_faq','','module_faq',0,'Frequently Asked Questions','Nick Baltas','1.5','\n		This module is used to create and display FAQ lists for eFront lessons.\n	','module_faq',NULL,NULL,'professor,student'),('module_flashcards','','module_flashcards',0,'Flashcards','Michael Makrigiannakis','1.0','This module is used to place flashcards in eFront questions','module_flashcards',NULL,NULL,'student,professor,administrator'),('module_gift_aiken','','module_gift_aiken',0,'GIFT/AIKEN Questions Import','Nick Baltas','1.4','\n		This module is used to create questions from GIFT/AIKEN formats\n	','module_gift_aiken',NULL,NULL,'professor'),('module_gradebook','','module_gradebook',0,'GradeBook','Andreas Makridakis','1.0','A module for handling the grades in each lesson','module_gradebook',NULL,NULL,'student,professor,administrator'),('module_idle_users','','module_idle_users',0,'Idle users','Periklis Venakis','1.1','A module to display idle users, per branch','module_idle_users',NULL,NULL,'administrator,professor,student'),('module_info_kiosk','','module_info_kiosk',0,'Info-kiosk','Periklis Venakis','1.4','A module that allows for uploading files to be visible for all users to see','module_info_kiosk',NULL,NULL,'administrator,professor,student'),('module_journal','','module_journal',0,'Journal','Andreas Makridakis','1.0','A common Journal per student/professor','module_journal',NULL,NULL,'student,professor,administrator'),('module_links','','module_links',0,'Links','Panagiotis Antonellis','1.4','\n		This module is used to create and display a list of useful links\n	','module_links',NULL,NULL,'professor,student'),('module_outlook_invitation','','module_outlook_invitation',0,'Outlook invitation Module','Michael Makrigiannakis','1.1.6','This module allows you to create events attached to a specific course. Whenever a user is assigned to a course with an attached Event, eFront will automatically send him an outlook calendar event','module_outlook_invitation',NULL,NULL,'administrator'),('module_quick_mails','','module_quick_mails',0,'Quick emails','Michael Makrigiannakis','1.2','\n		This module is used to send emails directly to professors students etc\n	','module_quick_mails',NULL,NULL,'student,professor'),('module_quote','','module_quote',0,'Quote of the day','Panagiotis Antonellis','1.1','\n		This module is used to display the quote of the day\n	','module_quote',NULL,NULL,'professor,student'),('module_rss','','module_rss',0,'RSS','Periklis Venakis','1.8','An RSS module for eFront','module_rss',NULL,NULL,'administrator,professor,student'),('module_security','','module_security',1,'Security Module','Periklis Venakis','1.0','Security module','module_security',NULL,NULL,'administrator'),('module_workbook','','module_workbook',0,'WorkBook','Andreas Makridakis','1.1','Through Workbook module professors are trying to get students to pay attention to certain lesson items','module_workbook',NULL,NULL,'student,professor'),('module_youtube','','module_youtube',0,'YouTube','Nick Baltas','1.2','\n		This module is used to connect eFront with YouTube\n	','module_youtube',NULL,NULL,'professor,student');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `data` text,
  `timestamp` int(10) unsigned DEFAULT '0',
  `expire` int(10) unsigned DEFAULT '0',
  `lessons_ID` mediumint(8) unsigned DEFAULT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'¡No te quedes sin inscribirte!','<p>CESBA no cobrará el pago de inscripción para los alumnos hasta el día 30 de abril de 2014. No esperes más.</p>',1397108700,1399700700,0,'admin');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) NOT NULL,
  `send_interval` varchar(10) NOT NULL DEFAULT '0',
  `send_conditions` text,
  `id_type_entity` varchar(255) DEFAULT NULL,
  `recipient` varchar(100) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text,
  `active` tinyint(1) DEFAULT '1',
  `html_message` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `recipient` (`recipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periods`
--

DROP TABLE IF EXISTS `periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periods` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `from_timestamp` int(10) unsigned NOT NULL,
  `to_timestamp` int(10) unsigned NOT NULL,
  `lessons_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periods`
--

LOCK TABLES `periods` WRITE;
/*!40000 ALTER TABLE `periods` DISABLE KEYS */;
/*!40000 ALTER TABLE `periods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_comments`
--

DROP TABLE IF EXISTS `profile_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_comments` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `authors_LOGIN` varchar(100) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_comments`
--

LOCK TABLES `profile_comments` WRITE;
/*!40000 ALTER TABLE `profile_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL,
  `data` text,
  `deadline` int(10) unsigned DEFAULT NULL,
  `creator_LOGIN` varchar(100) NOT NULL,
  `lessons_ID` mediumint(8) unsigned DEFAULT NULL,
  `auto_assign` tinyint(1) NOT NULL DEFAULT '0',
  `metadata` text,
  PRIMARY KEY (`id`),
  KEY `creator_LOGIN` (`creator_LOGIN`),
  KEY `deadline` (`deadline`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `content_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lessons_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `difficulty` varchar(255) NOT NULL,
  `options` text,
  `answer` text,
  `explanation` text,
  `answers_explanation` text,
  `estimate` int(10) unsigned DEFAULT NULL,
  `settings` text,
  `linked_to` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions_to_skills`
--

DROP TABLE IF EXISTS `questions_to_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions_to_skills` (
  `questions_id` mediumint(8) unsigned NOT NULL,
  `skills_ID` mediumint(8) unsigned NOT NULL,
  `relevance` int(1) DEFAULT '1',
  KEY `questions_id` (`questions_id`,`skills_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions_to_skills`
--

LOCK TABLES `questions_to_skills` WRITE;
/*!40000 ALTER TABLE `questions_to_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `questions_to_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions_to_surveys`
--

DROP TABLE IF EXISTS `questions_to_surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions_to_surveys` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `surveys_ID` mediumint(8) unsigned DEFAULT NULL,
  `type` varchar(40) DEFAULT NULL,
  `question` mediumtext,
  `answers` mediumtext,
  `created` int(10) unsigned DEFAULT NULL,
  `info` mediumtext,
  `father_ID` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surveys_ID` (`surveys_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions_to_surveys`
--

LOCK TABLES `questions_to_surveys` WRITE;
/*!40000 ALTER TABLE `questions_to_surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `questions_to_surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rules`
--

DROP TABLE IF EXISTS `rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rules` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `content_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rule_type` varchar(255) NOT NULL,
  `rule_content_ID` mediumint(8) unsigned DEFAULT '0',
  `rule_option` float DEFAULT '0',
  `lessons_ID` mediumint(8) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rules`
--

LOCK TABLES `rules` WRITE;
/*!40000 ALTER TABLE `rules` DISABLE KEYS */;
INSERT INTO `rules` VALUES (1,'*',0,'serial',0,0,2);
/*!40000 ALTER TABLE `rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scorm_data`
--

DROP TABLE IF EXISTS `scorm_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scorm_data` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `content_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(100) DEFAULT NULL,
  `timestamp` int(10) unsigned DEFAULT NULL,
  `lesson_location` text,
  `maxtimeallowed` varchar(255) DEFAULT NULL,
  `timelimitaction` varchar(255) DEFAULT NULL,
  `masteryscore` varchar(255) DEFAULT NULL,
  `datafromlms` text,
  `entry` varchar(255) NOT NULL DEFAULT '',
  `total_time` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `comments_from_lms` text,
  `lesson_status` varchar(255) DEFAULT NULL,
  `score` varchar(255) DEFAULT NULL,
  `scorm_exit` varchar(255) DEFAULT NULL,
  `minscore` varchar(255) DEFAULT NULL,
  `maxscore` varchar(255) DEFAULT NULL,
  `suspend_data` text,
  `completion_threshold` varchar(255) DEFAULT NULL,
  `completion_status` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_LOGIN` (`users_LOGIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scorm_data`
--

LOCK TABLES `scorm_data` WRITE;
/*!40000 ALTER TABLE `scorm_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `scorm_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_invertedindex`
--

DROP TABLE IF EXISTS `search_invertedindex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_invertedindex` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB AUTO_INCREMENT=5731 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_invertedindex`
--

LOCK TABLES `search_invertedindex` WRITE;
/*!40000 ALTER TABLE `search_invertedindex` DISABLE KEYS */;
INSERT INTO `search_invertedindex` VALUES (662,'\r\n						\r\n					\r\n					\r\n						\r\n						\r\n					\r\n				\r\n			\r\n			\r\n			'),(719,'\r\n						'),(649,'\r\n						between'),(718,'\r\n						c'),(654,'\r\n						cost'),(721,'\r\n						infinite'),(658,'\r\n						nearer'),(661,'\r\n						the'),(720,'\r\n					\r\n					\r\n						'),(722,'\r\n					\r\n				\r\n			\r\n			\r\n			we'),(104,'\r\n			\r\n			\r\n				\r\n					\r\n						\r\n						\r\n						\r\n						\r\n						\r\n					\r\n				\r\n			\r\n			\r\n		\r\n	\r\n\r\n'),(242,'\r\n			\r\n			\r\n				\r\n					\r\n						\r\n						\r\n						\r\n						\r\n					\r\n				\r\n			\r\n			\r\n		\r\n	\r\n\r\n'),(376,'\r\n			\r\n			\r\n				\r\n					\r\n						\r\n						\r\n						\r\n					\r\n				\r\n			\r\n			\r\n		\r\n	\r\n\r\n'),(644,'\r\n			\r\n			\r\n				\r\n					\r\n						here'),(374,'\r\n			\r\n			\r\n			\r\n			\r\n			solution'),(736,'\r\n			\r\n			\r\n			\r\n			\r\n		\r\n		\r\n			'),(315,'\r\n			\r\n			\r\n			\r\n			a'),(506,'\r\n			\r\n			\r\n			\r\n			every'),(79,'\r\n			\r\n			\r\n			\r\n			if'),(4866,'\r\n			\r\n			\r\n			a'),(470,'\r\n			\r\n			\r\n			dynamic'),(3188,'\r\n			\r\n			\r\n			in'),(403,'\r\n			\r\n			\r\n			methodology'),(2273,'\r\n			\r\n			\r\n			some'),(294,'\r\n			\r\n			\r\n			the'),(703,'\r\n			\r\n			\r\n		\r\n	\r\n\r\n\r\n	\r\n		\r\n			'),(558,'\r\n			\r\n			\r\n		\r\n	\r\n\r\n'),(375,'\r\n			\r\n			'),(520,'\r\n			\r\n			1'),(330,'\r\n			\r\n			a'),(2597,'\r\n			\r\n			after'),(2835,'\r\n			\r\n			although'),(255,'\r\n			\r\n			an'),(724,'\r\n			\r\n			as'),(4973,'\r\n			\r\n			ask'),(2889,'\r\n			\r\n			at'),(3589,'\r\n			\r\n			beyond'),(2777,'\r\n			\r\n			but'),(2541,'\r\n			\r\n			contention'),(2201,'\r\n			\r\n			debugging'),(3714,'\r\n			\r\n			difficult'),(3703,'\r\n			\r\n			don'),(4692,'\r\n			\r\n			engineers'),(3994,'\r\n			\r\n			evaluate'),(4194,'\r\n			\r\n			everything'),(3974,'\r\n			\r\n			finally'),(368,'\r\n			\r\n			for'),(3064,'\r\n			\r\n			good'),(5080,'\r\n			\r\n			guy'),(4065,'\r\n			\r\n			having'),(3315,'\r\n			\r\n			however'),(4403,'\r\n			\r\n			humans'),(3032,'\r\n			\r\n			i'),(2173,'\r\n			\r\n			idealists'),(4310,'\r\n			\r\n			ideally'),(164,'\r\n			\r\n			if'),(4625,'\r\n			\r\n			improving'),(493,'\r\n			\r\n			in'),(2972,'\r\n			\r\n			it'),(4906,'\r\n			\r\n			just'),(4545,'\r\n			\r\n			knowing'),(143,'\r\n			\r\n			let'),(4297,'\r\n			\r\n			make'),(4629,'\r\n			\r\n			memory'),(3621,'\r\n			\r\n			most'),(3829,'\r\n			\r\n			non-engineers'),(3849,'\r\n			\r\n			non-programmers'),(4529,'\r\n			\r\n			note'),(4560,'\r\n			\r\n			obviously'),(2607,'\r\n			\r\n			often'),(410,'\r\n			\r\n			on'),(2358,'\r\n			\r\n			once'),(3720,'\r\n			\r\n			one'),(3157,'\r\n			\r\n			pad'),(3998,'\r\n			\r\n			plan'),(4482,'\r\n			\r\n			portability'),(4975,'\r\n			\r\n			praise'),(3688,'\r\n			\r\n			programmers'),(3010,'\r\n			\r\n			programming'),(3091,'\r\n			\r\n			really'),(2719,'\r\n			\r\n			representations'),(5101,'\r\n			\r\n			schedule'),(3639,'\r\n			\r\n			since'),(5198,'\r\n			\r\n			some'),(2412,'\r\n			\r\n			sometimes'),(2748,'\r\n			\r\n			space'),(4367,'\r\n			\r\n			sql'),(668,'\r\n			\r\n			suppose'),(225,'\r\n			\r\n			the'),(2958,'\r\n			\r\n			then'),(2513,'\r\n			\r\n			there'),(114,'\r\n			\r\n			this'),(32,'\r\n			\r\n			to'),(2857,'\r\n			\r\n			try'),(4328,'\r\n			\r\n			uml'),(4243,'\r\n			\r\n			understanding'),(4164,'\r\n			\r\n			usually'),(50,'\r\n			\r\n			we'),(2634,'\r\n			\r\n			what'),(425,'\r\n			\r\n			when'),(4178,'\r\n			\r\n			whether'),(3116,'\r\n			\r\n			while'),(3864,'\r\n			\r\n			with'),(3402,'\r\n			\r\n			writing'),(4351,'\r\n			\r\n			xml'),(3045,'\r\n			\r\n			you'),(4994,'\r\n			\r\n			your'),(4016,'\r\n			\r\n			•'),(207,'\r\n			\r\n		\r\n	\r\n\r\n'),(708,'\r\n			'),(232,'\r\n			2'),(237,'\r\n			3'),(3732,'\r\n			<'),(2755,'\r\n			a'),(3002,'\r\n			actually'),(3429,'\r\n			admittedly'),(3265,'\r\n			after'),(3074,'\r\n			and'),(3083,'\r\n			as'),(3897,'\r\n			because'),(4603,'\r\n			best-case'),(3483,'\r\n			but'),(2430,'\r\n			called'),(3283,'\r\n			consider'),(2980,'\r\n			don'),(95,'\r\n			during'),(419,'\r\n			e'),(5210,'\r\n			either'),(2902,'\r\n			every'),(3717,'\r\n			everyone'),(111,'\r\n			example:'),(5016,'\r\n			fire'),(3039,'\r\n			first'),(2771,'\r\n			garbage'),(4807,'\r\n			hand'),(3818,'\r\n			here'),(4152,'\r\n			hopefully'),(3036,'\r\n			however'),(3323,'\r\n			i'),(3667,'\r\n			if'),(2622,'\r\n			in'),(3918,'\r\n			is'),(4011,'\r\n			it'),(2620,'\r\n			leaving'),(4389,'\r\n			legend'),(404,'\r\n			let'),(2519,'\r\n			logging'),(4124,'\r\n			make'),(4013,'\r\n			management'),(5150,'\r\n			most'),(4222,'\r\n			never'),(4679,'\r\n			new'),(4134,'\r\n			ninjaprogrammer'),(5157,'\r\n			often'),(2377,'\r\n			or'),(2188,'\r\n			organizations'),(4527,'\r\n			output'),(421,'\r\n			p'),(2976,'\r\n			people'),(3840,'\r\n			precise'),(3121,'\r\n			prepare'),(3192,'\r\n			preparedness'),(3853,'\r\n			programmers'),(4837,'\r\n			pruning'),(2714,'\r\n			representation'),(4962,'\r\n			simply'),(4203,'\r\n			since'),(4486,'\r\n			software'),(3403,'\r\n			study'),(2266,'\r\n			such'),(2722,'\r\n			techniques'),(2199,'\r\n			that'),(2515,'\r\n			the'),(2503,'\r\n			there'),(731,'\r\n			they'),(620,'\r\n			this'),(3923,'\r\n			though'),(2826,'\r\n			to'),(2725,'\r\n			transmitting'),(2844,'\r\n			try'),(4872,'\r\n			user'),(2899,'\r\n			we'),(3413,'\r\n			what'),(2332,'\r\n			when'),(100,'\r\n			which'),(4926,'\r\n			working'),(2834,'\r\n			you'),(2248,'\r\n			•'),(208,'\r\n	\r\n		\r\n			\r\n			adjoining'),(244,'\r\n	\r\n		\r\n			\r\n			backtracking\r\n			the'),(5145,'\r\n	\r\n		\r\n			\r\n			choosing'),(4317,'\r\n	\r\n		\r\n			\r\n			communication'),(378,'\r\n	\r\n		\r\n			\r\n			dijkstra'),(461,'\r\n	\r\n		\r\n			\r\n			dynamic'),(5,'\r\n	\r\n		\r\n			\r\n			floyd'),(619,'\r\n	\r\n		\r\n			\r\n			greed'),(440,'\r\n	\r\n		\r\n			\r\n			heuristic'),(2294,'\r\n	\r\n		\r\n			\r\n			how'),(580,'\r\n	\r\n		\r\n			\r\n			introduction\r\n			in'),(560,'\r\n	\r\n		\r\n			\r\n			kruskal'),(2162,'\r\n	\r\n		\r\n			\r\n			learn'),(433,'\r\n	\r\n		\r\n			\r\n			minimoum'),(704,'\r\n	\r\n		\r\n			\r\n			minimum'),(665,'\r\n	\r\n		\r\n			\r\n			order'),(528,'\r\n	\r\n		\r\n			\r\n			prim'),(3555,'\r\n	\r\n		\r\n			\r\n			take'),(110,'\r\n	\r\n		\r\n			\r\n			traveling'),(3052,'\r\n	\r\n		\r\n			\r\n			why'),(1836,'\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nthe'),(927,'\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\n'),(2022,'\r\n\r\n\r\n\r\n\r\n\r\n\r\n\r\nthe'),(1960,'\r\n\r\n\r\n\r\n\r\n\r\n\r\na'),(2155,'\r\n\r\n\r\n\r\n\r\n\r\n\r\nbonampak'),(1711,'\r\n\r\n\r\n\r\n\r\n\r\n'),(1775,'\r\n\r\n\r\n\r\n\r\n\r\nearly'),(2061,'\r\n\r\n\r\n\r\n\r\n\r\ngeographical'),(1955,'\r\n\r\n\r\n\r\n\r\n'),(1337,'\r\n\r\n\r\n\r\nalthough'),(1166,'\r\n\r\n\r\n\r\nat'),(1522,'\r\n\r\n\r\n\r\nbuilding'),(1447,'\r\n\r\n\r\n\r\nclassic'),(1854,'\r\n\r\n\r\n\r\neach'),(1265,'\r\n\r\n\r\n\r\nin'),(1375,'\r\n\r\n\r\n\r\nit'),(1888,'\r\n\r\n\r\n\r\nmayanists'),(1293,'\r\n\r\n\r\n\r\nmost'),(1596,'\r\n\r\n\r\n\r\nnotable'),(1323,'\r\n\r\n\r\n\r\nscribes'),(1134,'\r\n\r\n\r\n\r\nsince'),(1918,'\r\n\r\n\r\n\r\nspanish'),(1092,'\r\n\r\n\r\n\r\nthe'),(1365,'\r\n\r\n\r\n\r\nthere'),(1799,'\r\n\r\n\r\n\r\nunlike'),(1409,'\r\n\r\n\r\n\r\nurban'),(1309,'\r\n\r\n\r\n\r\nwriting'),(1006,'\r\n\r\n'),(1033,'\r\n\r\nalso'),(5312,'\r\n\r\nanother'),(2075,'\r\n\r\narchaeological'),(2035,'\r\n\r\nas'),(1957,'\r\n\r\neach'),(905,'\r\n\r\neven'),(5307,'\r\n\r\nfirst'),(5371,'\r\n\r\nhe'),(5319,'\r\n\r\nin'),(878,'\r\n\r\nit'),(831,'\r\n\r\nmaya'),(1958,'\r\n\r\nmayanists'),(794,'\r\n\r\nmuch'),(901,'\r\n\r\nphilosophically'),(5388,'\r\n\r\nskills'),(5379,'\r\n\r\nsome'),(1959,'\r\n\r\nspanish'),(769,'\r\n\r\nthe'),(2092,'\r\n\r\nthere'),(5399,'\r\n\r\nyou'),(5291,'\r\nafter'),(1354,'\r\narchitecture\r\n\r\n\r\n\r\nas'),(1956,'\r\nart\r\n\r\na'),(929,'\r\nastronomy\r\n\r\nuniquely'),(5294,'\r\ncreate'),(5333,'\r\nefront'),(1713,'\r\npostclassic'),(5289,'\r\nresponsible'),(5304,'\r\nuse'),(1057,'\r\nwriting\r\n\r\n\r\n\r\nthe'),(5300,'\r\n \r\nin'),(168,'!=20!'),(5700,'------------------'),(5023,'10\r\n			bad'),(2077,'1000'),(1715,'10th'),(1751,'1250'),(1734,'1450'),(1835,'1697'),(1051,'16th'),(5279,'16_exported'),(1833,'17th'),(5280,'17_exported'),(2068,'1800'),(5281,'18_exported'),(1251,'1950s'),(1252,'1970s'),(1292,'1987'),(906,'19th'),(239,'2\r\n			\r\n			in'),(4325,'2003'),(2154,'2005'),(1102,'2006'),(1096,'200–300'),(5278,'2013'),(5411,'2014'),(1248,'20th'),(167,'21-1'),(1967,'250–900'),(887,'260-day'),(925,'260-day-calendar'),(1055,'3257'),(889,'365-day'),(3541,'39;\r\n			\r\n			use'),(4588,'39;\r\n			\r\n		\r\n	\r\n\r\n'),(4671,'39;\r\n			<'),(5134,'39;\r\n			hard'),(5181,'39;\r\n			seems'),(3453,'39;!\r\n			\r\n		\r\n	\r\n\r\n'),(3763,'39;!\r\n			<'),(727,'39;=v'),(2146,'39;eqchi'),(915,'39;iche'),(923,'39;ij'),(1965,'39;inich\r\n\r\n\r\n\r\n\r\n'),(2654,'39;ll'),(2884,'39;m'),(926,'39;olk'),(1287,'39;pilgrim'),(2741,'39;re'),(3599,'39;s\r\n			any'),(2062,'39;s\r\n\r\n\r\n\r\n\r\n'),(144,'39;s'),(3860,'39;t\r\n			have'),(3455,'39;t\r\n			think'),(222,'39;t'),(2383,'39;ve'),(1745,'39;woj'),(4593,'39;``big'),(372,'4!=24'),(2830,'50-foot-invisible-scorpion-from-outer-space'),(3592,'60\r\n			hours'),(226,':\r\n			\r\n			1'),(4993,':\r\n			\r\n			what'),(711,':\r\n			'),(5374,':\r\n\r\nhow'),(420,':=cost'),(422,':=the'),(714,'=0;\r\n			'),(430,'=infinite'),(99,'=min'),(688,'=sum'),(3803,'?\r\n			\r\n			•'),(2213,'a\r\n			common'),(4063,'a\r\n			different'),(4602,'a\r\n			function'),(3485,'a\r\n			good'),(3927,'a\r\n			great'),(3169,'a\r\n			large'),(3561,'a\r\n			larger'),(2278,'a\r\n			little'),(3608,'a\r\n			manager'),(4192,'a\r\n			meeting'),(4898,'a\r\n			mockup'),(3802,'a\r\n			new'),(3528,'a\r\n			part'),(2501,'a\r\n			profiling'),(4936,'a\r\n			project'),(4892,'a\r\n			reason'),(4623,'a\r\n			small'),(2641,'a\r\n			system'),(4916,'a\r\n			very'),(4118,'a\r\n			way'),(4860,'a\r\n			well-designed'),(5589,'a> aportan'),(5556,'a> y'),(5475,'a> y herbart<'),(976,'abaj'),(1142,'abandoned'),(2691,'abbreviated'),(5047,'abilities'),(4250,'ability\r\n			to'),(2192,'ability'),(2403,'able\r\n			to'),(1256,'able'),(483,'abort'),(699,'aborts'),(3507,'about\r\n			the'),(5068,'about\r\n			this'),(90,'about'),(3414,'above'),(5722,'abril'),(4600,'absence'),(5495,'absoluta'),(2432,'absolute'),(3389,'absolutely'),(2702,'abstract'),(4429,'abstraction\r\n			abstraction'),(3481,'abstraction'),(4666,'abstraction<'),(4141,'abstractions'),(4461,'abstractly\r\n			and'),(1539,'abundant'),(3664,'abuse'),(5480,'académicos'),(1253,'accelerated'),(1607,'accented'),(532,'accept'),(604,'acceptable'),(4463,'accepted'),(1890,'accepting'),(712,'access'),(2539,'accesses'),(4092,'accessible'),(4467,'accessors'),(5571,'acción'),(1644,'accompanied'),(1992,'accompanying'),(3350,'accomplish'),(3309,'accomplishes\r\n			far'),(3020,'accomplishment'),(2005,'accomplishments'),(1506,'accordance'),(184,'according'),(1629,'account'),(2584,'accounts'),(1052,'accumulated'),(1054,'accumulates'),(448,'accurate'),(3071,'accurately'),(2145,'achí'),(2967,'acquire'),(1391,'acropolis'),(1760,'across'),(2770,'action'),(601,'actions'),(5293,'actions:\r\n\r\nadd'),(5341,'actions:<'),(5314,'activate'),(3054,'active'),(5655,'actividad'),(1904,'activities'),(3563,'activity'),(5670,'actores'),(5233,'acts'),(4744,'actual'),(453,'actually'),(1484,'added'),(2251,'adding'),(5084,'addition\r\n			to'),(1553,'addition'),(2473,'additional'),(1331,'additionally'),(2483,'address'),(3908,'addresses'),(4773,'addressing'),(686,'adds'),(5600,'además'),(2970,'adequately'),(41,'adjoining'),(5419,'administración'),(1935,'administrative'),(5290,'administrator'),(5326,'administrators'),(849,'admirable'),(3995,'admire'),(2886,'admit\r\n			i'),(3078,'admit'),(1581,'adobe'),(2139,'adoption'),(4817,'adult'),(4841,'advanced\r\n			programmer'),(1527,'advanced'),(2118,'advances'),(4928,'advantage\r\n			of'),(3154,'advantage'),(3505,'advantage:'),(4774,'advantages'),(2442,'advantages:\r\n			\r\n			•'),(5003,'adventure'),(3038,'advice'),(1914,'aesthetic'),(3479,'affect'),(3684,'affected'),(4470,'affecting'),(2926,'affectionately'),(4566,'affects\r\n			the'),(4128,'affects'),(834,'affinities'),(2745,'afford'),(2327,'after\r\n			'),(2368,'after\r\n			this'),(660,'after'),(1308,'afterlife'),(4366,'again\r\n			only'),(571,'again'),(4864,'against\r\n			future'),(5246,'against\r\n			the'),(1801,'against'),(812,'aged'),(3198,'agree\r\n			on'),(3705,'agree'),(4263,'agreement'),(1976,'agriculturally'),(1595,'airy'),(1847,'ajaw'),(1842,'ajawil'),(1843,'ajawlel'),(1844,'ajawlil'),(529,'algorithm\r\n			at'),(6,'algorithm\r\n			in'),(561,'algorithm\r\n			the'),(379,'algorithm\r\n			this'),(4,'algorithm'),(360,'algorithm:'),(3792,'algorithm?'),(441,'algorithms\r\n			the'),(2,'algorithms'),(5404,'align'),(1639,'aligned'),(1690,'aligning'),(4851,'alive'),(3336,'all\r\n			your'),(4225,'alleged'),(2805,'allocate\r\n			a'),(4637,'allocate\r\n			and'),(2672,'allocate'),(2806,'allocated\r\n			and'),(2754,'allocated'),(5393,'allocates'),(2818,'allocating'),(2621,'allocation'),(4469,'allow\r\n			the'),(4932,'allow\r\n			them'),(2452,'allow'),(2906,'allowed'),(3201,'allowing'),(4832,'allows'),(1487,'almost'),(4933,'alone'),(1999,'along'),(76,'already'),(3042,'also\r\n			helps'),(147,'also'),(1610,'altars'),(3884,'alter'),(4182,'alternate'),(4102,'alternative'),(1082,'although'),(974,'alto'),(1984,'altun'),(5721,'alumnos'),(537,'always'),(1192,'amatl'),(5490,'ambas'),(3301,'ambiguous'),(2026,'american'),(1133,'americas'),(133,'among'),(1145,'amongst'),(4184,'amount\r\n			carefully'),(2437,'amount'),(4655,'amount<'),(5142,'amplifies'),(4911,'an\r\n			athletic'),(4026,'an\r\n			entire'),(2388,'an\r\n			error'),(3166,'an\r\n			estimate'),(1003,'analysis'),(2579,'analyze'),(286,'ancestor'),(785,'ancient'),(3901,'and\r\n			a'),(5057,'and\r\n			boss'),(2971,'and\r\n			can'),(2268,'and\r\n			changing'),(2737,'and\r\n			computing'),(3698,'and\r\n			cordial'),(2610,'and\r\n			dividing'),(5079,'and\r\n			drink'),(2414,'and\r\n			fix'),(4478,'and\r\n			hence'),(2394,'and\r\n			how'),(4974,'and\r\n			make'),(4363,'and\r\n			parsing'),(3942,'and\r\n			quickly'),(3463,'and\r\n			that'),(2421,'and\r\n			then'),(2914,'and\r\n			we'),(3935,'and\r\n			weaknesses'),(3678,'and\r\n			whose'),(2253,'and\r\n			•'),(5321,'and\r\nhow'),(5358,'and<'),(3854,'anecdotally'),(5442,'angel'),(3287,'angles'),(1314,'animal'),(1534,'animals'),(556,'animated'),(2606,'another\r\n			two-fold'),(2107,'another\r\n'),(17,'another'),(4509,'answer\r\n			when'),(3311,'answer'),(4510,'answers'),(2222,'anticipate'),(2555,'anticipated'),(4237,'any\r\n			documents'),(4706,'anybody'),(3585,'anymore'),(3424,'anyone'),(4433,'anything\r\n			except'),(2587,'anything'),(3428,'anyway'),(2896,'apache™'),(4856,'apart'),(5438,'aplicar'),(2111,'apogee'),(4931,'apologize'),(150,'apparently'),(4753,'appeal'),(932,'appear'),(682,'appearance'),(2103,'appeared'),(999,'appears'),(557,'applet:\r\n			\r\n			\r\n				\r\n					\r\n						\r\n						\r\n						\r\n					\r\n				\r\n			\r\n			\r\n			'),(400,'appliance'),(2470,'applicable'),(3274,'application'),(5249,'applications\r\n			programmers'),(621,'applied'),(4563,'apply\r\n			here'),(583,'apply'),(500,'applying'),(5102,'appreciate\r\n			what'),(3501,'appreciate'),(5052,'appreciated'),(3985,'appreciation\r\n			for'),(3159,'approach\r\n			doesn'),(1898,'approach'),(4968,'appropriate\r\n			sides'),(766,'appropriate'),(3686,'appropriately'),(890,'approximated'),(447,'approximately'),(5478,'apreciar'),(5488,'aquí'),(1589,'arch'),(1311,'archaeological'),(2013,'archaeologists'),(1224,'archaeology'),(2436,'architects\r\n			must'),(1394,'architectural'),(1353,'architecture'),(408,'arcs'),(2657,'are\r\n			bottlenecks'),(2565,'are\r\n			first'),(4221,'are\r\n			great'),(3970,'are\r\n			many'),(2178,'are\r\n			not'),(3883,'are\r\n			saying'),(2477,'are\r\n			sprinkled'),(4425,'are\r\n			the'),(3795,'are:\r\n			\r\n			•'),(519,'are:'),(872,'area'),(2046,'areas'),(5659,'áreas'),(2648,'argue'),(3813,'arguing'),(4163,'argument'),(3281,'arguments'),(2207,'arise\r\n			places'),(2524,'arise'),(2221,'arises\r\n			that'),(3689,'arises'),(5041,'armed'),(775,'arms'),(2478,'around\r\n			risky'),(1089,'around'),(209,'array\r\n			the'),(34,'array'),(1136,'arrival'),(1294,'arrived'),(3472,'arrogance'),(4346,'arrows'),(4453,'article'),(1972,'artistic'),(866,'artwork'),(4750,'as\r\n			early'),(4950,'as\r\n			personal'),(4972,'as\r\n			quickly'),(3300,'as\r\n			they'),(3412,'as\r\n			you'),(2246,'as:\r\n			\r\n			•'),(3267,'ascertain'),(2885,'ashamed'),(309,'asked'),(3308,'asking'),(1525,'aspect'),(835,'aspects'),(2562,'assert'),(2267,'assertion'),(2238,'assertions'),(3303,'assign\r\n			meaning'),(416,'assign'),(2480,'assigned'),(4424,'assigning'),(4257,'assignment'),(4423,'assist'),(3624,'associated\r\n			with'),(751,'associated'),(148,'assume'),(710,'assuming'),(2219,'assumption'),(3098,'assumptions'),(2593,'assurance'),(3940,'astonishing'),(1685,'astronomers'),(995,'astronomical'),(928,'astronomy'),(4565,'at\r\n			all'),(2311,'at\r\n			the'),(4382,'at\r\n			which'),(2862,'at\r\n			your'),(2411,'atomic'),(1651,'atop'),(5505,'atribuido'),(541,'attached'),(2605,'attack'),(365,'attempt'),(3255,'attempting'),(1351,'attempts'),(3637,'attend\r\n			only'),(1502,'attention'),(1825,'attracted'),(4434,'attraction'),(5222,'attractive'),(3828,'audience'),(5247,'audience?'),(5672,'aula'),(1221,'authenticity'),(4337,'author'),(3257,'authority'),(5311,'auto-completion'),(4358,'automation'),(3296,'available\r\n			divination'),(1544,'available'),(3163,'average\r\n			weighted'),(3415,'average'),(2801,'avoid'),(2700,'avoiding'),(179,'aware'),(4148,'away\r\n			with'),(2664,'away'),(1470,'axis'),(5548,'ayudar'),(5705,'ayudaré'),(740,'aztec'),(4829,'baby\r\n			bridge'),(88,'back'),(285,'backtrack'),(243,'backtracking'),(4108,'bad?\r\n			\r\n			a'),(3699,'baits'),(5681,'bajo'),(1401,'baktún\r\n\r\n\r\n\r\nthrough'),(3307,'balance'),(876,'balancing'),(808,'ball'),(1498,'ball-courts'),(1701,'ball-game'),(1617,'ballgame'),(2536,'bandwidth'),(2088,'bartolo'),(130,'base'),(5225,'based\r\n			on'),(246,'based'),(2441,'basic'),(5427,'básicos'),(2969,'basics'),(2085,'basins'),(1039,'basis'),(4275,'bduf'),(4543,'be\r\n			a'),(4242,'be\r\n			accurately'),(4580,'be\r\n			afraid'),(4576,'be\r\n			an'),(4121,'be\r\n			asked'),(5000,'be\r\n			good'),(2855,'be\r\n			improperly'),(5116,'be\r\n			made'),(5056,'be\r\n			noticed'),(2241,'be\r\n			revealed'),(3904,'be\r\n			seen'),(4044,'be\r\n			spent'),(5078,'be\r\n			successful'),(3826,'bearing\r\n			in'),(2329,'bearing'),(4823,'bears'),(4551,'beautiful'),(4332,'beauty'),(2999,'became'),(37,'because'),(3991,'become\r\n			a'),(2764,'become\r\n			garbage'),(4447,'become\r\n			longer'),(240,'become'),(5211,'becomes\r\n			your'),(4445,'becomes'),(5185,'becoming\r\n			familiar'),(3886,'becoming'),(3458,'been\r\n			asked'),(4450,'been\r\n			spent'),(77,'been'),(3644,'before\r\n			they'),(2647,'before\r\n			you'),(250,'before'),(1571,'began'),(3393,'begin\r\n			looking'),(4286,'begin'),(2345,'beginner'),(2433,'beginners\r\n			must'),(2274,'beginners'),(4430,'beginning\r\n			programmers'),(127,'beginning'),(2296,'begins'),(2887,'begun'),(5061,'behave'),(3681,'behavior'),(2259,'behind'),(4587,'being\r\n			able'),(4631,'being\r\n			used'),(938,'being'),(858,'belief'),(2134,'beliefs'),(3365,'believe\r\n			they'),(1114,'believe'),(745,'believed'),(2029,'belize'),(4741,'belligerently'),(326,'belong'),(45,'belonging'),(234,'belongs'),(314,'below'),(5184,'belt'),(3340,'benefit\r\n			of'),(2633,'benefit'),(3319,'benefits'),(228,'beside'),(3641,'best\r\n			for'),(183,'best'),(1772,'best-known'),(4415,'better\r\n			than'),(5066,'better\r\n			to'),(5001,'better\r\n			yet'),(607,'better'),(2451,'between\r\n			statements'),(4596,'between\r\n			``constant-time'),(12,'between'),(5172,'beyond\r\n			your'),(1341,'beyond'),(476,'bigger'),(2723,'binary'),(1210,'bishop'),(4609,'bit\r\n			goes'),(3037,'bits'),(1318,'black'),(971,'blanca'),(2172,'blind'),(2465,'blindness'),(1107,'block'),(2673,'blocks'),(1509,'bodies'),(863,'bodily'),(3778,'body'),(1985,'bonampak'),(4498,'bonking'),(1202,'book'),(4404,'book-reading'),(1279,'books'),(52,'boolean'),(4191,'bored'),(4558,'boring'),(3150,'born'),(2646,'boss'),(5153,'bosses'),(817,'both'),(5019,'bothered'),(3090,'bothers'),(2604,'bottleneck'),(2581,'bottlenecks'),(4009,'bought'),(1194,'bound'),(2094,'boundaries'),(3944,'boundary'),(4345,'boxes'),(3293,'brain'),(4175,'brains'),(910,'branch'),(5400,'branches'),(3262,'brand'),(472,'break'),(3553,'breaks'),(1250,'breakthroughs'),(2460,'brevity'),(1349,'bricks'),(4801,'bridge'),(4831,'bridges'),(4674,'brief'),(4027,'bright'),(3957,'brimstone'),(3895,'bring'),(2594,'brings'),(4914,'broken'),(1313,'brushes'),(5677,'buen'),(2677,'buffer'),(4079,'bugginess'),(2828,'bugs\r\n			the'),(2413,'bugs'),(3751,'bugs<'),(2500,'build'),(2695,'building\r\n			a'),(2512,'building'),(1483,'buildings'),(5143,'builds'),(1422,'built'),(3704,'bully'),(1329,'bundles'),(2595,'burden\r\n			with'),(3206,'burden'),(2078,'burial'),(1663,'burials'),(2530,'burn\r\n			up'),(1206,'burnt'),(5706,'buscame'),(4695,'business\r\n			effectively'),(4032,'business\r\n			plan;'),(3279,'business'),(2668,'but\r\n			also'),(3114,'but\r\n			an'),(2747,'but\r\n			eventually'),(5050,'but\r\n			i'),(3473,'but\r\n			will'),(3535,'button'),(4022,'buying'),(4676,'buyouts'),(2859,'by\r\n			building'),(59,'c:\r\n			\r\n			a'),(2017,'cacao'),(2783,'cache'),(2710,'caching\r\n			brings'),(2699,'caching\r\n			is'),(2697,'caching'),(5518,'cada'),(3665,'caffeine'),(1982,'calakmul'),(3193,'calamity'),(465,'calculate'),(174,'calculations'),(603,'calculations!\r\n			\r\n			all'),(1041,'calendar'),(757,'calendars'),(405,'call'),(47,'called'),(4480,'caller'),(4471,'calling'),(2492,'calls'),(742,'came'),(1787,'campaign'),(1800,'campaigns'),(2034,'campeche'),(5469,'campo'),(5486,'campos'),(3418,'can\r\n			actually'),(2800,'can\r\n			be'),(3252,'can\r\n			easily'),(3890,'can\r\n			often'),(4133,'can\r\n			plan'),(4621,'can\r\n			sometimes'),(3162,'can;'),(984,'cancer'),(1993,'cancuen'),(1370,'candelaria'),(373,'candidate'),(484,'candidates'),(5207,'cannot\r\n			be'),(4216,'cannot\r\n			get'),(2170,'cannot'),(5390,'capability'),(4335,'capable'),(3938,'capacity'),(1708,'capital'),(2453,'captured'),(5618,'carácter'),(5667,'características'),(5569,'características;'),(1472,'cardinal'),(203,'care'),(3997,'career'),(1501,'careful'),(3498,'carefully\r\n			separate'),(2577,'carefully'),(2052,'caribbean'),(4698,'carry'),(5011,'carrying'),(1608,'carved'),(1106,'cascajal'),(194,'case'),(5188,'cases\r\n			stronger'),(592,'cases'),(4633,'catastrophic'),(3543,'catches'),(5492,'categoría'),(5285,'categories'),(2245,'categorized'),(2140,'catholicism'),(1951,'cause'),(836,'caused'),(1453,'causeways'),(4146,'causing'),(1366,'cave'),(1372,'cave-origin'),(5051,'caveat:'),(807,'caves'),(2904,'cease'),(752,'celestial'),(1560,'cement'),(2015,'cenote'),(1476,'cenotes'),(656,'center'),(1139,'centers'),(861,'central'),(1895,'centrality'),(173,'centuries'),(907,'century'),(1177,'ceramic'),(1598,'ceremonial'),(749,'ceremonies'),(263,'certain'),(4490,'certainly\r\n			a'),(3111,'certainly'),(5384,'certifications\r\n'),(5378,'certifications'),(5410,'cesba'),(737,'chaac'),(4169,'challenge'),(4945,'challenged'),(3721,'challenges'),(5034,'challenging'),(1625,'chambers'),(912,'chan'),(485,'chance'),(550,'change'),(2408,'changed'),(2596,'changes'),(2402,'changing'),(3894,'chaos'),(501,'chapter'),(5407,'chapters'),(3933,'character'),(3953,'characteristic'),(80,'characteristics'),(1717,'characterized'),(841,'characters'),(5398,'chart'),(1028,'charts'),(2798,'cheap'),(101,'cheaper'),(201,'cheapest'),(691,'check'),(3517,'checked'),(625,'checking'),(3670,'cherish'),(302,'chessboard'),(779,'chest'),(1374,'chiapas'),(1725,'chichen'),(241,'child'),(287,'children'),(1231,'chips'),(5175,'choice\r\n			of'),(504,'choice'),(489,'choices'),(235,'choose'),(187,'chooses'),(3441,'choosing'),(199,'chosen'),(911,'christianity'),(1990,'chunchucmil'),(2756,'chunk'),(1862,'ch’e’n'),(5436,'ciencia'),(5452,'ciencias'),(5595,'científica'),(5619,'científico'),(572,'circle'),(3268,'circumstance'),(2845,'circumstances'),(122,'cities'),(125,'city'),(146,'city-base'),(1978,'city-centered'),(1743,'city-states'),(2087,'cival'),(5413,'civilización'),(2108,'civilization\r\n\r\nthe'),(733,'civilization'),(1012,'civilizations'),(3200,'clarifies'),(4502,'clarify\r\n			exactly'),(5087,'clarify\r\n			in'),(2420,'clarify'),(3891,'clarity'),(5709,'clases'),(2872,'class'),(4405,'class-taking'),(1343,'classes'),(1466,'classic'),(869,'classical'),(1065,'classified'),(1335,'clay'),(5194,'clean'),(2683,'clear'),(5090,'clear-headedness'),(1987,'clearly'),(5086,'clients'),(2042,'climate'),(450,'close'),(750,'closely'),(2736,'closer'),(1654,'closest'),(49,'closure'),(4948,'clothing'),(958,'clue'),(4910,'coach'),(2071,'coast'),(1729,'coba'),(5718,'cobrará'),(3661,'cocaine'),(4436,'code\r\n			brevity'),(2511,'code\r\n			is'),(3454,'code\r\n			it'),(3478,'code\r\n			should'),(3497,'code\r\n			with'),(2185,'code'),(3969,'code---and'),(3426,'code-level'),(3447,'code;\r\n			•'),(3764,'code<'),(789,'codecs'),(3527,'coded'),(992,'codex'),(1317,'codex-style'),(1205,'codices'),(1233,'codices;'),(3152,'coding'),(3399,'cold'),(1750,'collapse'),(2189,'colleagues'),(5395,'collect'),(3029,'collecting'),(2772,'collection'),(1925,'collections'),(1805,'collective'),(2766,'collector'),(4590,'college'),(1739,'colonial'),(2126,'colonization'),(5223,'color\r\n			for'),(3977,'color'),(231,'column'),(1671,'comb'),(3663,'combat'),(135,'combination'),(594,'combinations'),(384,'combined'),(1680,'combs'),(87,'come'),(5725,'comentar'),(944,'comes'),(3645,'comfortable'),(3992,'comfortably'),(3406,'command'),(1400,'commemorate'),(2419,'comment'),(1930,'commerce'),(3058,'commercial\r\n			project'),(3215,'commit'),(4211,'commitment'),(4388,'commitment;'),(3499,'committed'),(3520,'committing'),(1010,'common'),(1599,'commonly'),(3185,'communicate'),(4339,'communicated'),(3128,'communicating\r\n			with'),(2689,'communicating'),(4189,'communication\r\n			in'),(3320,'communication'),(2073,'communities'),(1078,'community'),(3643,'commute\r\n			from'),(5464,'como'),(5693,'compaginado_400'),(5694,'compaginado_800'),(4199,'companies\r\n			do'),(4400,'companies\r\n			would'),(2187,'companies'),(2206,'company'),(3175,'company-wide'),(4107,'company?\r\n			\r\n			10'),(4105,'company?\r\n			\r\n			9'),(2688,'compared'),(3958,'comparing'),(608,'comparison'),(4030,'compelling'),(1742,'competing'),(4485,'compiler'),(2681,'compilers\r\n			and'),(5540,'complejo'),(1131,'complete'),(4315,'completed'),(4247,'completely\r\n			that'),(1075,'completely'),(4058,'completely?\r\n			\r\n			it'),(5299,'completes'),(5292,'completing'),(4296,'completion\r\n			of'),(4283,'completion'),(1132,'complex'),(3537,'complex:'),(1399,'complexes'),(3798,'complexity\r\n			and'),(2175,'complexity'),(4624,'complicate'),(555,'complicated'),(2554,'component'),(4055,'component?\r\n			\r\n			•'),(5186,'components\r\n			in'),(4048,'components'),(4907,'composer'),(5549,'comprender'),(5583,'comprensión'),(2459,'compromise'),(2518,'computation'),(4592,'computational'),(5218,'compute'),(3777,'computer\r\n			science\r\n			there'),(3021,'computer\r\n			science'),(4638,'computer\r\n			science<'),(2514,'computer'),(4539,'computers\r\n			and'),(599,'computers'),(2533,'computing'),(5498,'comunicación'),(3630,'concentrate'),(994,'concentration'),(1018,'concept'),(2963,'conception'),(5626,'concepto'),(5426,'conceptos'),(106,'concepts'),(2410,'conceptually'),(726,'concerned'),(1201,'concertina-style'),(4253,'concise'),(2776,'concision\r\n			cheaply'),(4276,'concision'),(716,'conclusion'),(5603,'concretas'),(3224,'concrete'),(2856,'concurrency'),(2874,'concurrent\r\n			evaluation'),(4506,'concurrent'),(4517,'concurrently'),(5680,'condiciones'),(337,'condition'),(2843,'conditions'),(2983,'conduct'),(1225,'conducted'),(5593,'confección'),(2563,'confidence'),(5214,'confidently'),(2440,'configurable'),(1638,'configurations'),(823,'configured'),(4491,'confine'),(1086,'confined'),(3700,'conflict'),(3668,'conflicts'),(2208,'conform'),(5585,'conformación'),(4740,'confronting'),(3043,'confused'),(3434,'confusing'),(3896,'confusion'),(1332,'conjunction'),(354,'connect'),(399,'connected'),(576,'connecting'),(424,'connection'),(65,'connections'),(723,'connective'),(478,'connects'),(4714,'connotation'),(5621,'conocimiento'),(5543,'conocimientos'),(481,'conquer'),(1744,'conquered'),(1161,'conquest'),(1884,'conquests'),(1809,'conquistador'),(1794,'conquistadores'),(1150,'conquistadors'),(3290,'cons'),(4757,'conscientious'),(4034,'conscious'),(3459,'consciously'),(4262,'consensual'),(3191,'consensus'),(4966,'consent'),(626,'consequences'),(2591,'consider\r\n			the'),(269,'consider'),(5045,'considerably'),(83,'consideration'),(3794,'considerations'),(2576,'considered\r\n			more'),(129,'considered'),(4035,'considering'),(1905,'considers'),(94,'consist'),(1556,'consisted'),(4620,'consistency'),(1403,'consistent'),(1624,'consisting'),(517,'consists'),(1869,'constant'),(4905,'constantly\r\n			changes'),(1517,'constantly'),(2676,'constants'),(948,'constellation'),(824,'constellations'),(4896,'construct'),(436,'constructed'),(335,'construction'),(1597,'constructions\r\n\r\n\r\n\r\n'),(1531,'constructions'),(331,'constructs'),(5609,'construir'),(5089,'consultant'),(4201,'consultants\r\n			use'),(4200,'consultants'),(4657,'consultants<'),(1943,'consumed'),(3646,'contagious'),(538,'contain'),(5125,'container\r\n			over'),(5126,'container'),(303,'containing'),(544,'contains'),(5657,'contenido'),(5383,'content\r\nhow'),(1264,'content'),(2558,'contention'),(4528,'contention?'),(5532,'contexto'),(1347,'contexts'),(4249,'contingency'),(4097,'continuation?\r\n			\r\n			5'),(2142,'continue'),(1722,'continued'),(1262,'continues'),(1196,'continuous'),(1831,'continuously'),(5085,'contractors'),(339,'contrary'),(1489,'contrasted'),(4029,'contribute'),(3986,'contribution'),(2300,'contrived\r\n			compared'),(3490,'control\r\n			source'),(1797,'control'),(3766,'control<'),(311,'controlled'),(307,'controls'),(5724,'convención'),(3867,'conversation\r\n			focuses'),(3875,'conversation'),(4336,'conveying'),(3219,'convince'),(2645,'convincing'),(3697,'cool'),(4540,'cooperating'),(1950,'copan'),(1981,'copán'),(2708,'copies'),(2703,'copy'),(1588,'corbel'),(1519,'core'),(2164,'cornerstone'),(4946,'corny'),(5077,'correct\r\n			vision'),(322,'correct'),(4984,'correction'),(4365,'correctness-checking'),(1856,'correspond'),(547,'corresponding'),(956,'corresponds'),(1945,'cosmic'),(800,'cosmos'),(38,'cost'),(26,'costs'),(4023,'costs?\r\n			•'),(1187,'cotinifolia'),(4285,'could\r\n			never'),(153,'could'),(1377,'count'),(2608,'counting'),(5192,'coupling'),(5155,'courage'),(549,'course'),(5296,'courses\r\n\r\n '),(5288,'courses'),(5344,'courses<'),(1707,'court'),(867,'courtly'),(809,'courts'),(1628,'courtyard;'),(2829,'cousin'),(2059,'cover'),(388,'covered'),(3669,'coworkers'),(2609,'cows'),(2877,'cpus'),(3789,'crack'),(3382,'crap'),(2310,'crash'),(2366,'crash?’'),(2317,'crashed'),(2308,'crashes'),(2210,'crashing'),(5431,'creación'),(4550,'create\r\n			artifacts'),(586,'create'),(1481,'created'),(4288,'creates'),(2255,'creating'),(2795,'creation'),(5232,'creative'),(2302,'creativity'),(3810,'criminal'),(5220,'crisp'),(610,'criteria'),(5653,'crítica'),(2271,'critical'),(4795,'criticism'),(4980,'criticize'),(1574,'crucial'),(1557,'crushed'),(914,'cruz'),(2706,'crystal\r\n			clear'),(5682,'cuales'),(4702,'cube'),(5405,'cultivate'),(5514,'cultura<'),(2097,'cultural'),(5567,'culturales'),(2114,'culturally'),(1939,'culture'),(2009,'cultures'),(1740,'current'),(4805,'currently'),(3095,'customer'),(2495,'customers'),(5306,'customize'),(888,'cycle'),(754,'cycles'),(746,'cyclical'),(4738,'damage'),(1237,'damaged'),(2711,'danger'),(1792,'dangerous'),(1000,'data'),(2693,'database\r\n			queries'),(2537,'database'),(4376,'databases'),(1095,'date'),(4235,'date;'),(1024,'dates'),(5182,'daunting'),(2888,'dawned'),(3158,'day---but'),(3126,'day;'),(3145,'days\r\n			documenting'),(1044,'days'),(4493,'dbms'),(5315,'deactivate'),(4853,'dead'),(669,'deadline'),(666,'deadlines\r\n			we'),(664,'deadlines'),(2546,'deadlock\r\n			is'),(2544,'deadlock'),(2457,'deal'),(3130,'dealing\r\n			with'),(4301,'dealing'),(2763,'deallocate'),(2815,'deallocated'),(2821,'deallocating'),(947,'deals'),(814,'death'),(4880,'debacle'),(4793,'debate'),(5429,'deber'),(2163,'debug\r\n			debugging'),(2161,'debug'),(3730,'debug<'),(2322,'debugger'),(2200,'debugging'),(1240,'decayed'),(3658,'deceptive\r\n			in'),(2379,'decide\r\n			which'),(2361,'decide'),(1246,'deciphered'),(1241,'decipherment'),(5570,'decir'),(4000,'decision\r\n			an'),(4012,'decision\r\n			because'),(4155,'decision\r\n			even'),(3711,'decision\r\n			have'),(3277,'decision'),(4646,'decision<'),(4004,'decisions'),(359,'declaration'),(2147,'declared'),(1141,'decline'),(4617,'decode'),(3122,'deconstructing'),(1620,'decorated'),(1633,'decoration'),(3218,'decrease'),(4066,'decreases'),(1692,'dedicated'),(4688,'deep'),(4713,'deeper'),(3626,'deeply'),(5276,'default'),(5283,'defaultca4k2r11'),(1616,'defeated'),(4099,'defection'),(3613,'defenses'),(1464,'defensive'),(3617,'defensively'),(40,'define'),(20,'defined'),(4353,'defining'),(3181,'definition'),(1741,'degenerated'),(1419,'degree'),(2232,'delay\r\n			between'),(230,'delete'),(233,'deleted'),(4151,'delicately'),(5035,'delineated'),(5098,'deliver\r\n			something'),(5094,'deliver'),(4840,'delivered'),(3397,'demand'),(460,'demands'),(4809,'demo'),(934,'demonstrate'),(860,'demonstrated'),(4109,'demonstrates'),(5141,'demoralizing'),(4299,'demos'),(2112,'densely'),(4384,'dent'),(4578,'department\r\n			or'),(4251,'depend\r\n			on'),(2991,'depend'),(4046,'dependence\r\n			modern'),(4045,'dependence'),(4648,'dependence<'),(2740,'dependent'),(511,'depending'),(650,'depends'),(1998,'depict'),(784,'depicted'),(1963,'depicting'),(1325,'depicts'),(3140,'depleted'),(3056,'deployment'),(3656,'depression'),(5418,'derecho'),(3339,'derive'),(3322,'derived'),(5579,'desarrolle'),(5687,'desarrollo'),(2128,'descendants'),(1921,'describe'),(1450,'described'),(1307,'describes'),(2001,'describing'),(4900,'description'),(5408,'descriptions\r\n\r\n'),(4202,'deserve'),(3915,'deserves'),(4347,'design\r\n			drawings'),(1410,'design\r\n\r\n\r\n\r\nas'),(1449,'design'),(2337,'design:\r\n			as'),(4492,'designated'),(3926,'designed'),(4018,'designed?\r\n			•'),(2953,'designing'),(1823,'designs'),(4106,'desirable'),(3902,'desire'),(5330,'desired'),(3299,'desires'),(631,'despite'),(5708,'despues'),(573,'destroy'),(1207,'destroyed'),(5114,'detailed'),(3868,'details'),(3146,'detectable'),(960,'detected'),(5564,'determinada'),(155,'determine'),(3223,'determines'),(3297,'determining'),(3072,'develop\r\n			software'),(1462,'develop'),(1017,'developed'),(3549,'developers'),(3401,'developing\r\n			code'),(5297,'developing'),(4115,'development\r\n			time\r\n			software'),(4652,'development\r\n			time<'),(1716,'development'),(3187,'devilishly'),(4239,'devote'),(3921,'devoted'),(4726,'devoutly'),(5474,'de kant<'),(327,'diagonal'),(308,'diagonals'),(5620,'dicho'),(1425,'dictated'),(2726,'dictionary'),(2505,'dictum'),(2309,'didn'),(1211,'diego'),(4824,'dies'),(5536,'diferentes'),(2816,'differ'),(4628,'difference\r\n			and'),(4595,'difference'),(3824,'different\r\n			from'),(5173,'different\r\n			language'),(567,'different'),(2095,'differentiate'),(5062,'differently:\r\n			\r\n			•'),(5179,'difficult\r\n			as'),(4812,'difficult\r\n			communication'),(3723,'difficult\r\n			person'),(119,'difficult'),(961,'diffuse'),(2117,'diffusion'),(23,'digraph'),(377,'dijkstra'),(4521,'dimension---such'),(299,'dimensioned'),(2349,'dimensions'),(3316,'diminishes'),(4422,'dinner'),(1159,'dire'),(64,'direct'),(223,'directed'),(392,'direction'),(1503,'directional'),(4049,'directly\r\n			under'),(398,'directly'),(4147,'disagree'),(3695,'disagreement\r\n			but'),(2093,'disagreement'),(1875,'disappeared'),(5204,'discern'),(5594,'disciplina'),(5546,'disciplinas'),(4077,'discouraged'),(2602,'discover'),(1586,'discovered'),(1657,'discoveries'),(4879,'discovering\r\n			that'),(832,'discrete'),(582,'discuss'),(4967,'discussed'),(3232,'discussion'),(3647,'disease'),(4813,'disheartened'),(3077,'dishonest'),(4725,'disillusion'),(2692,'disk'),(1924,'dispersed'),(1614,'display'),(1156,'displayed'),(3112,'displease'),(1222,'disputed'),(5140,'disrespectful'),(5013,'dissatisfaction'),(5009,'dissatisfied'),(1941,'disseminated'),(2007,'distance'),(1683,'distances'),(2011,'distant'),(1887,'distinct'),(4584,'distinction'),(1405,'distinctions'),(2132,'distinctive'),(1986,'distribution'),(3676,'disturbing'),(5691,'div>\r\n<'),(5692,'div>'),(2962,'diverged'),(5506,'diversas'),(1903,'diverse'),(1719,'diversity'),(480,'divide'),(507,'divided'),(3284,'divination'),(3256,'divine'),(990,'diving'),(1451,'division'),(4965,'do\r\n			backtrack'),(3552,'do\r\n			no'),(3622,'do\r\n			that'),(3467,'document\r\n			the'),(3378,'document'),(3385,'documentation\r\n			gives'),(3151,'documentation\r\n			takes'),(2209,'documentation'),(1650,'documented'),(3416,'documenting'),(5481,'documentos'),(3417,'documents'),(202,'does'),(636,'doesn'),(4458,'dogma'),(2981,'dogmatic'),(4557,'doing\r\n			ugly'),(4705,'doing\r\n			what'),(2916,'doing\r\n			when'),(2586,'doing'),(822,'doings'),(4526,'dominant'),(811,'dominated'),(5726,'donde'),(505,'done'),(2997,'done:'),(4391,'door'),(1689,'doorways'),(2838,'doubt'),(2638,'down'),(3719,'downs'),(969,'draconnis'),(1533,'draft'),(1359,'dramatic'),(4626,'dramatically'),(629,'drastic'),(313,'drawing'),(4331,'drawings'),(991,'dresden'),(3240,'drivel'),(5162,'driven'),(3542,'drivers'),(3726,'duck'),(3691,'ducked'),(5729,'duda'),(5696,'dudas'),(4186,'duration'),(282,'during'),(5604,'durkheim'),(3631,'duties'),(3212,'duty'),(1906,'dwellings'),(401,'dynamic'),(1885,'dynastic'),(1297,'dynasties'),(1858,'dynasty'),(2091,'dzibilchaltun'),(1636,'e-groups'),(4053,'each\r\n			component'),(2817,'each\r\n			kind'),(3125,'each\r\n			small'),(124,'each'),(3611,'eager\r\n			to'),(3108,'eager'),(1093,'earliest'),(1148,'early'),(805,'earth'),(2494,'easier\r\n			than'),(3430,'easier'),(280,'easily'),(457,'easy'),(1764,'eckixil'),(1928,'economic'),(5295,'edit'),(5238,'editors'),(2986,'edsger'),(5448,'edu-101'),(5453,'educación'),(5550,'educación;'),(4929,'educational'),(5664,'educativa'),(5642,'educativas'),(5632,'educativo'),(5648,'educativos'),(4205,'educators\r\n			in-house'),(1728,'edzná'),(4635,'effect'),(2570,'effective\r\n			to'),(3394,'effective'),(2171,'effectively'),(3551,'effectively;'),(3805,'effectively?\r\n			\r\n			if'),(883,'effects'),(2684,'efficient'),(2572,'efficiently'),(3375,'effort\r\n			made'),(469,'effort'),(4098,'effort?\r\n			\r\n			6'),(5302,'efront\r\ncreate'),(5275,'efront'),(5350,'efront<'),(1061,'egyptian'),(2836,'eight'),(574,'either'),(5551,'ejemplos'),(5324,'elearning'),(2992,'electronic'),(4808,'electronically'),(1575,'element'),(5590,'elementos'),(35,'elements'),(2055,'elevated'),(618,'elevation'),(3546,'eliminate'),(2380,'eliminated'),(1342,'elite'),(5552,'ello'),(2987,'eloquently'),(4707,'else\r\n			tells'),(566,'else'),(2663,'else?'),(2936,'email\r\n			threads'),(3216,'embarrassed'),(4987,'embarrassing'),(5243,'embedded\r\n			languages'),(5174,'embedded'),(5245,'embedding'),(1902,'embodiment'),(4820,'embryo'),(1894,'emphasis'),(5082,'emphasized'),(1838,'empire\r\n\r\n\r\n\r\n\r\n\r\n'),(1979,'empire'),(1802,'empires'),(2272,'employ'),(5406,'employee'),(4207,'employees\r\n			are'),(3331,'employees'),(5422,'empresas'),(2911,'emptied'),(4448,'empty'),(4059,'encapsulate'),(3796,'encapsulated'),(3482,'encapsulation'),(5437,'encarga'),(1705,'enclosed'),(2728,'encoded'),(2731,'encoding'),(2037,'encompassed'),(1763,'encompassing'),(5526,'encontrar'),(2655,'encounter'),(1879,'encountered'),(3506,'encourage'),(4861,'endpoint'),(701,'ends'),(3914,'energy'),(3724,'engaged'),(3015,'engaging'),(3141,'engineer'),(4847,'engineering\r\n			team'),(4014,'engineering'),(3134,'engineers'),(3313,'enjoying\r\n			your'),(1541,'enormous'),(2580,'enough\r\n			to'),(1548,'enough'),(4508,'enough?'),(5392,'ensuring'),(2851,'enter'),(5386,'enterprise'),(4431,'enthusiasm'),(3107,'enthusiastic'),(5689,'entidad'),(2211,'entire'),(1459,'entirely'),(833,'entities'),(1866,'entity'),(5558,'entre'),(4113,'entrepreneur'),(4001,'entrepreneurial'),(4799,'entrepreneurs\r\n			who'),(2446,'environment'),(3838,'environments'),(1271,'epigrapher'),(2119,'epigraphy'),(391,'equal'),(4730,'equated'),(987,'equidistant'),(1643,'equinoxes'),(5493,'equivale'),(301,'equivalent'),(2081,'erected'),(5230,'erotic'),(4513,'erratic'),(5169,'erroneous'),(2382,'error\r\n			i'),(1053,'error'),(3524,'error;'),(3738,'error<'),(2167,'errors'),(5688,'escuela'),(5656,'esencia'),(1688,'especially'),(5723,'esperes'),(3033,'essay'),(3492,'essential'),(5503,'ésta'),(1782,'establish'),(1471,'established'),(1911,'establishing'),(5515,'estados'),(5637,'estas'),(5559,'este'),(3220,'estimate\r\n			relatively'),(3102,'estimate\r\n			the'),(1168,'estimate'),(3182,'estimated\r\n			individually'),(3177,'estimated'),(5132,'estimates\r\n			will'),(3082,'estimates'),(3051,'estimation'),(5538,'estudia'),(5466,'estudio'),(5473,'estudios'),(5103,'ethic'),(5500,'etimológica'),(1038,'europe'),(1137,'europeans'),(5435,'evaluación'),(3804,'evaluate'),(3793,'evaluated\r\n			carefully'),(3211,'evaluated'),(4020,'evaluating'),(4041,'evaluation'),(251,'even'),(3640,'evenings'),(2359,'evenly'),(1640,'events'),(1871,'eventual'),(4443,'eventuality'),(2575,'eventually'),(3495,'ever\r\n			lost'),(1662,'ever'),(4999,'every\r\n			other'),(72,'every'),(3354,'everybody\r\n			there'),(5010,'everybody'),(157,'everyone'),(2842,'everything'),(930,'evidence'),(845,'evil'),(1408,'evolution'),(5195,'evolve'),(4886,'evolved\r\n			from'),(1500,'evolved'),(1518,'evolving'),(1043,'exactly'),(4991,'exaggerated'),(176,'examination'),(427,'examine'),(252,'examined'),(332,'examines'),(2169,'examining\r\n			it'),(2386,'examining'),(3226,'example\r\n			the'),(165,'example'),(438,'examples'),(1677,'exceed'),(674,'exceeded'),(1622,'exceedingly'),(2937,'except'),(4982,'exception:'),(3836,'exceptions'),(1169,'excess'),(2123,'exchange'),(4848,'excited'),(1002,'exclusively'),(2228,'execute'),(2236,'executed'),(2263,'executes'),(2244,'executing'),(545,'execution'),(3325,'executive'),(3616,'executives'),(1793,'exercise'),(4939,'exercising'),(2791,'exhausted\r\n			in'),(2793,'exhausted'),(175,'exhausting'),(431,'exist'),(5533,'existe'),(5542,'existen'),(5254,'existing\r\n			language'),(66,'existing'),(2373,'exists'),(5640,'éxito'),(709,'expand'),(16,'expansion'),(2990,'expcs'),(499,'expect'),(680,'expectable'),(288,'expected'),(1779,'expeditions'),(2686,'expense\r\n			for'),(2508,'expense'),(3747,'expense<'),(2463,'expensive'),(2344,'experience'),(3431,'experienced'),(3050,'experiment\r\n			simple---you'),(2284,'experiment'),(5599,'experimentación'),(2989,'experimental\r\n			science'),(3012,'experimental'),(2198,'experimentation'),(2985,'experiments\r\n			the'),(2984,'experiments'),(3035,'experiments;'),(3755,'experiments<'),(3270,'expert'),(3833,'experts\r\n			on'),(4246,'experts'),(3034,'explain'),(2988,'explained'),(4718,'explains'),(641,'explanation'),(5042,'explicitly\r\n			what'),(2762,'explicitly'),(2279,'exploratory'),(2742,'explore\r\n			it'),(5334,'explore'),(4183,'explored'),(588,'exponent'),(4468,'exposed'),(4794,'exposes\r\n			you'),(5606,'expresa'),(3984,'express'),(4153,'expressed'),(2669,'expression'),(1267,'extant'),(1837,'extend'),(1852,'extended'),(1759,'extending'),(1659,'extensive'),(3550,'extensively'),(1258,'extent'),(1720,'external'),(3596,'extra'),(3590,'extraordinary'),(3907,'extreme\r\n			programming'),(2729,'extreme'),(554,'extremely'),(3715,'eyes'),(3075,'face'),(463,'faced'),(5209,'facetious'),(4745,'facilitate'),(4323,'facillitate'),(394,'fact'),(1821,'factor'),(4248,'fail'),(2960,'failed'),(2611,'failing'),(3282,'fails'),(5002,'failure\r\n			in'),(2552,'failure'),(5005,'failures'),(3591,'fair'),(4348,'fairly'),(1946,'fall'),(5241,'fallen'),(1045,'falls'),(3386,'false'),(4326,'familiarity'),(2504,'famous'),(4272,'fancy\r\n			project'),(3776,'fancy'),(1361,'fantastic'),(2905,'far\r\n			as'),(5231,'fascination'),(1604,'fashion'),(451,'fast'),(5024,'faster\r\n			if'),(2499,'faster'),(3660,'fatigue'),(4985,'fault'),(4581,'favor'),(4579,'favors'),(2275,'fear'),(4234,'feature'),(1421,'features'),(4835,'feedback'),(5303,'feedbacks'),(4825,'feeds'),(2289,'feel'),(1667,'feet'),(5242,'fell'),(5539,'fenómeno'),(4889,'fiat'),(4712,'fiction'),(589,'fictional'),(1186,'ficus'),(1260,'field'),(1812,'fierce'),(1378,'fifty-two'),(5092,'fight'),(5111,'fighting'),(4525,'figure\r\n			out'),(862,'figure'),(1609,'figures'),(2074,'figurines'),(2694,'file'),(2912,'fill\r\n			up'),(2778,'fill'),(5662,'filosofía'),(628,'final'),(93,'finally'),(3060,'finance'),(5096,'financial'),(2466,'find\r\n			the'),(28,'find'),(494,'finding'),(1124,'findings'),(182,'finds'),(4613,'fine'),(3979,'finish'),(3516,'finished'),(1563,'finishing'),(955,'fire'),(1348,'fired'),(4678,'firings'),(4601,'firm'),(3005,'firmly'),(19,'first'),(5158,'firsthand'),(2651,'five'),(3582,'fixed'),(2398,'fixes'),(2387,'fixing'),(871,'flat'),(3366,'flatters'),(4769,'flawed'),(3934,'flaws'),(2671,'floating'),(1756,'flores'),(1723,'flourish'),(3,'floyd'),(5121,'fluid'),(1457,'focus'),(2331,'focused'),(1899,'focuses'),(2675,'fold'),(1198,'folded'),(945,'folk'),(3292,'follow'),(685,'followed'),(96,'following'),(729,'following:'),(266,'follows'),(3519,'for\r\n			anybody'),(4039,'for\r\n			building'),(4298,'for:'),(321,'forbiddingly'),(3377,'force'),(4961,'forced'),(3213,'forcefully'),(877,'forces'),(2353,'foreign\r\n			code'),(563,'forest'),(1822,'forestalling'),(2335,'forget'),(4409,'forgiving'),(2879,'forgot'),(513,'form'),(5528,'formación'),(4418,'formal\r\n			training'),(4330,'formal'),(4319,'formally'),(4789,'format'),(3990,'formation'),(2105,'formative'),(1232,'formerly'),(97,'formula'),(1940,'formulated'),(5076,'formulating'),(1463,'fortress-like'),(78,'found'),(1605,'foundation'),(4684,'founded'),(1047,'four'),(4806,'fourpronged\r\n			approach:'),(1219,'fourth'),(1277,'fraction'),(1226,'fragments'),(4375,'franca'),(2476,'free'),(2799,'freeing'),(2769,'frees'),(2929,'french'),(2051,'frequent'),(4976,'frequently'),(1474,'fresh-water'),(5237,'friend'),(2205,'from\r\n			a'),(3251,'from\r\n			the'),(30,'from'),(4274,'front'),(2630,'fruit'),(2635,'fruit?'),(3887,'frustrated\r\n			with'),(262,'fulfill'),(257,'fulfilled'),(587,'fulfills'),(1280,'full'),(4870,'full:\r\n			\r\n			i'),(387,'fully'),(4387,'fun\r\n			test'),(5631,'funcionamiento'),(5658,'funciones'),(382,'function'),(2871,'functional\r\n			programming'),(2875,'functional'),(1631,'functionality'),(590,'functions'),(2177,'fundamental'),(5472,'fundamento'),(1306,'funeral'),(341,'further'),(487,'furthermore'),(5110,'fuss'),(527,'future'),(939,'fuzzy'),(2193,'gain'),(3155,'gained\r\n			by'),(672,'gained'),(3477,'gained?\r\n			\r\n			in'),(5617,'garante'),(2767,'garbage\r\n			collector'),(2759,'garbage'),(4754,'gather'),(1456,'gathering'),(3779,'gee-whiz\r\n			stuff'),(4137,'gem:\r\n			\r\n			remember'),(2002,'genealogy'),(3859,'general\r\n			and'),(105,'general'),(5521,'generales'),(1190,'generally'),(4393,'generated'),(1165,'generations'),(569,'genetic'),(5006,'gentle\r\n			as'),(5031,'gentle'),(1223,'genuine'),(1122,'geographic'),(1865,'geographical'),(1737,'geographically'),(1413,'geography'),(4843,'germ'),(5212,'gets\r\n			most'),(2571,'gets'),(254,'getting'),(2908,'giant\r\n			problem---unless'),(3263,'gigantic'),(197,'give'),(137,'given'),(103,'gives'),(486,'giving'),(3183,'global'),(5305,'glossary\r\n'),(5332,'glossary'),(5352,'glossary<'),(954,'glowing'),(1081,'glyphs'),(2357,'go\r\n			wrong'),(3865,'goals'),(813,'gods'),(68,'goes'),(5461,'gogos<'),(2589,'going'),(1818,'gold'),(3408,'golden'),(844,'good'),(4104,'good?\r\n			\r\n			8'),(3536,'good?'),(2932,'goodness!'),(2016,'goods'),(1495,'governmental'),(4314,'gradually'),(1344,'graffiti'),(4456,'graham'),(1703,'grand'),(1672,'grandiose'),(3871,'granted'),(91,'graph'),(215,'graphs'),(3856,'great\r\n			opportunity'),(1418,'great'),(1515,'greater'),(4399,'greatest'),(616,'greed'),(456,'greediness'),(1,'greedy'),(534,'greek'),(1040,'gregorian'),(1434,'grew'),(297,'grid'),(1493,'grid-like'),(5457,'griego παιδιον<'),(1220,'grolier'),(4125,'ground'),(3830,'grounded'),(1648,'group'),(1926,'grouped'),(1747,'groups'),(3980,'grow'),(3508,'growing'),(5206,'grown'),(4821,'grows'),(4983,'growth\r\n			or'),(4836,'growth'),(350,'gt;-1'),(348,'gt;-2'),(670,'gt;0'),(349,'gt;1'),(347,'gt;2'),(346,'gt;3'),(149,'gt;c'),(31,'gt;j'),(418,'gt;v'),(329,'gt;±'),(445,'guarantee'),(5069,'guard'),(918,'guatemala'),(1786,'guatemalan'),(2846,'guess'),(5308,'guide'),(2010,'gulf-coast'),(4660,'h1>\r\n			a'),(4667,'h1>\r\n			abstraction'),(4647,'h1>\r\n			an'),(4645,'h1>\r\n			assume'),(4656,'h1>\r\n			carefully'),(3773,'h1>\r\n			computer'),(3731,'h1>\r\n			debugging'),(4654,'h1>\r\n			disagreement'),(5273,'h1>\r\n			embedding'),(4641,'h1>\r\n			engineers'),(3758,'h1>\r\n			estimation'),(4643,'h1>\r\n			evaluating'),(3748,'h1>\r\n			for'),(3739,'h1>\r\n			i'),(4662,'h1>\r\n			integration'),(3765,'h1>\r\n			it'),(4665,'h1>\r\n			learn'),(3743,'h1>\r\n			learning'),(3762,'h1>\r\n			life'),(3741,'h1>\r\n			logging'),(3750,'h1>\r\n			memory'),(4649,'h1>\r\n			modern'),(3744,'h1>\r\n			most'),(5267,'h1>\r\n			nietschze'),(3760,'h1>\r\n			respect'),(4653,'h1>\r\n			software'),(3746,'h1>\r\n			sometimes'),(3767,'h1>\r\n			source'),(4668,'h1>\r\n			stress'),(3752,'h1>\r\n			the'),(4639,'h1>\r\n			there'),(5271,'h1>\r\n			time-to-market'),(3754,'h1>\r\n			to'),(3769,'h1>\r\n			unit'),(4658,'h1>\r\n			use'),(4651,'h1>\r\n			using'),(3771,'h1>\r\n			when'),(3775,'h1>\r\n			you'),(5055,'habits'),(5149,'hacker'),(4867,'hafernik'),(1315,'hair'),(4103,'half'),(1910,'halls'),(4927,'hand'),(3184,'handle'),(4150,'handled'),(1424,'haphazardly'),(2915,'happen'),(4700,'happening'),(396,'happens'),(5054,'happenstance'),(4568,'happy'),(3850,'hard\r\n			even'),(2443,'hard'),(3031,'hard-to-repeat'),(3400,'hard:'),(1551,'hardened'),(2424,'harder'),(4941,'hardest'),(2690,'hardware\r\n			device'),(2682,'hardware'),(2996,'harm'),(5075,'harmoniously'),(3318,'has:'),(2781,'hash'),(5714,'hasta'),(1804,'hasten'),(4758,'hates'),(2556,'have\r\n			a'),(3693,'have\r\n			something'),(4161,'have\r\n			the'),(3242,'have\r\n			thought'),(2378,'have\r\n			to'),(357,'have'),(4803,'haven'),(568,'having'),(4586,'head'),(1330,'headdresses'),(1845,'headed'),(1615,'heads'),(3568,'health'),(4839,'healthful'),(4934,'healthy\r\n			teamwork'),(5097,'healthy'),(2753,'heap'),(4748,'hear\r\n			you'),(3359,'hear'),(5259,'hear<'),(3946,'heard'),(3314,'hearing'),(782,'heart'),(950,'hearths'),(3961,'heat'),(1508,'heavenly'),(1656,'heavens'),(3603,'heavy'),(5671,'hecho'),(5647,'hechos'),(1883,'hegemonic'),(1601,'height'),(1444,'heights'),(777,'held'),(3353,'hell'),(4265,'help'),(2561,'helpful'),(2330,'helps'),(2758,'hence'),(3449,'her;\r\n			and\r\n			•'),(413,'here'),(1846,'hereditary'),(611,'herein'),(2151,'heritage'),(3196,'herself\r\n			and'),(112,'heuristic'),(2240,'hidden'),(4464,'hide'),(4459,'hiding'),(1840,'hierarchical'),(259,'hierarchy'),(2000,'hieroglyphic'),(1305,'hieroglyphics'),(1058,'hieroglyphs'),(1938,'high'),(4913,'high-performing'),(2636,'higher'),(993,'highest'),(917,'highlands'),(1319,'highlights'),(1035,'highly'),(617,'hill'),(1438,'hills'),(3030,'hint'),(2864,'hints'),(3916,'hire'),(3949,'hired'),(4680,'hirings'),(5584,'historia'),(3569,'historical'),(1773,'historiography'),(1714,'history\r\n\r\n\r\n\r\nduring'),(1966,'history\r\n\r\nthe'),(2064,'history\r\n\r\nwhile'),(2063,'history'),(4497,'hitting'),(5187,'hodgepodge;'),(2239,'hold'),(1937,'holy'),(3566,'home\r\n			computer'),(3565,'home'),(3772,'home<'),(1521,'homes'),(3288,'homework'),(3650,'homicidal'),(2031,'honduras'),(3104,'honest'),(3076,'honestly'),(4428,'honesty'),(5139,'hoodwinked'),(3217,'hopefully\r\n			in'),(4213,'hopefully\r\n			your'),(2965,'hopefully'),(4223,'hopes'),(3855,'horrible'),(4878,'horror'),(3597,'hour'),(2837,'hours'),(4086,'house'),(1621,'housed'),(1896,'household'),(1933,'households'),(1578,'houses'),(3099,'how\r\n			obvious'),(2959,'how\r\n			the'),(696,'however'),(2922,'html'),(1291,'hudson'),(2730,'huffman'),(771,'human'),(5471,'humanas'),(5485,'humanidades'),(2152,'humanity'),(5578,'humano'),(4515,'humans'),(1022,'hundreds'),(2048,'hurricanes'),(4531,'hurt'),(3040,'hypothesis'),(2328,'i\r\n			find'),(5144,'i\r\n			hope'),(5462,'i> -conducir'),(5459,'i> -niño'),(5516,'iberoamericanos'),(1645,'iconographic'),(393,'idea'),(864,'ideal'),(4440,'idealists:'),(2390,'ideally'),(3360,'ideas'),(1094,'identifiably-maya'),(3165,'identify'),(1488,'identity'),(2137,'ideologies'),(4699,'if\r\n			nothing'),(3388,'if\r\n			someone'),(4212,'if\r\n			the'),(2304,'if\r\n			there'),(3371,'if\r\n			you'),(5337,'iframe&gt;&lt;'),(2746,'ignore'),(2833,'ignored'),(458,'ignores'),(5494,'igualdad'),(3654,'illness'),(1263,'illuminate'),(4289,'illusion'),(2917,'illustrates'),(4834,'image'),(1352,'imitate'),(4076,'immature\r\n			using'),(4075,'immature'),(4650,'immature<'),(4280,'immediate'),(1510,'immediately'),(3996,'impact\r\n			on'),(3712,'impact'),(1160,'impacts'),(4746,'impede'),(3027,'imperfect\r\n			knowledge'),(2190,'imperfect'),(2191,'imperfectly\r\n			documented'),(3006,'implanted\r\n			in'),(2824,'implement'),(5612,'implementación'),(2721,'implementation'),(4139,'implementations'),(218,'implemented'),(5573,'implícita'),(3135,'implicitly'),(3672,'implies'),(5376,'import'),(27,'importance'),(5628,'importancia'),(4426,'important\r\n			skills'),(3963,'important\r\n			than'),(3053,'important\r\n			to'),(39,'important'),(3756,'important<'),(5591,'importantes'),(3432,'importantly\r\n			however'),(4010,'importantly'),(2159,'imported_lesson'),(3079,'impossibility\r\n			of'),(5203,'impossible\r\n			it'),(3062,'impossible\r\n			to'),(3073,'impossible'),(5272,'impossible<'),(2527,'impractical'),(3609,'impress'),(3376,'impression'),(1443,'impressive'),(2549,'improper'),(2733,'improve'),(2720,'improved'),(2599,'improvement'),(2400,'improvement;'),(1564,'improvements'),(2578,'improving'),(4408,'in\r\n			a'),(4340,'in\r\n			it'),(3462,'in\r\n			order'),(5017,'in\r\n			public'),(2797,'in\r\n			the'),(4955,'in\r\n			this'),(2362,'in\r\n			which'),(4309,'in\r\n			your'),(4024,'in?\r\n			\r\n			you'),(2547,'inability'),(3518,'inactive\r\n			and'),(850,'inappropriate'),(741,'inca'),(4096,'incentive'),(1135,'inception'),(698,'inclining'),(951,'include'),(3023,'include:\r\n			\r\n			•'),(886,'included'),(364,'includes'),(1090,'including'),(5120,'incompressible'),(3433,'inconsistent'),(5561,'incorporar'),(2057,'incorporate'),(4070,'incorporated\r\n			into'),(3809,'increase\r\n			performance'),(366,'increase'),(2775,'increases'),(1718,'increasing'),(1889,'increasingly'),(1389,'indeed'),(3671,'independence'),(4156,'independent\r\n			and'),(1427,'independent'),(1016,'independently'),(5487,'independientes'),(2614,'index'),(5534,'indica'),(1327,'indicating'),(2680,'indices'),(1807,'indigenous'),(370,'indirect'),(5608,'indispensable'),(4396,'indistinguishable'),(4944,'individual\r\n			both'),(1170,'individual'),(4893,'individually'),(1152,'individuals'),(363,'inductive'),(4561,'industries'),(3909,'inefficiency;'),(3782,'inefficient'),(3623,'inertia'),(3952,'inessential'),(1952,'inevitable'),(2218,'inevitably'),(2623,'inexpert'),(715,'infinite'),(908,'influence'),(2106,'influenced'),(904,'influences'),(4762,'inform\r\n			everyone'),(2522,'inform'),(4195,'informal'),(3306,'information\r\n			sources\r\n			respect'),(3759,'information\r\n			sources<'),(2438,'information\r\n			that'),(488,'information'),(2428,'informative'),(4281,'informing'),(2303,'ingenuity'),(1634,'inhabitants'),(2065,'inhabited'),(4964,'initial'),(429,'initialization'),(533,'initialized'),(57,'initializes'),(1824,'initially'),(4785,'initiate'),(1780,'initiated'),(5240,'initiates'),(1336,'inkpots'),(2618,'inner'),(4080,'inoperability'),(4522,'input\r\n			rate'),(2507,'input'),(247,'inquisition'),(756,'inscribed'),(5717,'inscribirte!'),(5720,'inscripción'),(1019,'inscriptions'),(4171,'insecure'),(575,'insert'),(343,'inserted'),(646,'inside'),(5118,'insight'),(3465,'insist'),(3390,'insists'),(3249,'install'),(1429,'instance'),(1585,'instances'),(2713,'instantaneously'),(2298,'instead\r\n			it'),(1042,'instead'),(3989,'instigate'),(1383,'instigated'),(5685,'instituciones'),(4909,'instrument'),(2395,'insufficiently'),(2873,'insured'),(1214,'intact'),(2150,'intangible'),(671,'integer'),(1699,'integral'),(1417,'integrate'),(3999,'integrating'),(3817,'integration'),(4021,'integration?\r\n			•'),(3028,'integrity'),(1971,'intellectual'),(2813,'intelligence'),(3692,'intelligent'),(5574,'intencionalidad'),(5088,'intend'),(2416,'intended'),(1977,'intensive'),(4887,'intentional'),(2384,'intentionally'),(2184,'interact'),(2116,'interaction'),(4355,'interchange'),(1158,'interest'),(977,'interested'),(4784,'interesting'),(1868,'interestingly'),(5166,'interests\r\n			of'),(3786,'interface'),(4140,'interfaces'),(1458,'interior'),(4591,'intermediate\r\n			programmer'),(2422,'intermediate'),(2827,'intermittent'),(3347,'intern'),(5508,'internacionales'),(526,'internal'),(3234,'internet'),(3673,'interpersonal'),(5652,'interpretación'),(1091,'interpretation'),(3238,'interpretation:'),(759,'interpreting'),(829,'intersection'),(3962,'interview\r\n			it'),(3960,'interview'),(3968,'interviewee'),(3913,'interviewees\r\n			evaluating'),(3912,'interviewees'),(4642,'interviewees<'),(3936,'interviewers'),(3924,'interviewing'),(3839,'intimate'),(5060,'intimately'),(3843,'intimidated'),(4538,'into\r\n			memory'),(3618,'into\r\n			what'),(578,'into'),(5417,'introducción'),(5447,'introduccion'),(5433,'introducción&lt;'),(5622,'introduccion<'),(579,'introduction'),(4598,'intuit'),(3683,'inured'),(1920,'invariably'),(964,'invented'),(4033,'invest'),(4575,'invested'),(5668,'investigación'),(3119,'investigation'),(4921,'invoke'),(3627,'involved'),(2233,'involves'),(4677,'ipos'),(4383,'irrelevant'),(4612,'is\r\n			about'),(4333,'is\r\n			both'),(2482,'is\r\n			currently'),(4196,'is\r\n			done'),(4145,'is\r\n			hard'),(3067,'is\r\n			impossible'),(4227,'is\r\n			not'),(4759,'is\r\n			nothing'),(4472,'is\r\n			perhaps'),(3605,'is\r\n			programmers'),(3579,'is\r\n			serious'),(2423,'is\r\n			sometimes'),(2510,'is\r\n			spent'),(3172,'is\r\n			the'),(3544,'is\r\n			very'),(2342,'is\r\n			where'),(4842,'is\r\n			your'),(717,'is:\r\n			\r\n			\r\n				\r\n					\r\n						'),(328,'is:\r\n			\r\n			v'),(86,'is:\r\n			a'),(687,'is:\r\n			k'),(4131,'isolate'),(279,'isolated'),(5033,'issue\r\n			from'),(3816,'issue'),(4772,'issues\r\n			you'),(2509,'issues'),(3423,'it\r\n			clear'),(4149,'it\r\n			disagreement'),(2739,'it\r\n			for'),(5248,'it\r\n			help?'),(4088,'it\r\n			is'),(4306,'it\r\n			should'),(3634,'it\r\n			takes'),(4089,'it:\r\n			\r\n			1'),(4535,'it;\r\n			no'),(3439,'it;\r\n			•'),(5256,'it?\r\n			\r\n		\r\n	\r\n\r\n'),(5274,'it?\r\n			<'),(3132,'item'),(1942,'items'),(291,'itself'),(1726,'itza'),(1758,'itzá'),(816,'itzamna'),(344,'j:=v'),(2020,'jade'),(1120,'january'),(2895,'java™'),(5449,'jesus'),(367,'jk+1'),(5064,'job;'),(4862,'jobs'),(1367,'jolja'),(139,'journey'),(2369,'judge'),(2966,'judgment\r\n			that'),(2417,'judgment'),(5684,'juegan'),(1049,'julian'),(2282,'jump;'),(1679,'jungle'),(5715,'jurisprudencia'),(193,'just'),(5587,'kant'),(5081,'kawasaki'),(1684,'keen'),(159,'keep'),(924,'keeper'),(3049,'keeping'),(1592,'kept'),(3812,'keys'),(4394,'keystrokes'),(774,'killed'),(2628,'kind'),(3900,'kindness'),(2265,'kinds'),(1897,'king'),(1855,'kingdom'),(1769,'kingdoms'),(1151,'knew'),(2995,'knife'),(2269,'know'),(5071,'knowing\r\n			what'),(902,'knowing'),(4177,'knowledge\r\n			changes'),(935,'knowledge'),(4413,'knowledge?'),(116,'known'),(4573,'knows'),(3131,'knuckleheads'),(2090,'komchen'),(559,'kruskal'),(1693,'kukulcan'),(1848,'k’uhul'),(3101,'labor'),(1245,'laborious'),(1526,'lack'),(1465,'lacked'),(1532,'lacking'),(1994,'lacks'),(1757,'lake'),(1761,'lakes'),(1861,'land'),(1212,'landa'),(1798,'lands'),(1876,'landscape'),(3407,'language\r\n			in'),(3858,'language\r\n			that'),(1077,'language'),(5229,'languages\r\n			embedding'),(5146,'languages\r\n			the'),(4318,'languages\r\n			there'),(2141,'languages'),(4322,'languages---they'),(4663,'languages<'),(356,'large'),(1468,'large-scale'),(1851,'larger'),(1922,'largest'),(4923,'larry'),(324,'last'),(1247,'late'),(744,'later'),(2957,'latest'),(4350,'latin'),(982,'latitude'),(4292,'latter'),(4977,'lavishly'),(2840,'laws\r\n			of'),(4675,'layoffs'),(5316,'layout'),(1499,'layouts'),(4925,'laziness'),(5497,'lazos'),(5467,'la educación<'),(5553,'la historia<'),(5510,'la organización'),(5456,'la pedagogía<'),(5557,'la política<'),(5555,'la psicología<'),(5554,'la sociología<'),(204,'lead'),(3706,'leader'),(3722,'leaders'),(4791,'leadership'),(491,'leading'),(637,'leads'),(2788,'leak'),(2790,'leaks'),(4850,'leaps'),(2160,'learn'),(5320,'learn:\r\n\r\nhow'),(5356,'learn:<'),(4722,'learned'),(1281,'learning'),(3312,'learns'),(162,'least'),(3127,'leave\r\n			anything'),(3874,'leave'),(278,'leaves'),(4420,'lectures'),(702,'left'),(5507,'legislaciones'),(776,'legs'),(1037,'length'),(1791,'lengthy'),(395,'less'),(2774,'lessens'),(1511,'lesser'),(5322,'lesson\r\n'),(581,'lesson'),(5360,'lesson<'),(5381,'lessons\r\nhow'),(5287,'lessons'),(3545,'lets'),(2481,'level'),(275,'levels'),(5255,'leverage'),(5520,'leyes'),(5439,'leyes?&lt;'),(5633,'li>\r\n\r\n-'),(5345,'li>\r\n<'),(5368,'li>\r\nannouncements<'),(5367,'li>\r\ncalendar<'),(5364,'li>\r\nchat<'),(5369,'li>\r\ncomments<'),(5343,'li>\r\ncreate'),(5359,'li>\r\nhow'),(5365,'li>\r\npersonal'),(5351,'li>\r\nuse'),(2624,'libraries'),(3243,'library'),(2920,'licensed'),(857,'lies'),(868,'life'),(855,'life-cycle'),(1700,'lifestyle'),(4520,'light'),(4532,'lightly'),(2235,'like\r\n			the'),(797,'like'),(2375,'like:'),(3088,'likely'),(3171,'likewise'),(4838,'limbs'),(1430,'limestone'),(1569,'limestone-stucco'),(1591,'limitations'),(2435,'limited;'),(2365,'line\r\n			makes'),(229,'line'),(3199,'line-by-line'),(4362,'linear'),(1026,'lines'),(4374,'lingua'),(1270,'linguist'),(1576,'lintel'),(1175,'lintels'),(3272,'lisp'),(3280,'list'),(3372,'listen'),(4778,'listening'),(1324,'literacy\r\n\r\n\r\n\r\nscribes'),(1339,'literacy'),(882,'literally'),(1157,'little'),(2180,'live'),(1302,'lives'),(5572,'lleva'),(4496,'load'),(4533,'loaded'),(4619,'local\r\n			copy'),(909,'local'),(1934,'locales'),(1088,'localities'),(1857,'locality'),(2705,'locally'),(2083,'located'),(1428,'location'),(1440,'loft'),(2427,'log\r\n			logging'),(3740,'log<'),(4605,'logarithm'),(2258,'logging\r\n			are'),(2254,'logging'),(2841,'logic'),(693,'logically'),(5451,'logo'),(1064,'logograms'),(1066,'logographic'),(4947,'logoized'),(1068,'logosyllabic'),(5477,'logra'),(2434,'logs'),(1289,'london:'),(1244,'long'),(2407,'longer'),(605,'look'),(2242,'looking'),(2346,'looks'),(2659,'loop'),(2653,'loops\r\n			sometimes'),(2619,'loops'),(3745,'loops<'),(5191,'loose'),(2053,'loosely'),(4093,'lore'),(2291,'lose'),(5030,'loss'),(1163,'lost'),(3404,'lousy'),(3066,'love'),(5148,'loves'),(2629,'low-hanging'),(2045,'low-lying'),(3800,'low?\r\n			\r\n			•'),(274,'lower'),(1974,'lowland'),(1721,'lowlands'),(406,'lt;='),(730,'lt;=cost'),(3964,'luck'),(2372,'lucky'),(5728,'lugar'),(1228,'lumps'),(894,'lunation'),(4197,'lunches'),(4890,'lux!\r\n			\r\n		\r\n	\r\n\r\n'),(5262,'lux!\r\n			<'),(3014,'luxury'),(2212,'machine'),(3008,'machines'),(4439,'made\r\n			by'),(4767,'made\r\n			harder'),(609,'made'),(2040,'madre'),(1216,'madrid'),(5730,'maestría'),(5678,'maestro'),(2223,'magic'),(4731,'magnitude'),(74,'main'),(1295,'mainly'),(2131,'maintain'),(885,'maintained'),(4100,'maintainers?\r\n			\r\n			7'),(4622,'maintaining'),(3799,'maintenance'),(856,'maize'),(802,'major'),(1257,'majority'),(5025,'make\r\n			more'),(3831,'make\r\n			things'),(5107,'make\r\n			us'),(495,'make'),(4170,'maker'),(4160,'makers'),(4064,'makes\r\n			porting'),(2462,'makes'),(2220,'making'),(3653,'malfunctioning\r\n			or'),(4410,'manage\r\n			a'),(2743,'manage'),(4548,'managed'),(2352,'management'),(3156,'manager'),(4709,'managerial'),(3065,'managers'),(5385,'managing'),(5707,'mañana'),(5171,'mandated'),(3174,'mandatory'),(4370,'manipulation'),(4719,'mankind'),(1540,'manpower'),(1181,'manufactured'),(584,'many'),(1686,'mapped'),(227,'mark'),(3509,'marked'),(5073,'market'),(3057,'marketing'),(4256,'marking'),(3917,'marriage'),(3910,'marrying'),(840,'massive'),(2707,'master'),(2709,'master---period'),(4804,'mastered'),(2148,'masterpiece'),(4017,'match'),(2820,'matched'),(3540,'matches?'),(3539,'matching\r\n			algorithm'),(1387,'matching'),(5607,'materia'),(1239,'material'),(1523,'materials\r\n\r\n\r\n\r\na'),(1542,'materials'),(1584,'materials;'),(3244,'math'),(600,'mathematical'),(1008,'mathematics\r\n\r\n\r\n\r\n	\r\n		\r\n			 \r\n		\r\n		\r\n			 \r\n			maya'),(1007,'mathematics'),(1118,'matter'),(765,'matters'),(3801,'mature'),(4082,'matures'),(1864,'maxam'),(677,'maximize'),(624,'maximized'),(409,'maximum'),(4583,'may\r\n			have'),(732,'maya'),(2121,'maya;'),(853,'mayan'),(1731,'mayapan'),(2157,'mayas\r\n\r\n\r\n\r\n\r\n'),(593,'maybe'),(3361,'maybe;\r\n			they'),(2389,'mean'),(3437,'mean?'),(3120,'meaning\r\n			of'),(2631,'meaning\r\n			that'),(606,'meaning'),(3094,'means\r\n			with'),(44,'means'),(3092,'means:\r\n			\r\n			i'),(3438,'means:\r\n			\r\n			•'),(903,'meant'),(355,'meanwhile'),(4876,'measurable'),(1036,'measure'),(4437,'measured'),(2557,'measuring'),(5421,'medianas'),(5611,'medio'),(3557,'meditate'),(3460,'meet'),(4193,'meeting'),(4185,'meeting;'),(3638,'meetings'),(5582,'mejor'),(5575,'mejoramiento'),(5007,'member'),(3208,'members'),(2814,'memory\r\n			and'),(2744,'memory\r\n			memory'),(2351,'memory'),(3749,'memory<'),(5701,'mensaje'),(2356,'mental'),(2950,'mentor'),(2290,'mentor---we'),(5440,'mercadotecnia'),(5425,'mercantil'),(5208,'mere\r\n			work'),(4230,'merely'),(837,'merge'),(2135,'merger'),(5252,'meshes'),(1099,'mesoamerica'),(1011,'mesoamerican'),(3487,'mess'),(5336,'messages\r\ncalendar\r\nannouncements\r\ncomments\r\n\r\n '),(5366,'messages<'),(4446,'messy'),(1537,'metal'),(1600,'meters'),(245,'method'),(439,'methods'),(5441,'metodología'),(5614,'metodológicas'),(4735,'metric'),(1931,'mexican'),(5423,'mexicano'),(1828,'mexico'),(1881,'mexico:'),(1268,'michael'),(2089,'mid-sized'),(952,'middle'),(3330,'might\r\n			be'),(627,'might'),(3655,'mild'),(2122,'miles'),(4279,'milestone'),(4255,'milestones'),(2003,'military'),(2066,'millennium'),(1023,'millions'),(1559,'mimicked'),(634,'mind'),(3007,'minds'),(1415,'minimal'),(432,'minimoum'),(10,'minimum'),(2661,'minutes\r\n			considering'),(3326,'minutes'),(1668,'mirador'),(3879,'miscommunicate'),(3081,'miscommunication'),(4504,'miserable'),(3615,'mislead\r\n			the'),(5491,'misma'),(4278,'miss'),(4291,'missed'),(2779,'mistake'),(3878,'misunderstanding'),(2099,'mixe-zoque–'),(1558,'mixed'),(4519,'model\r\n			some'),(267,'model'),(4873,'models'),(2796,'moderately'),(3271,'modern\r\n			database'),(597,'modern'),(1536,'modes'),(1520,'modest'),(5070,'modesty'),(2250,'modification'),(1395,'modifications'),(2455,'modify'),(2276,'modifying'),(5375,'modules\r\n'),(5197,'modules'),(2393,'moment'),(4556,'money'),(973,'monte'),(3327,'month'),(920,'months'),(1900,'monumental'),(1174,'monuments'),(896,'moon'),(1915,'moral'),(4401,'morale'),(3651,'more\r\n			than'),(195,'more'),(1724,'more;'),(1555,'mortar'),(5219,'most\r\n			attractive'),(4359,'most\r\n			boring'),(3106,'most\r\n			engineers'),(115,'most'),(1172,'mostly'),(4549,'motivated\r\n			it'),(1813,'motivated'),(4669,'motivated<'),(4402,'motivates'),(4569,'motivating'),(4562,'motivational'),(3948,'motivations'),(2079,'mounds'),(2038,'mountainous'),(2964,'move\r\n			on'),(655,'move'),(828,'movements'),(648,'moving'),(170,'msec'),(2582,'much\r\n			sense'),(852,'much'),(3351,'much;'),(2870,'multi-threaded'),(879,'multiple'),(4187,'multiplied'),(5541,'multirreferencial'),(2893,'multithreaded\r\n			application'),(3295,'multitude'),(5428,'mundos'),(1436,'municipalities'),(3245,'mushrooms'),(4313,'must\r\n			explicitly'),(120,'must'),(4466,'mutators\r\n			and'),(2214,'mute'),(4343,'myself'),(3547,'mysteries'),(2297,'mystery'),(2341,'mystery?'),(3246,'mysticism'),(4711,'myth'),(1647,'mythology'),(4710,'myths\r\n			the'),(1373,'myths'),(4728,'myths:\r\n			\r\n			•'),(5258,'myths<'),(60,'n\r\n			\r\n			at'),(5522,'nacionales'),(5511,'naciones'),(1191,'nahuatl-language'),(2086,'nakbe'),(1031,'naked'),(414,'name'),(989,'named'),(5026,'namely\r\n			the'),(4360,'namely'),(1859,'naranjo'),(826,'narratives'),(5250,'narrowly'),(1121,'national'),(2028,'nations'),(1420,'natural'),(5018,'naturally'),(747,'nature'),(4883,'nature:'),(640,'near'),(1127,'nearly'),(937,'nebula'),(681,'necessarily'),(1530,'necessary'),(1568,'necessity'),(2860,'need\r\n			when'),(172,'need'),(3409,'need:'),(4019,'need?\r\n			•'),(1630,'needed'),(2749,'needs'),(4797,'neglect'),(5040,'negotiate'),(1949,'negras'),(1849,'neighborhood'),(2098,'neighboring'),(1015,'neighbors'),(2124,'neither'),(706,'network'),(1874,'never'),(3511,'new\r\n			name'),(4732,'new\r\n			persons'),(548,'newly'),(4882,'news'),(188,'next'),(2603,'next-most-expensive'),(3785,'nice'),(4552,'nifty'),(818,'night'),(2831,'nightmare'),(919,'nine'),(4884,'no\r\n			large'),(4385,'no\r\n			matter'),(4737,'no\r\n			success'),(4574,'no\r\n			trust'),(1512,'nobles'),(3600,'noblesse'),(3383,'nobody'),(5712,'noches&lt;'),(73,'node'),(273,'nodes'),(268,'non-cycle'),(3822,'non-engineers\r\n			engineers'),(3821,'non-engineers'),(3827,'non-engineers;'),(4640,'non-engineers<'),(4542,'non-linear'),(2012,'non-mesoamerican'),(4488,'non-portable'),(3419,'non-programmers'),(3837,'non-team'),(4398,'non-technical'),(2787,'noncollectable'),(1927,'none'),(1350,'nonsensical'),(3659,'normally'),(5432,'normas:'),(1390,'north'),(1432,'northern'),(2528,'not\r\n			always'),(5065,'not\r\n			big'),(3013,'not\r\n			have'),(4530,'not\r\n			help'),(5004,'not\r\n			occasional'),(2560,'not\r\n			occur'),(2644,'not\r\n			only'),(3870,'not\r\n			understand'),(4377,'not\r\n			work'),(1587,'notable'),(1100,'notably'),(4594,'notation'),(5193,'notations\r\n			'),(5178,'notations'),(1300,'note'),(2109,'noted'),(3959,'notes'),(4877,'nothing\r\n			is'),(565,'nothing'),(51,'notice'),(2883,'noticeable'),(2768,'notices'),(1243,'now-lost'),(145,'number'),(728,'number:\r\n			cost'),(1338,'numbered'),(546,'numbering'),(1009,'numbers\r\n		\r\n	\r\n\r\nin'),(397,'numbers'),(1402,'numerous'),(2101,'oaxaca'),(2839,'obey'),(3446,'obfuscate'),(4460,'object\r\n			oriented'),(1005,'object'),(3225,'objective'),(786,'objects'),(5465,'objeto'),(3601,'oblige\r\n			and'),(1032,'observation'),(997,'observations'),(1027,'observations;'),(1505,'observatories'),(2229,'observe\r\n			something'),(2406,'observe\r\n			that'),(2832,'observe'),(755,'observed'),(2021,'obsidian'),(3484,'obtain'),(5137,'obvious'),(277,'obviously'),(1020,'occasion'),(4956,'occasionally\r\n			means'),(1497,'occasionally'),(1991,'occupation'),(1832,'occupied'),(985,'occur'),(2944,'occurred'),(2217,'occurring'),(2802,'occurs'),(4232,'of\r\n			a'),(4342,'of\r\n			cases'),(2921,'of\r\n			code'),(2472,'of\r\n			execution'),(4304,'of\r\n			existing'),(5201,'of\r\n			language'),(5012,'of\r\n			low'),(4122,'of\r\n			maintenance'),(4716,'of\r\n			religious'),(5227,'of\r\n			success'),(2486,'of\r\n			the'),(3947,'of\r\n			their'),(2585,'of\r\n			thumb'),(3332,'of\r\n			time'),(3781,'of\r\n			your'),(3978,'off\r\n			with'),(4119,'offends'),(4414,'offer\r\n			to'),(523,'offer'),(790,'offered'),(783,'offering'),(3642,'office'),(3500,'official'),(5483,'oficiales'),(4963,'often\r\n			cause'),(3898,'often\r\n			find'),(2881,'often\r\n			finishing'),(2425,'often\r\n			messy'),(3525,'often\r\n			taboo'),(2374,'often\r\n			the'),(118,'often'),(1128,'oldest'),(1112,'olmec'),(1108,'olmecs'),(2812,'on\r\n			garbage'),(3047,'on\r\n			the'),(4935,'on\r\n			you'),(3115,'on-the-spot'),(126,'once'),(4614,'one\r\n			can'),(4432,'one\r\n			sign'),(5170,'one-dimensional'),(5189,'one-language'),(3504,'one-person'),(1627,'one-story'),(443,'ones'),(5244,'oneself'),(63,'only'),(1467,'onset'),(5105,'onto'),(780,'open'),(4008,'open-source'),(4959,'openly'),(2819,'operation\r\n			is'),(2822,'operation'),(55,'operations'),(3236,'opinion'),(1618,'opponents'),(3889,'opportunities'),(3202,'opportunity\r\n			for'),(2642,'opportunity'),(1386,'opposed'),(643,'optimal'),(4028,'optimistic'),(3445,'optimizations'),(2665,'optimize\r\n			the'),(2652,'optimize'),(2583,'optimizing'),(5239,'optional'),(3266,'options'),(2319,'or\r\n			'),(3469,'or\r\n			all'),(3662,'or\r\n			amphetamines'),(2637,'or\r\n			chop'),(4708,'or\r\n			project'),(4919,'or\r\n			the'),(2493,'or\r\n			visibility'),(2323,'or\r\n			we'),(2237,'or\r\n			whether'),(4258,'or\r\n			your'),(2149,'oral'),(1507,'orbits'),(140,'order'),(336,'ordered'),(4261,'ordering\r\n			equipment'),(1238,'organic'),(4826,'organisms'),(5630,'organización'),(3329,'organization'),(4673,'organizational\r\n			chaos\r\n			there'),(4685,'organizational\r\n			chaos'),(5257,'organizational\r\n			chaos<'),(4697,'organizational\r\n			mayhem'),(4672,'organizational'),(4220,'organizations'),(4311,'organize'),(4266,'organized'),(5286,'organizing'),(1504,'orientation'),(967,'oriented'),(2925,'origin'),(2415,'original\r\n			author'),(4988,'original\r\n			fault'),(473,'original'),(2120,'originate'),(936,'orion'),(536,'oristiko'),(4555,'other\r\n			roles'),(180,'other'),(1437,'others'),(36,'otherwise'),(5545,'otras'),(3011,'ought'),(1284,'ourselves'),(761,'outlook'),(2312,'output'),(1795,'outset'),(647,'outside'),(3876,'outsider'),(3869,'outsiders'),(3476,'outweigh'),(4475,'outweighs'),(1665,'over'),(692,'overadded'),(3797,'overall'),(4996,'overburden'),(4294,'overestimated'),(3149,'overestimating\r\n			is'),(981,'overhead'),(2104,'overlapping'),(1803,'overthrown'),(4917,'overused'),(5327,'overview'),(3572,'overwhelming'),(4771,'overworked\r\n			or'),(3576,'overworked'),(4069,'owner'),(4477,'owns'),(1989,'oxkintok'),(5711,'p&gt;\r\nbuenas'),(5704,'p&gt;\r\nclaro'),(5713,'p&gt;\r\ngracias'),(5710,'p&gt;\r\nsaludos&lt;'),(5703,'p&gt;\r\n &lt;'),(5623,'p>\r\n\r\n\r\n\r\npedagogía'),(5624,'p>\r\n\r\n\r\n-'),(5342,'p>\r\n\r\nadd'),(5363,'p>\r\n\r\nforum<'),(5357,'p>\r\n\r\nhow'),(5353,'p>\r\n<'),(5340,'p>\r\nafter'),(5355,'p>\r\nanother'),(5683,'p>\r\naprendizaje'),(5654,'p>\r\ncomo'),(5673,'p>\r\ncomunidad'),(5629,'p>\r\nde'),(5645,'p>\r\ndominicano'),(5361,'p>\r\nefront'),(5501,'p>\r\nel'),(5686,'p>\r\nen'),(5635,'p>\r\nentorno'),(5661,'p>\r\nfilosóficos'),(5354,'p>\r\nfirst'),(5651,'p>\r\nhistoria'),(5348,'p>\r\nin'),(5537,'p>\r\nla'),(5639,'p>\r\npara'),(5666,'p>\r\npolìtica'),(5339,'p>\r\nresponsible'),(5347,'p>\r\n <'),(2070,'pacific'),(5122,'pack'),(3250,'package'),(3138,'padding'),(1188,'padifolia'),(2897,'page'),(2901,'page-turning'),(1199,'pages'),(5719,'pago'),(5458,'paidos<'),(5136,'painfully'),(4144,'painless'),(1230,'paint'),(1179,'painted'),(2156,'painting'),(317,'pair'),(13,'pairs'),(5519,'país'),(5527,'palabra'),(1623,'palace'),(1619,'palaces'),(2023,'palenqua'),(1962,'palenque'),(2014,'panama'),(5669,'papel'),(1180,'paper'),(5513,'para'),(1892,'paradigm'),(383,'paragraph'),(598,'parallel'),(3882,'paraphrase'),(1217,'paris'),(642,'part'),(5284,'part1'),(5331,'part2'),(4188,'participants'),(2006,'participated'),(4544,'participating\r\n			machines'),(5328,'participation'),(4043,'particular\r\n			product'),(1087,'particular'),(1973,'particularly'),(4949,'parties'),(300,'partly'),(1249,'parts'),(508,'parts:'),(4087,'party'),(85,'pass'),(979,'passages'),(980,'passes'),(123,'passing'),(3728,'passively'),(762,'past'),(3227,'patch'),(653,'path'),(3696,'patience'),(4811,'patient'),(1853,'patronage'),(3606,'patsies'),(3604,'patsy'),(2808,'pattern'),(3680,'patterns'),(4455,'paul'),(5566,'pautas'),(5396,'payments'),(4863,'payoff'),(417,'peak'),(1138,'peaking'),(14,'peaks'),(5445,'pedagogía'),(5455,'pedagogía?'),(1785,'peninsula'),(4783,'people\r\n			outside'),(3666,'people\r\n			you'),(773,'people'),(3774,'people<'),(1808,'peoples'),(5420,'pequeñas'),(5165,'perceive'),(5044,'perception\r\n			of'),(3480,'perception'),(2391,'perfect\r\n			understanding'),(2182,'perfect'),(875,'perfection'),(1573,'perfectly'),(5676,'perfil'),(3022,'perform'),(2450,'performance'),(1603,'performed'),(767,'performing'),(5104,'perhaps\r\n			because'),(2601,'perhaps\r\n			reanalyze'),(1611,'perhaps'),(3702,'period\r\n			of'),(1778,'period\r\n\r\n\r\n\r\n\r\nshortly'),(899,'period'),(5650,'periodo'),(895,'periods'),(3009,'peripheral\r\n			equipment'),(854,'permanence'),(847,'permanent'),(2197,'permanently'),(5577,'permita'),(319,'permutes'),(353,'permutes:'),(2750,'persist'),(1144,'persisted'),(3491,'person\r\n			and'),(778,'person'),(3593,'person;'),(3276,'personal'),(4166,'personality'),(4564,'personally\r\n			motivating'),(3598,'personally'),(4270,'persons'),(3362,'perspective'),(5468,'perteneciente'),(3258,'pertinent'),(1829,'peru'),(3352,'pester'),(3356,'pestered'),(1748,'peten'),(1754,'petén'),(4457,'pgsite'),(4312,'phase'),(1687,'phases'),(996,'phenomena'),(859,'philosophy'),(1062,'phonetic'),(4006,'phrase'),(2096,'physical'),(2951,'physically'),(3017,'physicists'),(4608,'physics---and'),(2632,'picked'),(787,'pictorial'),(371,'picture'),(2474,'piece'),(2955,'pieces'),(1948,'piedras'),(1983,'pilas'),(941,'pin-point'),(1655,'place'),(306,'placed'),(304,'places'),(5119,'plain'),(1431,'plains'),(4127,'plan\r\n			should'),(3063,'plan'),(803,'planes'),(1029,'planets'),(3055,'planning\r\n			the'),(1414,'planning'),(4038,'plans'),(1229,'plaster'),(5414,'plataforma'),(1606,'platform'),(1480,'platforms'),(1071,'play'),(4159,'player'),(4416,'playing'),(4417,'playtime'),(1455,'plazas'),(3109,'please'),(968,'pleiades'),(1547,'pliable'),(5091,'pocketbooks'),(2260,'point\r\n			in'),(61,'point'),(423,'pointer'),(190,'pointers'),(503,'points'),(502,'points:'),(5152,'pointy-haired'),(2281,'poke'),(1580,'poles'),(570,'policy'),(3228,'politely'),(5665,'política'),(5690,'polìtica'),(1385,'political'),(5641,'políticas'),(1811,'polities'),(1830,'polity'),(1820,'poor'),(2626,'poorly'),(1774,'popol'),(293,'popular'),(2113,'populated'),(1147,'population'),(2130,'populations'),(2718,'portability'),(4484,'portable'),(4487,'ported?'),(3920,'portion'),(2058,'portions'),(5565,'posee'),(4483,'poses'),(5525,'posible'),(3349,'position\r\n			can'),(2025,'position\r\n\r\nthe'),(316,'position'),(5401,'positions'),(381,'positive'),(5616,'positivista'),(5387,'possesses'),(490,'possibility'),(136,'possible'),(3931,'possibly'),(1111,'post'),(1461,'post-classic'),(2136,'post-conquest'),(1712,'postclassic'),(1283,'posterity'),(3231,'posting'),(4760,'postponing'),(5615,'postura'),(5581,'potencialidades'),(4819,'potency'),(615,'potential'),(1178,'pottery'),(743,'power'),(4368,'powerful'),(5597,'práctica'),(2264,'practical'),(3069,'practically'),(5602,'prácticas'),(4427,'practice\r\n			courage'),(2301,'practice'),(770,'practiced'),(3885,'practices'),(4776,'practicing'),(4978,'praise'),(4986,'praised'),(4979,'praiseworthy'),(1286,'prayer'),(1364,'pre-classic'),(1073,'pre-columbian'),(1109,'pre-maya'),(798,'pre-modern'),(933,'pre-telescopic'),(1097,'preceded'),(380,'precedence'),(3317,'precious\r\n			commodity'),(1817,'precious'),(2471,'precise'),(2490,'precisely'),(965,'preclassic'),(5160,'precludes'),(2080,'precursors'),(1469,'predetermined'),(3070,'predict'),(4547,'predictability\r\n			so'),(3061,'predictability'),(3427,'prefer'),(4814,'preparation'),(4037,'prepare'),(4798,'prepared\r\n			to'),(3708,'prepared'),(4260,'preparing'),(1783,'presence'),(5484,'presencia'),(695,'present'),(2027,'present-day'),(4356,'presented'),(3370,'president'),(3118,'pressure\r\n			permits'),(5093,'pressure\r\n			time-to-market'),(2334,'pressure'),(5270,'pressure<'),(1653,'presumably'),(689,'presuming'),(4582,'pretend'),(3578,'pretty'),(1446,'prevailed'),(5224,'preview'),(411,'previous'),(4481,'price\r\n			to'),(758,'priest'),(1209,'priests'),(217,'prim'),(1001,'primarily'),(1583,'primary'),(5434,'primera'),(496,'principal'),(5598,'principios'),(3675,'principles'),(2324,'printline'),(2249,'printlining'),(1516,'privacy'),(4176,'private'),(4981,'private;'),(1936,'privileged'),(3164,'probabilities'),(3173,'probability'),(2615,'probably\r\n			made'),(154,'probably'),(3558,'problem\r\n			magically'),(296,'problem\r\n			we'),(8,'problem'),(22,'problem:\r\n			\r\n			in'),(4901,'problems\r\n			it'),(2487,'problems\r\n			learning'),(4779,'problems\r\n			lie'),(2564,'problems\r\n			most'),(2484,'problems\r\n			that'),(117,'problems'),(4054,'problems:\r\n			\r\n			•'),(3742,'problems<'),(248,'procedure'),(2548,'proceed'),(5430,'procesos'),(290,'process'),(1182,'processed'),(5402,'processes'),(2941,'processing'),(2531,'processor'),(2687,'processors'),(3635,'procure'),(5108,'produce\r\n			software'),(260,'produce'),(264,'produced'),(369,'produces'),(5113,'producing\r\n			an'),(2431,'producing'),(340,'producing-examining'),(2658,'product'),(4372,'product-dependent'),(2445,'production'),(3221,'productive'),(4903,'productivity\r\n			of'),(4050,'productivity'),(4305,'products'),(5699,'profe'),(4341,'professional'),(3982,'professionally\r\n			assume'),(3981,'professionally'),(4644,'professionally<'),(5133,'professionals'),(5277,'professor'),(5325,'professors'),(5397,'profiles'),(2526,'profiling'),(613,'profit'),(679,'profit?\r\n			\r\n			an'),(4607,'profound'),(138,'program'),(4407,'program?'),(2370,'program?’'),(5416,'programador?'),(2158,'programmer'),(4800,'programmers\r\n			are'),(2823,'programmers\r\n			often'),(2179,'programmers'),(3019,'programming\r\n			can'),(3783,'programming\r\n			consists'),(4371,'programming\r\n			language'),(4321,'programming\r\n			languages'),(462,'programming\r\n			the'),(402,'programming'),(2203,'programs'),(5576,'progresivo'),(1288,'progress'),(4444,'progresses'),(3513,'progressive'),(3123,'progressively'),(4267,'project\r\n			plan'),(4891,'project\r\n			to'),(673,'project'),(5263,'project<'),(1913,'projecting'),(663,'projects'),(5235,'promethean'),(1269,'prominent'),(3093,'promise'),(4226,'promised'),(4091,'promises'),(4922,'promote'),(5038,'promoted'),(5037,'promotion\r\n			to'),(5036,'promotion'),(5268,'promotion<'),(3725,'prone'),(5063,'pronouncements'),(4696,'proof'),(515,'proofed'),(1676,'propaganda'),(258,'proper'),(1067,'properly'),(276,'properties'),(333,'property'),(760,'prophetic'),(5568,'propias'),(5517,'propios'),(5592,'propone'),(2942,'proportional'),(4786,'proposal\r\n			of'),(2650,'proposal\r\n			will'),(5067,'proposal'),(3899,'propose'),(3815,'proposed'),(5560,'propósito'),(3289,'pros'),(542,'prosorino'),(1814,'prospects'),(4897,'prototype'),(3117,'prototyping'),(1790,'prove'),(455,'proven'),(5544,'provenientes'),(4061,'proves'),(2612,'provide\r\n			a'),(1312,'provide'),(2439,'provided'),(2765,'provides'),(4546,'providing'),(5199,'psychological;'),(5200,'psychology'),(4777,'public\r\n			speaking'),(1454,'public'),(1103,'publication'),(4473,'publish'),(1123,'published'),(5547,'pueden'),(1538,'pulleys'),(793,'pure'),(3930,'purpose'),(5391,'pursuing'),(2734,'pushing'),(815,'putrefaction'),(1893,'puts'),(551,'putting'),(2060,'puuc'),(1398,'pyramid'),(1363,'pyramids'),(2940,'quadratic'),(2592,'quality'),(595,'quantities'),(591,'quantity'),(1550,'quarried'),(1545,'quarries'),(1565,'quarrying'),(5716,'quedes'),(305,'queen'),(295,'queens'),(4369,'query'),(464,'quest'),(177,'question'),(4036,'questions'),(2910,'queues'),(1765,'quexil'),(1770,'quiché'),(181,'quick'),(2340,'quickly'),(3392,'quietly'),(1316,'quills'),(2033,'quintana'),(3620,'quit'),(3814,'quite\r\n			low'),(1572,'quite'),(213,'quot;'),(1749,'quot;classic'),(1891,'quot;court'),(211,'quot;data'),(1953,'quot;death'),(1110,'quot;epi-olmec'),(1590,'quot;false'),(880,'quot;gods'),(848,'quot;good'),(764,'quot;heavens'),(1709,'quot;i'),(1322,'quot;land'),(1736,'quot;maya'),(437,'quot;minimum'),(1694,'quot;observatories'),(1275,'quot;our'),(639,'quot;pretty'),(522,'quot;principal'),(1789,'quot;the'),(1997,'quot;tree-stones'),(4869,'quote\r\n			in'),(2144,'rabinal'),(738,'rain'),(1441,'raise'),(3587,'raising'),(1706,'ramps'),(530,'random'),(4395,'rapid'),(1254,'rapidly'),(3716,'rare'),(1085,'rarely'),(5129,'rather\r\n			to'),(1594,'rather'),(3239,'ratio'),(1485,'re-built'),(3475,'re-test'),(657,'reach'),(806,'reached'),(4173,'react'),(1153,'read'),(3384,'read;'),(2717,'readability'),(2724,'readable'),(3448,'reader'),(1543,'readily'),(2701,'reading'),(707,'real'),(4268,'reality'),(4818,'realize'),(2935,'realized'),(2810,'reallocated'),(4703,'really\r\n			sure'),(3877,'really\r\n			there'),(2168,'really'),(1702,'realm'),(2489,'reason\r\n			that'),(265,'reason'),(3588,'reason---every'),(3701,'reasonable'),(1213,'reasonably'),(3709,'reasons\r\n			for'),(652,'reasons'),(3204,'reassignment'),(1382,'rebuilding'),(1381,'rebuilt'),(3310,'receiving'),(1259,'recent'),(2890,'recently'),(1105,'recently-discovered'),(4479,'recode'),(1360,'recognizable'),(3564,'recognize'),(3823,'recognized'),(3136,'recommend'),(2679,'recomputing'),(4162,'reconsidering'),(1752,'reconstituted'),(1296,'record'),(893,'recorded'),(1970,'recording'),(2429,'records'),(5029,'recover\r\n			from'),(1171,'recovered'),(1242,'recovery'),(3363,'recruit'),(3922,'recruitment'),(1227,'rectangular'),(842,'recur'),(2656,'recursive'),(2456,'redeploy'),(2640,'redesign'),(3489,'redesigned'),(1567,'reduced'),(4112,'reducing'),(2785,'reference\r\n			remains'),(1266,'reference'),(2784,'references'),(2786,'referent'),(5509,'referido'),(5605,'referirse'),(1626,'referred'),(2993,'referring'),(2760,'refers'),(2712,'reflect'),(5021,'reflected'),(5095,'reflects\r\n			a'),(5601,'reflexione'),(4126,'regained'),(5032,'regard'),(3461,'regardless'),(1762,'region'),(1873,'regional'),(1975,'regions'),(5613,'reglas'),(4206,'regular'),(843,'regularity'),(4743,'reinforces'),(338,'reject'),(249,'rejected'),(4796,'rejection'),(5586,'relación'),(5634,'relaciones'),(2848,'related'),(4567,'relating\r\n			each'),(2613,'relational'),(763,'relations'),(4720,'relationship'),(1116,'relationships'),(5046,'relative'),(2567,'relatively'),(4233,'release'),(4071,'release;'),(2566,'released'),(2449,'relevant'),(5649,'relevantes'),(4734,'reliably'),(1961,'relief'),(1646,'reliefs'),(735,'religion\r\n\r\n\r\n\r\n	\r\n		\r\n			'),(734,'religion'),(5674,'religión'),(4727,'religious\r\n			person'),(768,'religious'),(2811,'rely'),(3291,'remain\r\n			indecisive'),(524,'remain'),(1304,'remainder'),(1546,'remained'),(690,'remaining'),(1235,'remains'),(4259,'remember\r\n			to'),(2974,'remember'),(1406,'remnants'),(1380,'remodeled'),(2381,'remove'),(1552,'removed'),(552,'removing'),(2868,'reoccur\r\n			to'),(3872,'repeat'),(4930,'repeatable'),(4411,'replacement'),(921,'replicated'),(4888,'reply'),(4130,'reported\r\n			after'),(5323,'reporting'),(1826,'reports'),(214,'represent'),(2698,'representation'),(1681,'representations'),(881,'represented'),(612,'representing'),(272,'represents'),(2404,'reproduce'),(2447,'reproduced'),(4174,'reptilian\r\n			part'),(4739,'reputation'),(4503,'request'),(46,'requested'),(602,'require'),(166,'required'),(256,'requirement'),(325,'requirement:'),(352,'requirements'),(161,'requires'),(2405,'rerun'),(1104,'research'),(5074,'researchers\r\n			are'),(1113,'researchers'),(1060,'resemblance'),(3636,'reserving'),(5318,'reset'),(1632,'residence'),(4636,'resident'),(4245,'resides'),(1664,'residing'),(2861,'resign'),(4138,'resillient'),(3727,'resist'),(1806,'resistance'),(2947,'resolve'),(3690,'resolved\r\n			somehow'),(2516,'resource'),(1473,'resources'),(4406,'respect\r\n			for'),(3945,'respect\r\n			this'),(1877,'respect'),(5167,'respected'),(4158,'respectful'),(2102,'respectively'),(4951,'respects'),(3026,'response'),(3983,'responsibility'),(1771,'responsible'),(4577,'responsive'),(415,'rest'),(3097,'restate'),(3866,'restated'),(4056,'restrict'),(512,'restricted'),(4802,'rests'),(206,'result'),(3468,'resulting\r\n			document'),(3254,'results'),(4854,'resurrected'),(2600,'rethink'),(3943,'retract'),(477,'retroactive'),(238,'return'),(131,'returning'),(4052,'reuse'),(1301,'reveal'),(3932,'revealing'),(1004,'reveals'),(4179,'reversed'),(4215,'review'),(4217,'reviewed'),(3510,'revision'),(1733,'revolt'),(3486,'reworking'),(2426,'rewrite'),(4143,'rewrites'),(5196,'rewriting'),(3633,'rhythm'),(4329,'rich'),(1827,'riches'),(2945,'right\r\n			away'),(3903,'right'),(1492,'rigid'),(2809,'ring'),(1320,'rise'),(2918,'risk'),(4942,'riskiest'),(4219,'risks\r\n			a'),(3210,'risks'),(4659,'risks<'),(2354,'risky'),(1602,'rites'),(1282,'ritual'),(748,'rituals'),(1072,'role'),(1050,'roman'),(1670,'roof'),(1577,'roofs'),(3080,'room'),(1909,'rooms'),(271,'root'),(2807,'rotation'),(1167,'rough'),(497,'roughly'),(1691,'round'),(29,'route'),(434,'routes\r\n			the'),(11,'routes'),(1496,'royal'),(1907,'royalty'),(4723,'rude'),(2825,'rudimentary'),(1695,'ruin'),(2024,'ruins\r\n\r\n\r\n\r\n\r\n'),(1776,'ruins'),(84,'rule'),(3440,'rule;\r\n			•'),(1732,'ruled'),(1384,'ruler'),(1299,'rulers'),(3583,'rules'),(1730,'ruling'),(3791,'run-of-the-mill'),(2202,'running'),(1860,'saal'),(5610,'saber'),(1479,'sacbé'),(1477,'sacbeob'),(1514,'sacred'),(772,'sacrifice'),(4953,'sacrifices'),(791,'sacrificial'),(3674,'sacrificing\r\n			your'),(3444,'sacrificing'),(4992,'said'),(5022,'salaries'),(3059,'sales'),(109,'salesman'),(2018,'salt'),(2030,'salvador'),(318,'same'),(913,'santa'),(4782,'satisfaction'),(342,'satisfied'),(323,'satisfies'),(4524,'satisfy'),(351,'satisfying'),(3470,'save'),(4686,'saved'),(4015,'savvy'),(3612,'saying'),(498,'says'),(1704,'scale'),(3574,'scarcity'),(3846,'scared'),(2553,'schedule'),(4300,'scheduled\r\n			periodic'),(4295,'scheduled'),(796,'scholars'),(3951,'school'),(2975,'science'),(3004,'science’'),(3001,'science’---which'),(2401,'scientific\r\n			method'),(2979,'scientific'),(2751,'scope'),(5377,'scorm'),(5282,'scorm_2004_4ed_v1_1_tr_20090814'),(4885,'scratch'),(2231,'screen'),(1328,'scribes'),(1079,'script'),(2464,'scroll'),(3233,'search'),(3229,'searching'),(851,'season'),(827,'seasonal'),(4101,'seasoned'),(1046,'seasons'),(4752,'second\r\n			best'),(1460,'secondary'),(2532,'seconds'),(4690,'secret'),(4868,'section'),(3387,'security'),(2072,'sedentary'),(4273,'seduced'),(5380,'see:\r\n\r\n\r\nhow'),(4816,'seed'),(614,'seek'),(553,'seem'),(1486,'seemed'),(1529,'seemingly'),(1393,'seems'),(970,'seen'),(4438,'sees'),(1146,'segments'),(1816,'seizure'),(5313,'select'),(700,'selected'),(4214,'selection'),(5444,'selectos'),(200,'selects'),(3729,'self-assesment'),(3939,'self-deception'),(4683,'self-esteem'),(3436,'self-explanatory\r\n			code?'),(3422,'self-explanatory'),(1944,'self-proclaimed'),(3832,'sell'),(3975,'selling'),(1445,'semblance'),(2041,'semi-arid'),(3298,'semi-conscious'),(3176,'seminar?'),(3652,'send'),(585,'sense'),(4120,'sensibilities'),(3973,'sensible'),(4136,'sent'),(3531,'sentence'),(3087,'sentence:\r\n			\r\n			i'),(5504,'sentido'),(3421,'sentiment:\r\n			write'),(623,'separate'),(2385,'separated'),(4554,'separates'),(2306,'sequence'),(5727,'será'),(3046,'series'),(3523,'serious'),(5049,'seriously\r\n			unappreciated'),(2639,'seriously'),(4025,'serve'),(1674,'served'),(2538,'server'),(2894,'servers'),(3929,'serves'),(5164,'serving'),(428,'set:'),(683,'set:l='),(261,'sets'),(3264,'sets?’'),(4792,'setting'),(1954,'settlement'),(1923,'settlements'),(2313,'seven'),(1025,'several'),(1236,'severely'),(4198,'shame'),(334,'shape'),(1698,'shapes'),(3862,'share'),(2542,'shared'),(2115,'shares'),(1490,'sharply'),(1197,'sheet'),(1334,'shell'),(2019,'shells'),(3205,'shifting'),(1872,'shifts'),(4132,'shoddiness'),(3457,'shoes'),(3381,'short'),(4489,'shortand-\r\n			easily-ported'),(3857,'shorthand'),(1204,'shortly'),(635,'shortsighted'),(4995,'should\r\n			challenge'),(4378,'should\r\n			have'),(2789,'should\r\n			look'),(4271,'should\r\n			shift'),(3278,'should\r\n			start'),(676,'should'),(3602,'shoulder'),(407,'show'),(820,'showing'),(4970,'shown'),(3358,'showoff'),(62,'shows'),(1513,'shrines;'),(630,'shrinking'),(3294,'shut'),(3168,'sick'),(4302,'sickness'),(390,'side'),(386,'sides'),(5529,'siendo'),(2039,'sierra'),(1084,'sign'),(4717,'significance'),(957,'significant'),(1070,'signs'),(1819,'silver'),(1878,'similar'),(2535,'similarly'),(5499,'similitud'),(192,'simple'),(2299,'simple---any'),(3533,'simple:'),(2666,'simple;'),(4344,'simpler'),(3096,'simpleton'),(531,'simplicity'),(4958,'simply\r\n			disagree'),(479,'simply'),(4512,'simulate'),(4518,'simulations'),(178,'since'),(1195,'single'),(1478,'singular:'),(5496,'sino'),(5531,'sinónimos'),(5627,'sistema'),(5660,'sistemas'),(1368,'site'),(966,'sites'),(667,'situation'),(2525,'situations'),(2129,'sizable'),(475,'size'),(4523,'size---until'),(4231,'skeptical'),(1143,'skill'),(5409,'skill-gap'),(4940,'skills\r\n			are'),(4397,'skills\r\n			learning'),(2948,'skills\r\n			to'),(2643,'skills'),(3753,'skills<'),(1995,'slabs'),(5014,'slack'),(3396,'slacken'),(3148,'slacking'),(4135,'slashdot'),(3560,'sleep'),(4419,'sleeping'),(2195,'slightest\r\n			bump'),(3808,'slightly'),(4284,'slipped'),(4761,'slips'),(2497,'slow'),(2616,'slower'),(3521,'slows'),(577,'small'),(198,'smaller'),(2316,'smaller:'),(1710,'smallest'),(2968,'smart'),(4505,'smashing\r\n			success?'),(4704,'smile'),(4844,'smoothly'),(953,'smudge'),(4181,'so!’'),(4003,'so-called'),(5523,'sobre'),(3685,'social\r\n			pressure'),(1912,'social'),(5470,'sociales'),(5563,'sociedad'),(799,'societies'),(5636,'socioeconómico'),(2069,'soconusco'),(4111,'software\r\n			and'),(4081,'software\r\n			before'),(3261,'software\r\n			that'),(2186,'software'),(4094,'software?\r\n			\r\n			3'),(891,'solar'),(3259,'solid'),(5147,'solitary'),(988,'solstice'),(1642,'solstices'),(3443,'solution\r\n			faster;\r\n			•'),(21,'solution'),(151,'solutions'),(33,'solve'),(15,'solves'),(3584,'solving'),(2956,'some\r\n			books'),(2573,'some\r\n			other'),(2475,'some\r\n			variable'),(385,'some'),(3607,'somebody'),(3161,'somehow'),(152,'someone'),(3103,'something\r\n			big'),(3790,'something\r\n			other'),(2204,'something'),(452,'sometimes'),(1423,'somewhat'),(3464,'somewhere'),(5298,'soon'),(5106,'sooner'),(2110,'sophisticated'),(3677,'sort'),(3253,'sorting'),(622,'sought'),(4960,'sound'),(3972,'sounds'),(3305,'sour'),(1735,'source'),(1919,'sources'),(1768,'southern'),(2295,'space\r\n			debugging'),(4589,'space\r\n			you'),(1452,'space'),(3737,'space<'),(1901,'spaces'),(5123,'span'),(5338,'span><'),(1880,'spaniards'),(1149,'spanish'),(1357,'spans'),(4770,'speak'),(4606,'speaking'),(468,'special'),(1929,'specialization'),(1184,'species'),(1637,'specific'),(2084,'specifically'),(220,'specified'),(1356,'spectacular'),(4442,'speculate'),(4462,'speculative\r\n			code'),(4451,'speculative'),(4117,'speed'),(5253,'spend\r\n			much'),(2660,'spend'),(467,'spending'),(2502,'spent'),(4855,'spiral'),(4763,'spirit'),(2338,'split'),(2293,'splitting'),(1076,'spoken'),(5424,'sportscar'),(1435,'sprawling'),(1411,'spread'),(5028,'spreading'),(2943,'square\r\n			of'),(310,'square'),(4597,'squared'),(298,'squares'),(2257,'stable'),(3374,'staff'),(1613,'stake'),(3105,'stall'),(3113,'stalled'),(3110,'stalling'),(3847,'stand'),(4352,'standard'),(4324,'standardization'),(4373,'standardized'),(4354,'standards'),(3619,'stands'),(2225,'staring'),(962,'stars'),(2559,'start'),(361,'starting'),(3084,'startling'),(562,'starts'),(2551,'starvation\r\n			is'),(2545,'starvation'),(1841,'state'),(1274,'stated:\r\n\r\n\r\n\r\n'),(2479,'statement'),(2325,'statements'),(1767,'states'),(2448,'statistics'),(1635,'stature'),(3514,'stay'),(1176,'stelae'),(940,'stellar'),(169,'step'),(1362,'stepped'),(163,'steps'),(3178,'stick'),(320,'still'),(1173,'stone'),(1570,'stones'),(4691,'stop'),(4618,'store'),(492,'stored'),(3577,'stories'),(525,'storing'),(2050,'storms'),(4715,'story'),(3442,'straightforward'),(3345,'strange'),(1185,'strangler'),(4787,'strawman'),(4208,'strengths'),(3928,'stress'),(4701,'stressed-out'),(4165,'stressful'),(4937,'stretch'),(4998,'stretched'),(514,'strictly'),(3194,'strikes'),(2930,'stripper'),(3195,'strong'),(3209,'stronger'),(4452,'strongly\r\n			recommend'),(3324,'strongly'),(1554,'structural'),(212,'structure'),(2138,'structured'),(1839,'structures\r\n\r\n\r\n\r\n\r\na'),(1388,'structures'),(4361,'structuring'),(1562,'stucco'),(2909,'stuck'),(5317,'student'),(2933,'studied\r\n			it'),(3286,'studied'),(2949,'study'),(4349,'studying'),(2667,'stuff'),(4559,'stuff;'),(3556,'stumped\r\n			when'),(3554,'stumped'),(3770,'stumped<'),(3594,'stupid'),(2869,'stupidest'),(2973,'style'),(2982,'styles'),(5226,'styling'),(1404,'stylistic'),(509,'sub-problem'),(474,'sub-problems'),(2036,'sub-region'),(1482,'sub-structure'),(284,'sub-tree'),(3302,'subconscious'),(2360,'subdivided'),(3841,'subdivisions'),(1810,'subdue'),(1834,'subdued'),(435,'subgraph'),(3248,'subject\r\n			and'),(2977,'subject'),(3237,'subjective'),(1781,'subjugate'),(1882,'subordinated'),(2752,'subroutine'),(2125,'subsequent'),(1162,'subsequently'),(289,'subsets'),(1796,'substantive'),(2496,'subsystem'),(2625,'subsystems'),(3124,'subtasks'),(2261,'subtly'),(5228,'succeed'),(2961,'succeeded'),(1130,'succeeding'),(3987,'success'),(2314,'successfully'),(4736,'succinctness'),(3610,'succumb'),(142,'such'),(4536,'sufficient'),(931,'suggest'),(1376,'suggested'),(1126,'suggesting'),(2670,'suggestions:\r\n			\r\n			•'),(2076,'suggests'),(4241,'suitability'),(5562,'sujetos'),(3881,'summarize'),(2738,'summation'),(3346,'summer'),(518,'sums'),(1641,'sun’s'),(1059,'superficial'),(4899,'superior\r\n			to'),(1030,'superior'),(3337,'superiors'),(821,'supernatural'),(959,'support'),(4894,'supporters\r\n			for'),(942,'supports'),(3285,'suppose'),(459,'suppresses'),(2321,'sure'),(4228,'surest'),(2280,'surgery'),(1524,'surprising'),(1494,'surrounded'),(1678,'surrounding'),(4780,'survival'),(1218,'survive'),(1215,'survived'),(998,'surviving'),(2047,'susceptible'),(2854,'suspect'),(4694,'sustain\r\n			nothing'),(4693,'sustain'),(4060,'swapped'),(4971,'switch'),(3562,'switching'),(1069,'syllabic'),(412,'symbolism'),(874,'symbolized'),(1063,'symbols'),(224,'symmetrical'),(2550,'synchronization'),(2880,'synchronize'),(2543,'synchronized'),(3819,'synergize'),(3905,'synergizing'),(4051,'synergy'),(898,'synodic'),(4320,'syntactic'),(4379,'syntax'),(5236,'system\r\n			into'),(2866,'system\r\n			may'),(4815,'system\r\n			the'),(4495,'system\r\n			works'),(2761,'system\r\n			you'),(1014,'system'),(5261,'system<'),(521,'systematic'),(156,'systematically'),(1098,'systems'),(4057,'systems?\r\n			\r\n			•'),(2032,'tabasco'),(2782,'table'),(3736,'table>\r\n'),(4238,'tackled'),(3203,'tactical'),(2923,'tags'),(975,'takalik'),(3395,'take\r\n			to'),(633,'take'),(81,'taken'),(2529,'takes'),(4775,'taking'),(946,'tale'),(4990,'talent\r\n			nietschze'),(4989,'talent'),(5266,'talent<'),(3269,'talk\r\n			to'),(3273,'talk'),(3328,'talking\r\n			to'),(89,'talking'),(5524,'también'),(1234,'tantalizing'),(5697,'tarea'),(2882,'task'),(5183,'task;'),(4918,'tasks\r\n			sometimes'),(3842,'tasks'),(5264,'tasks<'),(3679,'taught'),(4733,'taxing'),(1753,'tayasal'),(3735,'tbody>\r\n<'),(3733,'td>\r\n		<'),(3367,'teach'),(4218,'team\r\n			members'),(4952,'team\r\n			spirit'),(3189,'team'),(3190,'team-wide'),(3710,'teammate'),(4943,'teammates\r\n			to'),(3522,'teammates'),(5265,'teammates<'),(3834,'teams'),(3941,'technical\r\n			skills'),(3348,'technical'),(4920,'technique\r\n			for'),(2305,'technique'),(1566,'techniques'),(5154,'technological\r\n			decision'),(4204,'technologies\r\n			and'),(1528,'technologies'),(3820,'technology\r\n			to'),(3787,'technology'),(3788,'technology?'),(963,'telescope'),(2907,'tell'),(4749,'telling'),(5443,'temas'),(1697,'temple'),(1379,'temples'),(2286,'temporarily'),(543,'temporary'),(3657,'tempted'),(4047,'tend'),(1416,'tended'),(3085,'tendency'),(471,'tends'),(5695,'tengo'),(1932,'tenochtitlan'),(2716,'tension'),(5596,'teórica'),(1491,'teotihuacan'),(442,'term'),(1788,'termed'),(426,'terminal'),(540,'terminally'),(236,'terminates'),(5535,'términos'),(2485,'terms'),(2056,'terrain'),(753,'terrestrial'),(3919,'terrible'),(1784,'territories'),(1321,'territory'),(3474,'test\r\n			burden'),(4494,'test\r\n			stress'),(3526,'test\r\n			unit'),(3532,'test\r\n			will'),(107,'test'),(3768,'test<'),(3530,'tested'),(4307,'testing\r\n			integration'),(2792,'testing'),(4661,'testing<'),(5394,'tests\r\n\r\n\r\n\r\n\r\n\r\n'),(5389,'tests'),(1996,'tetun'),(2924,'text'),(5479,'textos'),(788,'texts'),(1290,'thames'),(3687,'than\r\n			you'),(196,'than'),(2931,'thank'),(3488,'that\r\n			are'),(3966,'that\r\n			can'),(3275,'that\r\n			has'),(3988,'that\r\n			help'),(4627,'that\r\n			needs'),(2444,'that\r\n			occur'),(3581,'that\r\n			other'),(2252,'that\r\n			print'),(3235,'that\r\n			smacks'),(3147,'that\r\n			the'),(2704,'that\r\n			value'),(2397,'that\r\n			your'),(43,'that'),(510,'that:\r\n			\r\n			\r\n			minimum'),(4828,'that;'),(1582,'thatch'),(2994,'the\r\n			1960s'),(4781,'the\r\n			advanced'),(4338,'the\r\n			audience'),(4474,'the\r\n			benefit'),(5115,'the\r\n			best'),(4078,'the\r\n			biggest'),(2939,'the\r\n			code'),(2735,'the\r\n			computation'),(4168,'the\r\n			confidence'),(4316,'the\r\n			course'),(4154,'the\r\n			decision'),(4123,'the\r\n			decrease'),(2292,'the\r\n			delicate'),(2540,'the\r\n			end'),(4142,'the\r\n			eventual'),(3911,'the\r\n			idea'),(3357,'the\r\n			intern'),(3230,'the\r\n			internet'),(4859,'the\r\n			milestones'),(3570,'the\r\n			need'),(5161,'the\r\n			part'),(4682,'the\r\n			programmer'),(4116,'the\r\n			project'),(3016,'the\r\n			realm'),(3137,'the\r\n			results'),(5215,'the\r\n			risks'),(4282,'the\r\n			scheduled'),(2865,'the\r\n			solution'),(2350,'the\r\n			space'),(4264,'the\r\n			start'),(2590,'the\r\n			system'),(4954,'the\r\n			team'),(3398,'the\r\n			truth'),(4822,'the\r\n			uses'),(2847,'the\r\n			variability'),(4881,'the\r\n			voter'),(2262,'the\r\n			way'),(3682,'the\r\n			workplace'),(4210,'their\r\n			financial'),(3333,'their\r\n			position'),(4915,'their\r\n			strengths'),(3580,'their\r\n			teammates'),(253,'their'),(3888,'theirs'),(3368,'them\r\n			something'),(128,'them'),(5372,'theme'),(5370,'themes'),(1661,'themselves'),(4810,'then\r\n			patiently'),(67,'then'),(516,'theorem'),(3068,'theoretically'),(943,'theory'),(3835,'there\r\n			are'),(3586,'there\r\n			can'),(4969,'there\r\n			is'),(4499,'there\r\n			may'),(25,'there'),(1255,'thereafter'),(446,'therefore'),(4269,'these\r\n			decisions'),(4742,'these\r\n			myths'),(2355,'these\r\n			other'),(158,'these'),(345,'these:j-1'),(2270,'they\r\n			are'),(5109,'they\r\n			can'),(3186,'they\r\n			exist'),(3844,'they\r\n			may'),(3628,'they\r\n			work'),(444,'they'),(2318,'thing'),(2399,'things\r\n			that'),(2234,'things'),(2174,'think'),(3648,'thinking\r\n			suicidal'),(75,'thinking'),(3144,'thinks'),(2732,'third'),(2919,'third-party'),(3018,'thirty'),(2878,'this\r\n			case'),(4687,'this\r\n			for'),(2715,'this\r\n			is'),(5151,'this\r\n			issue'),(2467,'this\r\n			reason'),(5168,'this\r\n			the'),(7,'this'),(3614,'this:\r\n			\r\n			•'),(4040,'thorough'),(539,'those'),(638,'though'),(810,'thought'),(3649,'thoughts'),(1080,'thousand'),(1278,'thousands'),(2469,'thread'),(2900,'threads'),(2315,'three\r\n			are'),(3142,'three\r\n			days'),(4240,'three\r\n			products'),(801,'three'),(1908,'throne'),(69,'through'),(1189,'throughout'),(2196,'throw'),(3496,'throw-away'),(739,'thunder\r\n		\r\n	\r\n\r\nlike'),(4390,'tiemann2'),(5463,'tiene'),(5638,'tienen'),(2696,'tight\r\n			loop'),(4615,'tightly'),(1392,'tikal'),(4908,'timbre'),(4599,'time\r\n			against'),(3100,'time\r\n			estimation'),(4729,'time\r\n			on'),(3222,'time\r\n			the'),(4252,'time\r\n			to'),(71,'time'),(2568,'time-to-market'),(3170,'time;'),(3757,'time<'),(70,'times'),(3573,'timeto-\r\n			market'),(1276,'tiny'),(5039,'title'),(2727,'to\r\n			be'),(3893,'to\r\n			bring'),(3784,'to\r\n			build'),(3811,'to\r\n			consider'),(4002,'to\r\n			constantly'),(2534,'to\r\n			deal'),(5112,'to\r\n			do'),(5138,'to\r\n			everyone'),(4501,'to\r\n			figure'),(4721,'to\r\n			forget'),(4392,'to\r\n			listen'),(4857,'to\r\n			mark'),(3048,'to\r\n			provide'),(2166,'to\r\n			remove'),(3632,'to\r\n			satisfy'),(3425,'to\r\n			see'),(5176,'to\r\n			serve'),(4756,'to\r\n			slip'),(2506,'to\r\n			that'),(3976,'to\r\n			the'),(4172,'to\r\n			their'),(2927,'to\r\n			this'),(3025,'to\r\n			understand'),(2780,'to\r\n			use'),(4634,'to\r\n			using'),(3595,'to\r\n			work'),(3338,'to\r\n			you'),(4129,'to\r\n			your'),(5301,'to:\r\n\r\nadd'),(5349,'to:<'),(5580,'todas'),(2127,'today'),(2143,'today;'),(2333,'together'),(3355,'tolerated?'),(5502,'tomada'),(1660,'tombs'),(3780,'too\r\n			complicated'),(2339,'too\r\n			many'),(2461,'too\r\n			much'),(2946,'took\r\n			us'),(2891,'took'),(2247,'tool'),(1310,'tools\r\n\r\n\r\n\r\nalthough'),(1333,'tools'),(5335,'tools:\r\n\r\nforum\r\nchat\r\npersonal'),(5362,'tools:<'),(2998,'topic'),(632,'topical;'),(1426,'topography'),(1777,'topoxte\r\n\r\n\r\n\r\n\r\n'),(1669,'topped'),(781,'tore'),(3925,'torturous'),(678,'total'),(4381,'touch-type'),(1696,'tour-guides'),(1658,'toward'),(1652,'towering'),(1442,'towers'),(186,'town'),(1850,'towns'),(3734,'tr>\r\n	<'),(3493,'track'),(897,'tracked'),(2008,'trade'),(4611,'traded'),(4114,'tradeoff'),(5117,'tradeoffs'),(795,'tradition'),(949,'traditional'),(3575,'traditionally'),(2133,'traditions'),(5329,'traffic'),(3892,'trained'),(5309,'trainees'),(922,'training'),(846,'traits'),(697,'transfer'),(5403,'transferred'),(1115,'transitional'),(48,'transitive'),(3718,'transitory'),(132,'transportation'),(694,'transpositions'),(2858,'trap'),(1326,'trappings'),(5489,'trata'),(113,'traveling'),(108,'travelling'),(5310,'traverse'),(4827,'treating'),(705,'tree\r\n			let'),(270,'tree'),(1183,'tree-bark'),(564,'trees'),(3153,'tremendous'),(5234,'tremendously\r\n			powerful'),(2044,'tremendously'),(4244,'tribal\r\n			knowledge'),(4689,'tribal'),(3344,'tribe'),(2224,'trick'),(3971,'tried'),(4865,'triple'),(5043,'trite'),(3247,'trivial'),(983,'tropic'),(2049,'tropical'),(2343,'true'),(3143,'truly'),(3139,'trust'),(4571,'trusted\r\n			to'),(4570,'trusted'),(4670,'trusted<'),(4572,'trustworthy'),(3241,'truth'),(3041,'trying'),(1867,'tsuk'),(1369,'tunich'),(4005,'turn'),(2934,'turning'),(2898,'turns'),(986,'twice'),(1397,'twin'),(2598,'two-fold'),(1666,'two-hundred'),(4380,'type\r\n			learn'),(1675,'type'),(4364,'type-'),(4664,'type<'),(2678,'typecasts'),(4167,'types\r\n			this'),(884,'types'),(358,'typical'),(1193,'typically'),(5202,'tyranny'),(1612,'tzompantli'),(1649,'uaxactun'),(972,'ujuxte'),(5346,'ul>\r\n <'),(4858,'ultra-competitive'),(2454,'unanticipated\r\n			specific'),(2488,'unavoidable'),(839,'unbounded'),(1117,'unclear'),(3335,'uncomfortable'),(4072,'uncomfortably'),(5156,'unconventional'),(3967,'uncover'),(1345,'uncovered'),(4534,'under\r\n			heavy'),(4449,'under\r\n			pressure'),(82,'under'),(4871,'under-emphasize'),(4293,'underestimated'),(725,'undergraph'),(1755,'underlay'),(3466,'understand\r\n			it'),(3373,'understand\r\n			the'),(3852,'understand\r\n			why'),(2285,'understand'),(4435,'understandable'),(2277,'understandable---it'),(2685,'understanding\r\n			of'),(1407,'understanding'),(281,'understood'),(804,'underworld'),(2153,'unesco'),(4830,'unfinished'),(825,'unfolding'),(3567,'unfortunate'),(1155,'unfortunately'),(5100,'unhealthy'),(5446,'unidad'),(5512,'unidas'),(3629,'uninterrupted'),(1886,'unions'),(1355,'unique'),(4308,'unit\r\n			tested'),(5382,'unit\r\nhow'),(675,'unit'),(659,'units'),(5159,'unity'),(4553,'universal\r\n			but'),(3420,'universal'),(870,'universe'),(5482,'universitarios'),(1273,'university'),(3180,'unk-unks'),(2364,'unknown'),(3179,'unknowns'),(2588,'unless'),(4630,'unlike'),(3167,'unlikely'),(3571,'unloaded'),(2674,'unnecessarily'),(2617,'unnecessary'),(4073,'unofficial'),(4755,'unpleasant'),(2523,'unpredictable\r\n			circumstance'),(4514,'unpredictable'),(3559,'unravels'),(5135,'unrealistic'),(4042,'unreasonable'),(1119,'unsettled'),(4681,'unsettling'),(4724,'unsuccessful\r\n			to'),(454,'until'),(3411,'unto'),(2938,'unusual'),(4229,'unwise'),(4062,'unworkable'),(4290,'up\r\n			the'),(4254,'up-to-date'),(4277,'up-to-dateness'),(1964,'upakal'),(5373,'upload'),(1285,'upon'),(2803,'upper'),(3515,'upto-\r\n			date'),(1448,'urban'),(1969,'urbanism'),(3851,'us---technical'),(2574,'usability'),(4084,'usable'),(4083,'usage'),(210,'used'),(5251,'used;'),(4845,'useful\r\n			artifact'),(216,'useful'),(2757,'useless'),(5059,'user\r\n			is'),(5058,'user\r\n			it'),(2794,'user'),(5412,'user1'),(5269,'user<'),(4095,'user?\r\n			\r\n			4'),(4507,'users'),(3807,'uses'),(4516,'using\r\n			a'),(219,'using'),(4912,'usually\r\n			be'),(449,'usually'),(5476,'usualmente'),(1439,'usumacinta'),(2876,'utilization'),(3304,'utilize'),(1034,'utilized'),(1727,'uxmal'),(3129,'vacation'),(4303,'vacations'),(5216,'vague'),(56,'valid'),(98,'valid:\r\n			a'),(3342,'valuable\r\n			than'),(2521,'valuable'),(58,'value'),(1916,'values'),(4224,'vapor'),(4090,'vapor?'),(2853,'variability'),(4604,'variable\r\n			n'),(4465,'variable'),(53,'variables'),(1083,'variations'),(1412,'varied'),(4904,'varies'),(651,'variety'),(1346,'various'),(2043,'vary'),(1682,'vast'),(5450,'vázquez'),(900,'venus'),(2165,'verb'),(4790,'verbally'),(3024,'verify'),(4074,'version'),(3494,'versions'),(292,'very'),(2978,'vested'),(3369,'vice'),(792,'victims'),(2004,'victories'),(3906,'view'),(1013,'vigesimal'),(4924,'virtue'),(2194,'visibility'),(2230,'visible'),(3512,'visibly'),(2348,'vision'),(5644,'visión'),(121,'visit'),(191,'visited'),(141,'visiting'),(4334,'visual'),(4846,'visualize'),(4874,'vital'),(5530,'vocablos'),(3861,'vocabulary\r\n			create'),(5127,'volume'),(2867,'wait'),(4421,'waiting'),(1863,'wakab’nal'),(3456,'walked'),(4541,'wall\r\n			'),(1673,'wall'),(2517,'wall--clock'),(4537,'wall-clock\r\n			time'),(2520,'wall-clock'),(4632,'wall;'),(645,'walls'),(4747,'want\r\n			to'),(9,'want'),(5131,'want?’'),(5213,'wanted'),(5072,'wants'),(1870,'warfare'),(3625,'warmed-up'),(1298,'wars'),(18,'warshall'),(4190,'wasted'),(5099,'wasteful'),(3863,'wastes'),(3880,'watch\r\n			carefully'),(3955,'watch'),(5083,'watching'),(5124,'water'),(4997,'way\r\n			they'),(4610,'way!\r\n			\r\n			time'),(838,'ways'),(5048,'ways-\r\n			--after'),(4007,'ways:\r\n			\r\n			it'),(5190,'ways:\r\n			\r\n			•'),(4833,'we\r\n			have'),(3089,'we\r\n			will'),(3197,'weak'),(3207,'weaker\r\n			team'),(5027,'weaker'),(4209,'weaknesses'),(1815,'wealth'),(2913,'week'),(2892,'weeks'),(3321,'weighed\r\n			against'),(1593,'weighty'),(4357,'welcome'),(4511,'well\r\n			enough'),(4766,'well\r\n			to'),(389,'well'),(1947,'well-documented'),(4110,'well-established'),(4751,'well-informed'),(3806,'well-isolated'),(1980,'well-known'),(5015,'well-motivated\r\n			and'),(5008,'well-motivated'),(2954,'well-written'),(5260,'well<'),(1475,'wells'),(1140,'went'),(42,'were'),(916,'western'),(4412,'what\r\n			can'),(3133,'what\r\n			is'),(2336,'what\r\n			the'),(3954,'what\r\n			they'),(482,'what'),(2326,'whatever'),(2176,'whatnot'),(1535,'wheel-based'),(2227,'when\r\n			it'),(2952,'when\r\n			they'),(4327,'when\r\n			to'),(2850,'when\r\n			we'),(4616,'when\r\n			you'),(221,'when'),(185,'whenever'),(3950,'where\r\n			they'),(189,'where'),(684,'whereas'),(4849,'wherever\r\n			they'),(4895,'wherever'),(24,'whether'),(2396,'which\r\n			you'),(54,'which'),(134,'whichever'),(3956,'whiff'),(312,'while'),(2863,'whim'),(4788,'white-paper'),(4852,'whole\r\n			time;'),(283,'whole'),(3713,'whole-heartedly'),(4768,'whom'),(362,'whose'),(1561,'widely'),(1917,'wider'),(1340,'widespread'),(1200,'width'),(3529,'will\r\n			be'),(4765,'will\r\n			have'),(2468,'will\r\n			identify'),(2287,'will\r\n			make'),(2804,'will\r\n			need'),(5221,'will\r\n			often'),(4386,'will\r\n			probably'),(3341,'will\r\n			save'),(4764,'will\r\n			want'),(92,'will'),(819,'window'),(102,'winner'),(3364,'wisdom'),(2569,'wise'),(3380,'wisely\r\n			life'),(3379,'wisely'),(3761,'wisely<'),(4236,'wiser'),(4031,'wish'),(3086,'wishfully'),(1371,'witch'),(4085,'with\r\n			a'),(5163,'with\r\n			experience'),(3044,'with\r\n			others'),(2627,'with\r\n			respect'),(3937,'with\r\n			themselves'),(2458,'with\r\n			those'),(160,'with'),(4287,'with;'),(1164,'within'),(4902,'without\r\n			consideration'),(3503,'without\r\n			one'),(3694,'without\r\n			prejudice'),(5053,'without\r\n			visibility'),(466,'without'),(1968,'witnessed'),(1303,'women'),(2256,'wonderful'),(2773,'wonderful:'),(4441,'wonderfully\r\n			abstract'),(1579,'wooden'),(535,'word'),(4476,'wordiness'),(3848,'words'),(5177,'work\r\n			more'),(4875,'work\r\n			on'),(1261,'work'),(3160,'work---note'),(4067,'workarounds'),(1549,'worked'),(5205,'working\r\n			programmers'),(3993,'working\r\n			with'),(1021,'working'),(5020,'works\r\n			hard'),(2226,'works'),(1074,'world'),(830,'worlds'),(3548,'worry\r\n			about'),(2288,'worse'),(873,'worshiped'),(596,'worst'),(3825,'worth'),(171,'would'),(3502,'wouldn'),(1154,'write'),(3405,'writer'),(1056,'writing'),(1125,'writings'),(3435,'written\r\n			perfectly'),(1203,'written'),(205,'wrong'),(2216,'wrote'),(2852,'wyoming'),(5698,'xxxxxxx'),(5702,'xxxxxxx&lt;'),(1746,'yalain'),(1272,'yale'),(1396,'yaxhá'),(892,'year'),(1048,'years'),(1358,'years;'),(4157,'yes-man'),(2181,'you\r\n			are'),(4957,'you\r\n			believe'),(3873,'you\r\n			can'),(2662,'you\r\n			compute'),(2215,'you\r\n			create'),(2347,'you\r\n			don'),(2283,'you\r\n			have'),(4068,'you\r\n			make'),(3471,'you\r\n			rewrote'),(2307,'you\r\n			run'),(2409,'you\r\n			should'),(3707,'you\r\n			think'),(2498,'you\r\n			try'),(2371,'you\r\n			will'),(2491,'you\r\n			write'),(865,'young'),(3334,'your\r\n			boss'),(3214,'your\r\n			manager'),(2418,'your\r\n			own'),(2183,'your'),(3343,'yours'),(4938,'yourself\r\n			not'),(2649,'yourself'),(2367,'yourself:'),(1433,'yucatán'),(1738,'yucatec'),(5588,'y durkheim<'),(5460,'y γωγος<'),(1766,'zacpeten'),(1129,'zapotec'),(1101,'zapotecs'),(2100,'zapotec–speaking'),(1208,'zealous'),(978,'zenial'),(713,'zero'),(1988,'zone'),(2082,'zones'),(2054,'zones:'),(3451,'``bar'),(3538,'``did'),(3410,'``do'),(3534,'``does'),(3452,'``doit'),(3450,'``foo'),(5180,'``learning'),(4585,'``not'),(4454,'``succinctness'),(3391,'``yes'),(5625,' analizar'),(5646,' destacar'),(5675,' elaborar'),(5663,' establecer'),(5643,' presentar'),(5679,' sintetizar'),(5415,'¿cómo'),(5454,'¿qué'),(2392,'‘a-ha!’'),(5217,'‘build'),(3000,'‘computer'),(3260,'‘does'),(2376,'‘either'),(4180,'‘i\r\n			told'),(2243,'‘innards’'),(3003,'‘knife'),(5128,'‘no’'),(2903,'‘stuck’'),(3965,'‘take-home’'),(2928,'‘the'),(2849,'‘this'),(4500,'‘wall’'),(5130,'‘what'),(2363,'‘which'),(3845,'‘yes’'),(2320,'’\r\n			\r\n			can'),(2067,'“maya”');
/*!40000 ALTER TABLE `search_invertedindex` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_keywords`
--

DROP TABLE IF EXISTS `search_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_keywords` (
  `keyword` mediumint(8) unsigned DEFAULT NULL,
  `foreign_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `table_name` tinyint(1) NOT NULL,
  `position` tinyint(1) NOT NULL DEFAULT '1',
  KEY `keyword` (`keyword`),
  KEY `foreign_ID` (`foreign_ID`),
  KEY `table_name` (`table_name`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_keywords`
--

LOCK TABLES `search_keywords` WRITE;
/*!40000 ALTER TABLE `search_keywords` DISABLE KEYS */;
INSERT INTO `search_keywords` VALUES (5279,3,8,1),(5276,3,8,1),(5277,3,8,1),(5276,3,8,1),(5277,3,8,1),(5276,3,8,1),(5277,3,8,1),(5278,3,8,1),(2694,3,8,1),(5280,4,8,1),(5276,4,8,1),(5277,4,8,1),(5276,4,8,1),(5277,4,8,1),(5276,4,8,1),(5277,4,8,1),(5278,4,8,1),(2694,4,8,1),(5281,5,8,1),(5276,5,8,1),(5277,5,8,1),(5276,5,8,1),(5277,5,8,1),(5276,5,8,1),(5277,5,8,1),(5278,5,8,1),(2694,5,8,1),(5282,7,8,1),(5276,7,8,1),(5277,7,8,1),(5276,7,8,1),(5277,7,8,1),(5276,7,8,1),(5277,7,8,1),(5278,7,8,1),(2694,7,8,1),(5283,9,8,1),(5276,9,8,1),(5277,9,8,1),(5276,9,8,1),(5277,9,8,1),(5276,9,8,1),(5277,9,8,1),(5278,9,8,1),(2694,9,8,1),(5410,14,8,1),(1014,14,8,1),(5290,14,8,1),(1014,14,8,1),(5290,14,8,1),(1014,14,8,1),(5290,14,8,1),(5411,14,8,1),(2694,14,8,1),(5410,15,8,1),(1014,15,8,1),(5290,15,8,1),(1014,15,8,1),(5290,15,8,1),(1014,15,8,1),(5290,15,8,1),(5411,15,8,1),(2694,15,8,1),(5412,16,8,1),(1014,16,8,1),(5290,16,8,1),(1014,16,8,1),(5290,16,8,1),(1014,16,8,1),(5290,16,8,1),(5411,16,8,1),(2694,16,8,1),(5424,17,8,1),(1014,17,8,1),(5290,17,8,1),(1014,17,8,1),(5290,17,8,1),(1014,17,8,1),(5290,17,8,1),(5411,17,8,1),(2694,17,8,1),(5418,59,10,0),(5436,59,10,0),(5437,59,10,0),(5438,59,10,0),(5439,59,10,0),(5442,18,8,1),(1014,18,8,1),(5290,18,8,1),(1014,18,8,1),(5290,18,8,1),(1014,18,8,1),(5290,18,8,1),(5411,18,8,1),(2694,18,8,1),(5446,11,3,0),(5448,19,8,1),(5449,19,8,1),(5450,19,8,1),(5449,19,8,1),(5450,19,8,1),(5449,19,8,1),(5450,19,8,1),(5411,19,8,1),(2694,19,8,1),(5451,20,8,1),(1014,20,8,1),(5290,20,8,1),(1014,20,8,1),(5290,20,8,1),(1014,20,8,1),(5290,20,8,1),(5411,20,8,1),(2694,20,8,1),(5417,11,1,0),(5426,12,1,0),(5427,12,1,0),(5426,12,3,0),(5427,12,3,0),(5441,9,2,0),(105,9,2,0),(5443,10,2,0),(5444,10,2,0),(5445,10,2,0),(5456,1045,0,1),(5457,1045,0,1),(5458,1045,0,1),(5459,1045,0,1),(5460,1045,0,1),(5461,1045,0,1),(5462,1045,0,1),(5436,1045,0,1),(5463,1045,0,1),(5464,1045,0,1),(5465,1045,0,1),(5466,1045,0,1),(5467,1045,0,1),(5468,1045,0,1),(5469,1045,0,1),(5452,1045,0,1),(5470,1045,0,1),(5471,1045,0,1),(5472,1045,0,1),(496,1045,0,1),(5473,1045,0,1),(5474,1045,0,1),(5475,1045,0,1),(5476,1045,0,1),(5477,1045,0,1),(5478,1045,0,1),(5479,1045,0,1),(5480,1045,0,1),(5481,1045,0,1),(5482,1045,0,1),(5483,1045,0,1),(5484,1045,0,1),(5485,1045,0,1),(5486,1045,0,1),(5487,1045,0,1),(5488,1045,0,1),(5489,1045,0,1),(5490,1045,0,1),(5491,1045,0,1),(5492,1045,0,1),(5493,1045,0,1),(5494,1045,0,1),(5495,1045,0,1),(5496,1045,0,1),(5497,1045,0,1),(5498,1045,0,1),(5499,1045,0,1),(5500,1045,0,1),(5347,1045,0,1),(5501,1045,0,1),(5445,1045,0,1),(5453,1045,0,1),(5502,1045,0,1),(5503,1045,0,1),(5504,1045,0,1),(105,1045,0,1),(5505,1045,0,1),(5506,1045,0,1),(5507,1045,0,1),(5508,1045,0,1),(5509,1045,0,1),(5510,1045,0,1),(5511,1045,0,1),(5512,1045,0,1),(5513,1045,0,1),(5514,1045,0,1),(2153,1045,0,1),(5515,1045,0,1),(5516,1045,0,1),(5517,1045,0,1),(5518,1045,0,1),(5519,1045,0,1),(5520,1045,0,1),(5521,1045,0,1),(5522,1045,0,1),(5523,1045,0,1),(5524,1045,0,1),(5525,1045,0,1),(5526,1045,0,1),(5527,1045,0,1),(5528,1045,0,1),(5529,1045,0,1),(5530,1045,0,1),(5531,1045,0,1),(5532,1045,0,1),(5533,1045,0,1),(4793,1045,0,1),(5534,1045,0,1),(5535,1045,0,1),(5536,1045,0,1),(5537,1045,0,1),(5538,1045,0,1),(5539,1045,0,1),(5540,1045,0,1),(5541,1045,0,1),(5542,1045,0,1),(5543,1045,0,1),(5544,1045,0,1),(5545,1045,0,1),(5546,1045,0,1),(5547,1045,0,1),(5548,1045,0,1),(5549,1045,0,1),(5550,1045,0,1),(5551,1045,0,1),(5552,1045,0,1),(5553,1045,0,1),(5554,1045,0,1),(5555,1045,0,1),(5556,1045,0,1),(5557,1045,0,1),(5558,1045,0,1),(5559,1045,0,1),(5560,1045,0,1),(5561,1045,0,1),(5562,1045,0,1),(5563,1045,0,1),(5564,1045,0,1),(5565,1045,0,1),(5566,1045,0,1),(5567,1045,0,1),(5568,1045,0,1),(5569,1045,0,1),(5570,1045,0,1),(5571,1045,0,1),(5572,1045,0,1),(5573,1045,0,1),(5574,1045,0,1),(5575,1045,0,1),(1912,1045,0,1),(5576,1045,0,1),(5577,1045,0,1),(5578,1045,0,1),(5579,1045,0,1),(5580,1045,0,1),(5581,1045,0,1),(5582,1045,0,1),(5583,1045,0,1),(5584,1045,0,1),(5585,1045,0,1),(5586,1045,0,1),(5587,1045,0,1),(5588,1045,0,1),(5589,1045,0,1),(5590,1045,0,1),(5591,1045,0,1),(5592,1045,0,1),(5593,1045,0,1),(5594,1045,0,1),(5595,1045,0,1),(5596,1045,0,1),(5597,1045,0,1),(130,1045,0,1),(5598,1045,0,1),(5599,1045,0,1),(5600,1045,0,1),(5601,1045,0,1),(5602,1045,0,1),(5603,1045,0,1),(5604,1045,0,1),(5605,1045,0,1),(5606,1045,0,1),(5607,1045,0,1),(5608,1045,0,1),(5609,1045,0,1),(5610,1045,0,1),(5611,1045,0,1),(5612,1045,0,1),(5613,1045,0,1),(5614,1045,0,1),(5615,1045,0,1),(5616,1045,0,1),(5617,1045,0,1),(5618,1045,0,1),(5619,1045,0,1),(5620,1045,0,1),(5621,1045,0,1),(5454,1045,0,0),(5455,1045,0,0),(5622,1044,0,1),(5347,1044,0,1),(5623,1044,0,1),(5338,1044,0,1),(5624,1044,0,1),(5625,1044,0,1),(5626,1044,0,1),(5627,1044,0,1),(5628,1044,0,1),(5513,1044,0,1),(5583,1044,0,1),(5629,1044,0,1),(5630,1044,0,1),(5631,1044,0,1),(5632,1044,0,1),(5353,1044,0,1),(5633,1044,0,1),(5634,1044,0,1),(5542,1044,0,1),(5558,1044,0,1),(5635,1044,0,1),(5636,1044,0,1),(5637,1044,0,1),(5638,1044,0,1),(5639,1044,0,1),(5640,1044,0,1),(5641,1044,0,1),(5642,1044,0,1),(5643,1044,0,1),(5644,1044,0,1),(3183,1044,0,1),(5645,1044,0,1),(5646,1044,0,1),(5647,1044,0,1),(5648,1044,0,1),(5649,1044,0,1),(5518,1044,0,1),(5650,1044,0,1),(5651,1044,0,1),(5652,1044,0,1),(5653,1044,0,1),(5453,1044,0,1),(5654,1044,0,1),(5655,1044,0,1),(1912,1044,0,1),(5656,1044,0,1),(5657,1044,0,1),(5658,1044,0,1),(5659,1044,0,1),(5660,1044,0,1),(5661,1044,0,1),(5586,1044,0,1),(5662,1044,0,1),(5663,1044,0,1),(5664,1044,0,1),(5665,1044,0,1),(5666,1044,0,1),(5667,1044,0,1),(5668,1044,0,1),(5669,1044,0,1),(5670,1044,0,1),(5671,1044,0,1),(5672,1044,0,1),(5673,1044,0,1),(5674,1044,0,1),(5519,1044,0,1),(5563,1044,0,1),(105,1044,0,1),(5675,1044,0,1),(5676,1044,0,1),(5677,1044,0,1),(5678,1044,0,1),(5679,1044,0,1),(5680,1044,0,1),(5681,1044,0,1),(5682,1044,0,1),(5683,1044,0,1),(260,1044,0,1),(5684,1044,0,1),(5536,1044,0,1),(5685,1044,0,1),(5686,1044,0,1),(5687,1044,0,1),(5688,1044,0,1),(5464,1044,0,1),(5689,1044,0,1),(5690,1044,0,1),(5345,1044,0,1),(5346,1044,0,1),(5691,1044,0,1),(5692,1044,0,1),(5417,1044,0,0),(5426,1046,0,0),(5427,1046,0,0),(5693,21,8,1),(1014,21,8,1),(5290,21,8,1),(1014,21,8,1),(5290,21,8,1),(1014,21,8,1),(5290,21,8,1),(5411,21,8,1),(2694,21,8,1),(5694,22,8,1),(1014,22,8,1),(5290,22,8,1),(1014,22,8,1),(5290,22,8,1),(1014,22,8,1),(5290,22,8,1),(5411,22,8,1),(2694,22,8,1),(5693,23,8,1),(1014,23,8,1),(5290,23,8,1),(1014,23,8,1),(5290,23,8,1),(1014,23,8,1),(5290,23,8,1),(5411,23,8,1),(2694,23,8,1),(5694,24,8,1),(1014,24,8,1),(5290,24,8,1),(1014,24,8,1),(5290,24,8,1),(1014,24,8,1),(5290,24,8,1),(5411,24,8,1),(2694,24,8,1),(5694,25,8,1),(1014,25,8,1),(5290,25,8,1),(1014,25,8,1),(5290,25,8,1),(1014,25,8,1),(5290,25,8,1),(5411,25,8,1),(2694,25,8,1),(5695,1,6,1),(5696,1,6,1),(5523,1,6,1),(5697,1,6,1),(5698,1,6,1),(5699,1,6,0),(5695,2,6,1),(5696,2,6,1),(5523,2,6,1),(5697,2,6,1),(5698,2,6,1),(5699,2,6,0),(5695,3,6,1),(5696,3,6,1),(5523,3,6,1),(5697,3,6,1),(5698,3,6,1),(5699,3,6,0),(5695,4,6,1),(5696,4,6,1),(5523,4,6,1),(5697,4,6,1),(5698,4,6,1),(5699,4,6,0),(5700,5,6,1),(5701,5,6,1),(473,5,6,1),(5695,5,6,1),(5696,5,6,1),(5523,5,6,1),(5697,5,6,1),(5702,5,6,1),(5703,5,6,1),(5704,5,6,1),(5705,5,6,1),(5706,5,6,1),(5707,5,6,1),(5708,5,6,1),(5709,5,6,1),(5710,5,6,1),(5711,5,6,1),(5712,5,6,1),(5699,5,6,0),(5700,6,6,1),(5701,6,6,1),(473,6,6,1),(5695,6,6,1),(5696,6,6,1),(5523,6,6,1),(5697,6,6,1),(5702,6,6,1),(5703,6,6,1),(5704,6,6,1),(5705,6,6,1),(5706,6,6,1),(5707,6,6,1),(5708,6,6,1),(5709,6,6,1),(5710,6,6,1),(5711,6,6,1),(5712,6,6,1),(5699,6,6,0),(5451,26,8,1),(5449,26,8,1),(5450,26,8,1),(5449,26,8,1),(5450,26,8,1),(5449,26,8,1),(5450,26,8,1),(5411,26,8,1),(2694,26,8,1),(5451,27,8,1),(5449,27,8,1),(5450,27,8,1),(5449,27,8,1),(5450,27,8,1),(5449,27,8,1),(5450,27,8,1),(5411,27,8,1),(2694,27,8,1),(5700,7,6,1),(5701,7,6,1),(473,7,6,1),(5695,7,6,1),(5696,7,6,1),(5523,7,6,1),(5697,7,6,1),(5702,7,6,1),(5703,7,6,1),(5713,7,6,1),(5714,7,6,1),(5707,7,6,1),(5699,7,6,0),(5700,8,6,1),(5701,8,6,1),(473,8,6,1),(5695,8,6,1),(5696,8,6,1),(5523,8,6,1),(5697,8,6,1),(5702,8,6,1),(5703,8,6,1),(5713,8,6,1),(5714,8,6,1),(5707,8,6,1),(5699,8,6,0),(5417,11,2,0),(5418,11,2,0),(5417,13,3,0),(5418,13,3,0),(5715,14,3,0),(5715,14,1,0),(5417,13,1,0),(5418,13,1,0),(5448,28,8,1),(5449,28,8,1),(5450,28,8,1),(5449,28,8,1),(5450,28,8,1),(5449,28,8,1),(5450,28,8,1),(5411,28,8,1),(2694,28,8,1),(5451,29,8,1),(5449,29,8,1),(5450,29,8,1),(5449,29,8,1),(5450,29,8,1),(5449,29,8,1),(5450,29,8,1),(5411,29,8,1),(2694,29,8,1),(5451,30,8,1),(5449,30,8,1),(5450,30,8,1),(5449,30,8,1),(5450,30,8,1),(5449,30,8,1),(5450,30,8,1),(5411,30,8,1),(2694,30,8,1),(5716,1,7,0),(5717,1,7,0),(5410,1,7,1),(5718,1,7,1),(5719,1,7,1),(5720,1,7,1),(5513,1,7,1),(5721,1,7,1),(5714,1,7,1),(5722,1,7,1),(5411,1,7,1),(5723,1,7,1),(5434,1047,0,0),(5435,1047,0,0),(5694,31,8,1),(1014,31,8,1),(5290,31,8,1),(1014,31,8,1),(5290,31,8,1),(1014,31,8,1),(5290,31,8,1),(5411,31,8,1),(2694,31,8,1),(5694,32,8,1),(1014,32,8,1),(5290,32,8,1),(1014,32,8,1),(5290,32,8,1),(1014,32,8,1),(5290,32,8,1),(5411,32,8,1),(2694,32,8,1),(5694,33,8,1),(1014,33,8,1),(5290,33,8,1),(1014,33,8,1),(5290,33,8,1),(1014,33,8,1),(5290,33,8,1),(5411,33,8,1),(2694,33,8,1),(5724,1,5,0),(5724,1,4,0),(5725,1,5,1),(5726,1,5,1),(5727,1,5,1),(5728,1,5,1),(5446,1048,0,0),(5729,9,6,1),(5729,9,6,0),(5730,9,6,0),(5729,10,6,1),(5729,10,6,0),(5730,10,6,0),(5424,34,8,1),(1014,34,8,1),(5290,34,8,1),(1014,34,8,1),(5290,34,8,1),(1014,34,8,1),(5290,34,8,1),(5411,34,8,1),(2694,34,8,1),(5424,35,8,1),(1014,35,8,1),(5290,35,8,1),(1014,35,8,1),(5290,35,8,1),(1014,35,8,1),(5290,35,8,1),(5411,35,8,1),(2694,35,8,1),(5424,36,8,1),(1014,36,8,1),(5290,36,8,1),(1014,36,8,1),(5290,36,8,1),(1014,36,8,1),(5290,36,8,1),(5411,36,8,1),(2694,36,8,1),(5412,37,8,1),(1014,37,8,1),(5290,37,8,1),(1014,37,8,1),(5290,37,8,1),(1014,37,8,1),(5290,37,8,1),(5411,37,8,1),(2694,37,8,1),(5412,38,8,1),(1014,38,8,1),(5290,38,8,1),(1014,38,8,1),(5290,38,8,1),(1014,38,8,1),(5290,38,8,1),(5411,38,8,1),(2694,38,8,1),(5412,39,8,1),(1014,39,8,1),(5290,39,8,1),(1014,39,8,1),(5290,39,8,1),(1014,39,8,1),(5290,39,8,1),(5411,39,8,1),(2694,39,8,1),(5442,40,8,1),(1014,40,8,1),(5290,40,8,1),(1014,40,8,1),(5290,40,8,1),(1014,40,8,1),(5290,40,8,1),(5411,40,8,1),(2694,40,8,1),(5442,41,8,1),(1014,41,8,1),(5290,41,8,1),(1014,41,8,1),(5290,41,8,1),(1014,41,8,1),(5290,41,8,1),(5411,41,8,1),(2694,41,8,1),(5442,42,8,1),(1014,42,8,1),(5290,42,8,1),(1014,42,8,1),(5290,42,8,1),(1014,42,8,1),(5290,42,8,1),(5411,42,8,1),(2694,42,8,1),(5442,43,8,1),(1014,43,8,1),(5290,43,8,1),(1014,43,8,1),(5290,43,8,1),(1014,43,8,1),(5290,43,8,1),(5411,43,8,1),(2694,43,8,1);
/*!40000 ALTER TABLE `search_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sent_notifications`
--

DROP TABLE IF EXISTS `sent_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sent_notifications` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) NOT NULL,
  `recipient` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sent_notifications`
--

LOCK TABLES `sent_notifications` WRITE;
/*!40000 ALTER TABLE `sent_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `sent_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_questions_done`
--

DROP TABLE IF EXISTS `survey_questions_done`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_questions_done` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `surveys_ID` mediumint(8) unsigned NOT NULL,
  `question_ID` mediumint(8) unsigned NOT NULL,
  `user_answers` mediumtext NOT NULL,
  `submited` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_questions_done`
--

LOCK TABLES `survey_questions_done` WRITE;
/*!40000 ALTER TABLE `survey_questions_done` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_questions_done` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surveys`
--

DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surveys` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `survey_code` varchar(150) DEFAULT NULL,
  `survey_name` varchar(150) DEFAULT NULL,
  `survey_info` mediumtext,
  `author` varchar(100) DEFAULT NULL,
  `lang` varchar(50) DEFAULT NULL,
  `start_date` int(10) unsigned DEFAULT NULL,
  `end_date` int(10) unsigned DEFAULT NULL,
  `lessons_ID` mediumint(8) unsigned NOT NULL,
  `status` tinyint(1) DEFAULT '0',
  `start_text` mediumtext,
  `end_text` mediumtext,
  PRIMARY KEY (`id`),
  KEY `survey_code` (`survey_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surveys`
--

LOCK TABLES `surveys` WRITE;
/*!40000 ALTER TABLE `surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests`
--

DROP TABLE IF EXISTS `tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tests` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `content_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lessons_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `mastery_score` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `description` text,
  `options` text,
  `publish` tinyint(1) DEFAULT '1',
  `keep_best` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lessons_ID` (`lessons_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests`
--

LOCK TABLES `tests` WRITE;
/*!40000 ALTER TABLE `tests` DISABLE KEYS */;
INSERT INTO `tests` VALUES (9,1,1047,11,'Primera Evaluación',0,'<p>El objetivo es verificar los conocimientos de la Unidad 1</p>','a:22:{s:8:\"duration\";i:0;s:8:\"redoable\";i:1;s:8:\"onebyone\";i:0;s:12:\"only_forward\";i:0;s:13:\"given_answers\";i:0;s:10:\"show_score\";i:0;s:20:\"show_answers_if_pass\";i:0;s:16:\"maintain_history\";i:1;s:7:\"answers\";i:0;s:8:\"redirect\";i:1;s:15:\"shuffle_answers\";i:0;s:17:\"shuffle_questions\";i:0;s:10:\"pause_test\";i:0;s:12:\"display_list\";i:0;s:15:\"display_weights\";i:0;s:10:\"answer_all\";i:1;s:13:\"test_password\";s:0:\"\";s:10:\"redo_wrong\";i:0;s:17:\"general_threshold\";i:0;s:13:\"assign_to_new\";i:0;s:20:\"automatic_assignment\";i:0;s:15:\"student_results\";i:0;}',1,0);
/*!40000 ALTER TABLE `tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests_to_questions`
--

DROP TABLE IF EXISTS `tests_to_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tests_to_questions` (
  `tests_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `questions_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `weight` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `previous_question_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tests_ID`,`questions_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests_to_questions`
--

LOCK TABLES `tests_to_questions` WRITE;
/*!40000 ALTER TABLE `tests_to_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tests_to_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `themes`
--

DROP TABLE IF EXISTS `themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `themes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `description` text,
  `options` text,
  `layout` text,
  `path` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `themes`
--

LOCK TABLES `themes` WRITE;
/*!40000 ALTER TABLE `themes` DISABLE KEYS */;
INSERT INTO `themes` VALUES (1,'default','Default eFront Theme','eFront team','1.0','The default eFront theme','a:9:{s:11:\"show_header\";s:1:\"1\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"0\";s:13:\"sidebar_width\";s:3:\"175\";s:17:\"images_displaying\";s:1:\"0\";s:16:\"toolbar_position\";s:5:\"right\";s:6:\"locked\";s:1:\"0\";s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:5:\"three\";s:8:\"leftList\";a:2:{i:0;s:5:\"login\";i:1;s:6:\"online\";}s:10:\"centerList\";a:1:{i:0;s:7:\"lessons\";}s:9:\"rightList\";a:2:{i:0;s:4:\"news\";i:1;s:15:\"selectedLessons\";}}}','default/'),(2,'eFront2013','A minimal eFront theme','Athanasios Papagelis','1.0','A minimal eFront theme. This is the default theme for eFront 3.6.13','a:9:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:16:\"toolbar_position\";s:5:\"right\";s:6:\"locked\";s:1:\"0\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:5:\"three\";s:8:\"leftList\";a:2:{i:0;s:5:\"login\";i:1;s:6:\"online\";}s:10:\"centerList\";a:1:{i:0;s:7:\"lessons\";}s:9:\"rightList\";a:2:{i:0;s:4:\"news\";i:1;s:15:\"selectedLessons\";}}}','efront2013/'),(3,'IE6','IE6 theme','eFront team','1.0','\n		eFront IE6 theme\n	','a:7:{s:11:\"show_header\";s:1:\"1\";s:11:\"show_footer\";s:1:\"1\";s:13:\"sidebar_width\";s:3:\"175\";s:17:\"sidebar_interface\";s:1:\"0\";s:17:\"images_displaying\";s:1:\"0\";s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:5:\"three\";s:8:\"leftList\";a:2:{i:0;s:5:\"login\";i:1;s:6:\"online\";}s:10:\"centerList\";a:1:{i:0;s:7:\"lessons\";}s:9:\"rightList\";a:2:{i:0;s:4:\"news\";i:1;s:15:\"selectedLessons\";}}}','ie6/'),(4,'blue_html5','A simple blue theme with varying width','Athanasios Papagelis','1.0','A simple blue theme with varying width','a:9:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:16:\"toolbar_position\";s:5:\"right\";s:6:\"locked\";s:1:\"0\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:4:\"left\";s:8:\"leftList\";a:3:{i:0;s:5:\"login\";i:1;s:6:\"online\";i:2;s:15:\"selectedLessons\";}s:10:\"centerList\";a:2:{i:0;s:4:\"news\";i:1;s:7:\"lessons\";}s:9:\"rightList\";a:0:{}}}','blue_html5/'),(5,'modern','A modern eFront theme','Athanasios Papagelis','1.0','A modern eFront theme','a:9:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:16:\"toolbar_position\";s:5:\"right\";s:6:\"locked\";s:1:\"0\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:4:\"left\";s:8:\"leftList\";a:3:{i:0;s:5:\"login\";i:1;s:6:\"online\";i:2;s:15:\"selectedLessons\";}s:10:\"centerList\";a:2:{i:0;s:4:\"news\";i:1;s:7:\"lessons\";}s:9:\"rightList\";a:0:{}}}','modern/'),(6,'enterprise','Enterprise eFront Theme','eFront team','1.0','Enterprise eFront theme','a:9:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:16:\"toolbar_position\";s:5:\"right\";s:6:\"locked\";s:1:\"0\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:4:\"left\";s:8:\"leftList\";a:3:{i:0;s:5:\"login\";i:1;s:6:\"online\";i:2;s:15:\"selectedLessons\";}s:10:\"centerList\";a:2:{i:0;s:4:\"news\";i:1;s:7:\"lessons\";}s:9:\"rightList\";a:0:{}}}','enterprise/'),(7,'mobile','Mobile eFront Theme','eFront team','1.0','The mobile eFront theme','a:7:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:4:\"left\";s:8:\"leftList\";a:3:{i:0;s:5:\"login\";i:1;s:6:\"online\";i:2;s:15:\"selectedLessons\";}s:10:\"centerList\";a:2:{i:0;s:4:\"news\";i:1;s:7:\"lessons\";}s:9:\"rightList\";a:0:{}}}','mobile/'),(8,'green','Green theme','eFront team','1.0','\n		eFront green theme\n	','a:7:{s:11:\"show_header\";s:1:\"1\";s:11:\"show_footer\";s:1:\"1\";s:13:\"sidebar_width\";s:3:\"175\";s:17:\"sidebar_interface\";s:1:\"0\";s:17:\"images_displaying\";s:1:\"0\";s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:5:\"three\";s:8:\"leftList\";a:2:{i:0;s:5:\"login\";i:1;s:6:\"online\";}s:10:\"centerList\";a:1:{i:0;s:7:\"lessons\";}s:9:\"rightList\";a:2:{i:0;s:4:\"news\";i:1;s:15:\"selectedLessons\";}}}','green/'),(9,'blue','Blue eFront Theme','eFront team','1.0','Blue eFront theme','a:9:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:16:\"toolbar_position\";s:5:\"right\";s:6:\"locked\";s:1:\"0\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:4:\"left\";s:8:\"leftList\";a:3:{i:0;s:5:\"login\";i:1;s:6:\"online\";i:2;s:15:\"selectedLessons\";}s:10:\"centerList\";a:2:{i:0;s:4:\"news\";i:1;s:7:\"lessons\";}s:9:\"rightList\";a:0:{}}}','blue/'),(10,'modern_rtl','A modern eFront theme (RTL version)','Periklis Venakis','1.0','A modern eFront theme (RTL version)','a:9:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:16:\"toolbar_position\";s:4:\"left\";s:6:\"locked\";s:1:\"0\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:5:\"right\";s:9:\"rightList\";a:3:{i:0;s:5:\"login\";i:1;s:6:\"online\";i:2;s:15:\"selectedLessons\";}s:10:\"centerList\";a:2:{i:0;s:4:\"news\";i:1;s:7:\"lessons\";}s:8:\"leftList\";a:0:{}}}','modern_rtl/'),(11,'pad','A theme optimized for ipad-like devices','Athanasios Papagelis','1.0','A theme optimized for ipad-like devices','a:9:{s:11:\"show_header\";s:1:\"2\";s:11:\"show_footer\";s:1:\"1\";s:17:\"sidebar_interface\";s:1:\"2\";s:17:\"images_displaying\";s:1:\"2\";s:16:\"toolbar_position\";s:5:\"right\";s:6:\"locked\";s:1:\"0\";s:13:\"sidebar_width\";i:175;s:4:\"logo\";b:0;s:7:\"favicon\";b:0;}','a:1:{s:9:\"positions\";a:4:{s:6:\"layout\";s:4:\"left\";s:8:\"leftList\";a:3:{i:0;s:5:\"login\";i:1;s:6:\"online\";i:2;s:15:\"selectedLessons\";}s:10:\"centerList\";a:2:{i:0;s:4:\"news\";i:1;s:7:\"lessons\";}s:9:\"rightList\";a:0:{}}}','pad/');
/*!40000 ALTER TABLE `themes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tokens`
--

DROP TABLE IF EXISTS `tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokens` (
  `token` char(30) NOT NULL,
  `status` text NOT NULL,
  `users_LOGIN` varchar(100) DEFAULT NULL,
  `create_timestamp` int(10) unsigned NOT NULL,
  `expired` tinyint(1) NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tokens`
--

LOCK TABLES `tokens` WRITE;
/*!40000 ALTER TABLE `tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profile`
--

DROP TABLE IF EXISTS `user_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_profile` (
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `db_type` varchar(10) NOT NULL,
  `size` tinyint(3) unsigned DEFAULT '255',
  `type` varchar(10) DEFAULT NULL,
  `options` text,
  `default_value` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `languages_NAME` varchar(50) NOT NULL,
  `field_order` int(11) DEFAULT NULL,
  `rule` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profile`
--

LOCK TABLES `user_profile` WRITE;
/*!40000 ALTER TABLE `user_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_times`
--

DROP TABLE IF EXISTS `user_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_times` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `session_timestamp` int(10) unsigned NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `session_custom_identifier` char(40) NOT NULL DEFAULT '',
  `session_expired` tinyint(1) NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(100) NOT NULL,
  `timestamp_now` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `lessons_ID` mediumint(8) unsigned DEFAULT NULL,
  `courses_ID` mediumint(8) unsigned DEFAULT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` mediumint(8) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `users_LOGIN` (`users_LOGIN`),
  KEY `session_expired` (`session_expired`),
  KEY `entity` (`entity`),
  KEY `lessons_ID` (`lessons_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_times`
--

LOCK TABLES `user_times` WRITE;
/*!40000 ALTER TABLE `user_times` DISABLE KEYS */;
INSERT INTO `user_times` VALUES (1,1365429302,'8641qo59veud1jdu1slik8uie3','2d98d8a338b48d356b4aae28043332b9d6217924',1,'professor',1365429326,24,NULL,NULL,'system',0),(2,1365429740,'bbo6v0vdh2qmcfahphp08vgl25','5369d080862693f5598d6d07ec233a78e757c160',1,'student',1365429742,2,NULL,NULL,'system',0),(3,1365429742,'bbo6v0vdh2qmcfahphp08vgl25','5369d080862693f5598d6d07ec233a78e757c160',1,'student',1365429752,10,6,2,'lesson',6),(4,1365429752,'bbo6v0vdh2qmcfahphp08vgl25','5369d080862693f5598d6d07ec233a78e757c160',1,'student',1365429755,3,6,2,'unit',1005),(5,1365429760,'tp96t1v416jo618nv8en03b9h7','714a1f014d2441884de64025c9aaa2a8f69f6169',1,'admin',1365431120,1360,NULL,NULL,'system',0),(6,1387289515,'nppovd2ctvg7jpa44b7sj065m0','238ea429992b6cb1727106659d32f76262d43c13',1,'admin',1387289521,6,NULL,NULL,'system',0),(7,1387289746,'vcfuij0jlo37qseb28o91ahqi6','9708ee6ee67853140640bf0e97516fc838a15d79',1,'admin',1387289755,9,NULL,NULL,'system',0),(8,1387289821,'3r9sduigqpvi6gmiji1f3jnud1','bb225c9631b535667cb373b3318b78274248b439',1,'admin',1387289829,8,NULL,NULL,'system',0),(9,1396549675,'mthuksr87ni18etkghb8b11dq5','16fa84bc30f751c0a70c4824531b0cc11807fce6',1,'admin',1396551881,2206,NULL,NULL,'system',0),(10,1396552014,'ute1386rps0av1u467rgvbb447','50182838340754466a3f622ad66c3b9dd77ea005',1,'admin',1396552061,47,NULL,NULL,'system',0),(11,1396552112,'r7qno0thhnr12crvddion14c55','f3a2ef4f48c15358873c89523802de0d6c2568dc',1,'admin',1396552181,69,NULL,NULL,'system',0),(12,1396552191,'itn74fhec8a2383fc3fg1203g2','565f3cdcd70bedf0a99b5eaf4ab72fb5f7f37348',1,'admin',1396556110,3919,NULL,NULL,'system',0),(13,1396556161,'09bjs46dlu8qr4tp6o36ik33c2','e8174616578783d9c1ab9751d2eaf5edf8f4c704',1,'admin',1396557159,998,NULL,NULL,'system',0),(14,1396557191,'a5pfqou4nrh9q4p3up53bsobr5','10b294e94193fcd6547db519f2c1e98d4640544b',1,'admin',1396557937,746,NULL,NULL,'system',0),(15,1396557945,'hab7tgq7iovqp6athk9953p957','1b46f2fb1f34b3e01d6ababc97b39233608eef01',1,'jvazquez',1396558048,103,NULL,NULL,'system',0),(16,1396558060,'87od51v9n77fgvjg5uuv2cpoc3','d3672340b7b7c2878ddd3c885ba38f538b6d5873',1,'jvazquez',1396558649,589,NULL,NULL,'system',0),(17,1396558655,'kcds4023hks2ngeno5ac294pu7','6f484aded0232dd9d0f261305473321ebebe1b95',1,'admin',1396558921,266,NULL,NULL,'system',0),(18,1396558927,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396558934,7,NULL,NULL,'system',0),(19,1396558935,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559012,77,9,3,'lesson',9),(20,1396559013,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559018,5,9,3,'unit',1040),(21,1396559018,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559050,32,9,3,'lesson',9),(22,1396559050,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559056,6,9,3,'unit',1041),(23,1396559056,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559061,5,9,3,'unit',1040),(24,1396559062,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559140,78,9,3,'lesson',9),(25,1396559140,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559146,6,9,3,'unit',1042),(26,1396559146,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559149,3,9,3,'unit',1040),(27,1396559149,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559163,14,9,3,'lesson',9),(28,1396559164,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559171,7,9,3,'unit',1040),(29,1396559171,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559174,3,9,3,'unit',1041),(30,1396559175,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559263,88,9,3,'lesson',9),(31,1396559264,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559297,33,10,3,'lesson',10),(32,1396559298,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559299,1,9,3,'lesson',9),(33,1396559300,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559303,3,9,3,'unit',1040),(34,1396559306,'d0hqmp39qmsuhh17fk12eqif76','c464e0352e21492b0eb38eb53b39281140897942',1,'jvazquez',1396559539,233,9,3,'lesson',9),(35,1397101079,'tsdr3h9iqejfsg2uivab43ju47','4bc59c9f1c0f5fb4e3b971507709a06209b738b1',1,'cmonterrosa',1397101118,39,NULL,NULL,'system',0),(36,1397101129,'1dplef35ah12523u0qp5i3c8j6','0011f38499dc7f881b3d6b333cf0075c18813f7a',1,'jvazquez',1397101399,270,NULL,NULL,'system',0),(37,1397101626,'6utav9qqqmnck8sr766i7f39m1','d257cf8ee4f181b4941d8f4800027efd7e3a9717',1,'admin',1397102526,900,NULL,NULL,'system',0),(38,1397102614,'pis5q30qbmbgiorscmja52o4a5','0a96f00de5fba192c95a70110367724efb2cecf3',1,'admin',1397102911,297,NULL,NULL,'system',0),(39,1397102919,'enna4kk102malp9tdk2jpnv6a4','4b0ee689263480f4820b5e8ec6c356c62d814405',1,'mulloa',1397102980,61,NULL,NULL,'system',0),(40,1397102983,'al3kbe90t1epus998elcbmgca2','ac36255221204ffeb505da9c39ac1580857cb28c',1,'admin',1397103025,42,NULL,NULL,'system',0),(41,1397103040,'613m4abmhn8sk2fgbqrrk1md75','6f5407eeb8a8ca84ab53fea01f64ec54b90fc672',1,'mulloa',1397103707,667,NULL,NULL,'system',0),(42,1397103741,'0fh67suoju99lefphpr0tvn7c4','138ea5d94cbe60891c4802e966fa61e0c35f70bb',1,'jvazquez',1397103749,8,NULL,NULL,'system',0),(43,1397103749,'0fh67suoju99lefphpr0tvn7c4','138ea5d94cbe60891c4802e966fa61e0c35f70bb',1,'jvazquez',1397104130,381,11,7,'lesson',11),(44,1397104130,'0fh67suoju99lefphpr0tvn7c4','138ea5d94cbe60891c4802e966fa61e0c35f70bb',1,'jvazquez',1397104139,9,11,7,'unit',1044),(45,1397104139,'0fh67suoju99lefphpr0tvn7c4','138ea5d94cbe60891c4802e966fa61e0c35f70bb',1,'jvazquez',1397104142,3,11,7,'lesson',11),(46,1397104142,'0fh67suoju99lefphpr0tvn7c4','138ea5d94cbe60891c4802e966fa61e0c35f70bb',1,'jvazquez',1397104152,10,11,7,'unit',1044),(47,1397104153,'0fh67suoju99lefphpr0tvn7c4','138ea5d94cbe60891c4802e966fa61e0c35f70bb',1,'jvazquez',1397105108,955,11,7,'lesson',11),(48,1397105112,'1sjn3cp6945nj188v9h13jfdl2','827e1aa16da8138c966c5ced03f960539251329c',1,'admin',1397105227,115,NULL,NULL,'system',0),(49,1397105513,'i0v0o31absht0bghchq7bmgja2','60d9f268a3291dd28ec95fe53072fdd25a1cf666',1,'admin',1397105567,54,NULL,NULL,'system',0),(50,1397105962,'5juua1ob2lq7bok6md9sde13g1','cb55fdbaebb98f6e1fe4a9ab2499049ba3de4c73',1,'admin',1397106164,202,NULL,NULL,'system',0),(51,1397106203,'ud2ftmmgsv9m8c2k3tr9opvqk6','029b7843b9d10c677580a50460f93c86f71f796e',1,'admin',1397106532,329,NULL,NULL,'system',0),(52,1397106542,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106563,21,NULL,NULL,'system',0),(53,1397106564,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106571,7,11,9,'lesson',11),(54,1397106571,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106582,11,11,9,'unit',1044),(55,1397106582,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106629,47,11,9,'lesson',11),(56,1397106629,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106639,10,11,9,'unit',1045),(57,1397106640,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106653,13,11,9,'lesson',11),(58,1397106658,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106666,8,11,9,'unit',1045),(59,1397106666,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106668,2,11,9,'lesson',11),(60,1397106668,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106683,15,11,9,'unit',1044),(61,1397106683,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106684,1,11,9,'lesson',11),(62,1397106685,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106688,3,11,9,'unit',1044),(63,1397106688,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106696,8,11,9,'lesson',11),(64,1397106699,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106701,2,11,9,'unit',1044),(65,1397106702,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106705,3,11,9,'lesson',11),(66,1397106705,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106753,48,12,9,'lesson',12),(67,1397106754,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106764,10,12,9,'unit',1046),(68,1397106764,'vfn6pbsajlgkru7n2sd49r82a3','13236acd56df78b40cd2daba0058940d5d99c1bb',1,'jvazquez',1397106801,37,12,9,'lesson',12),(69,1397106822,'bq9uurembuq0a0ecdur0j59895','126770e0b68b14a4d336e451af4c0d48d165694c',1,'admin',1397106924,102,NULL,NULL,'system',0),(70,1397106942,'3iu3g0kjatfrau25ae7o9o6go0','7920122779dd405069da7cbb87a0c1384d2172ab',1,'admin',1397107031,89,NULL,NULL,'system',0),(71,1397107058,'oequsrdqj33hp7euo18p7000d3','eeba1a9f78e3f62c05f28c9707a4f1a15316c4e1',1,'admin',1397107200,142,NULL,NULL,'system',0),(72,1397107209,'2hgohdqgggaiitmpgl88bjous5','5dd5c2738ffbe35672a0951f3dfef2dff52397bc',1,'cmonterrosa',1397107236,27,NULL,NULL,'system',0),(80,1397107381,'dk2n1vvgetjtmoijg7najkikr7','6bae6a523de73e8845634bb75f3d0d430b925174',1,'jvazquez',1397107534,153,NULL,NULL,'system',0),(81,1397107534,'dk2n1vvgetjtmoijg7najkikr7','6bae6a523de73e8845634bb75f3d0d430b925174',1,'jvazquez',1397107535,1,11,9,'lesson',11),(82,1397107536,'dk2n1vvgetjtmoijg7najkikr7','6bae6a523de73e8845634bb75f3d0d430b925174',1,'jvazquez',1397107548,12,11,9,'unit',1044),(83,1397107549,'dk2n1vvgetjtmoijg7najkikr7','6bae6a523de73e8845634bb75f3d0d430b925174',1,'jvazquez',1397107563,14,11,9,'lesson',11),(84,1397107574,'vnsibl1em7f2bi2772ruism340','ebf3dfd2adc6bbc8789e2dee1e5e40ad74939339',1,'cmonterrosa',1397107696,122,NULL,NULL,'system',0),(85,1397107699,'4f6dtp4o96p406nvhtbpv87gd5','58dbd478ec95ad2af288dd4db3208de9e5323d25',1,'admin',1397108391,692,NULL,NULL,'system',0),(86,1397108438,'cd0sbrkhktic0151ckf8pum8j6','06c56c3d255ce182b29a9d8c720fc0f662ec8e62',1,'jvazquez',1397108525,87,NULL,NULL,'system',0),(87,1397108536,'peo6q47atofqsg2i9ab5far6u5','53a8bb0f859fe529bad8c06394b7cf512e42c89f',1,'admin',1397109022,486,NULL,NULL,'system',0),(88,1397109059,'4fbll1uq7rbfsvdhkhbkproqi1','73fac9f7633a0c788438f3554d36a664927ce757',1,'admin',1397109499,440,NULL,NULL,'system',0),(89,1397109532,'r1cjnu8djpm6hi52kh4h9rp7b2','8b4cf5263f47f8e26995c073bd18bba187f93448',1,'cmonterrosa',1397109591,59,NULL,NULL,'system',0),(93,1397109686,'erg776hhnigogjf1810es0trn6','020b1f4e4cf3eb7fc39159fc7e696787b4c978a3',1,'jvazquez',1397109767,81,NULL,NULL,'system',0),(94,1397109767,'erg776hhnigogjf1810es0trn6','020b1f4e4cf3eb7fc39159fc7e696787b4c978a3',1,'jvazquez',1397109792,25,11,9,'lesson',11),(95,1397109799,'b2ps8dftsng9glcsl8k6oml317','2dd31a83ff3be13d55ae364ddbcaf8409d3e19bb',1,'admin',1397110002,203,NULL,NULL,'system',0),(96,1397110022,'o8j90sgokamlleadd0nddr49t0','313a52927cecebdb1399076195f35b1d11652088',1,'jvazquez',1397110029,7,NULL,NULL,'system',0),(97,1397110029,'o8j90sgokamlleadd0nddr49t0','313a52927cecebdb1399076195f35b1d11652088',1,'jvazquez',1397110411,382,11,9,'lesson',11),(98,1397110445,'vhvgjnp2inju2qpo7i5kd4gj13','26ebba08eb3ed61765d645bb1f29e0ebd6b92a76',1,'admin',1397110614,169,NULL,NULL,'system',0),(99,1397151661,'aq0u2os6raqqmiocgn7b2gm8o6','4cf27a9b08f8630e864bf98d3236cff7de5f96f1',1,'mulloa',1397151785,124,NULL,NULL,'system',0),(100,1397152002,'mso44eo0cb9o87tvjdv3r09jo1','166baa8a2a701c8f7499e0a5863ed9ae802cdaeb',1,'mulloa',1397152396,394,NULL,NULL,'system',0),(101,1397152426,'65q0o83999hbl7kmmokhk99ag2','c2b085cb91917b5079388522d9ffbb0f26a96323',1,'jvazquez',1397152476,50,NULL,NULL,'system',0),(102,1397152476,'65q0o83999hbl7kmmokhk99ag2','c2b085cb91917b5079388522d9ffbb0f26a96323',1,'jvazquez',1397152492,16,11,9,'lesson',11),(103,1397152493,'65q0o83999hbl7kmmokhk99ag2','c2b085cb91917b5079388522d9ffbb0f26a96323',1,'jvazquez',1397152501,8,11,9,'unit',1044),(104,1397152501,'65q0o83999hbl7kmmokhk99ag2','c2b085cb91917b5079388522d9ffbb0f26a96323',1,'jvazquez',1397152504,3,11,9,'unit',1047),(105,1397152504,'65q0o83999hbl7kmmokhk99ag2','c2b085cb91917b5079388522d9ffbb0f26a96323',1,'jvazquez',1397152555,51,11,9,'lesson',11),(106,1397152555,'65q0o83999hbl7kmmokhk99ag2','c2b085cb91917b5079388522d9ffbb0f26a96323',1,'jvazquez',1397152634,79,11,9,'unit',1048),(107,1397152634,'65q0o83999hbl7kmmokhk99ag2','c2b085cb91917b5079388522d9ffbb0f26a96323',1,'jvazquez',1397152651,17,11,9,'lesson',11),(108,1397152666,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397152687,21,NULL,NULL,'system',0),(109,1397152687,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397152690,3,11,9,'lesson',11),(110,1397152690,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397152707,17,11,9,'unit',1044),(111,1397152707,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397152730,23,11,9,'unit',1047),(112,1397152730,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397152770,40,11,9,'unit',1044),(113,1397152770,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397152777,7,11,9,'lesson',11),(114,1397152777,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397153372,595,12,9,'lesson',12),(115,1397153372,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397154648,1276,12,9,'unit',1046),(116,1397154648,'s059f6210enp9b991gupb7u7n5','ea7340f54280e394f3f9dde08c775628016e03e8',1,'cmonterrosa',1397154848,200,12,9,'lesson',12),(117,1398358658,'9qg4apmc7vgpr55o0k328ke373','57d175cade968e43f9f3c8a7c0522f863cec5db3',1,'admin',1398359173,515,NULL,NULL,'system',0),(118,1398359237,'a3bmuapd8f0d72o1ppnnmuao14','d381b7c5fec07d4f37f4ad223301b7b3b5b96716',1,'jvazquez',1398359245,8,NULL,NULL,'system',0),(119,1398359301,'qrleqmv8g7jo78a9f39dophlh4','28b47341b85c9b78f1cce622eddb874a7e2f93bb',1,'admin',1398360071,770,NULL,NULL,'system',0),(120,1398360092,'35thf5i0jq55ghm85a4btrgcr2','9c66a2c0db8ea23304f4694a1ed723ccb4141b1c',1,'mulloa',1398360861,769,NULL,NULL,'system',0),(121,1398360916,'salbd8f8mvkbd97thb479qfcm3','5b3e34bb75a5a2d64e38ad9b0c65a50bca571e27',1,'mulloa',1398416375,55459,NULL,NULL,'system',0),(122,1398360956,'a1fn8la8ros37s3edidjvkene0','83c2d59bad0fe04d494115615132521ad8e4db87',1,'jvazquez',1398361014,58,NULL,NULL,'system',0),(123,1398361014,'a1fn8la8ros37s3edidjvkene0','83c2d59bad0fe04d494115615132521ad8e4db87',1,'jvazquez',1398368850,7836,11,9,'lesson',11),(124,1398439480,'n4auil2jks6to461hb9uqj0v25','99eb786c055470e8ac92c99e1098359e03e88b45',1,'mulloa',1398443046,3566,NULL,NULL,'system',0),(125,1398536405,'vnaq6e3mdjri2cuo9bt480khj4','353488107e0cdf3b82f91719a7427a97eb3960cb',1,'admin',1398536439,34,NULL,NULL,'system',0);
/*!40000 ALTER TABLE `user_times` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_types`
--

DROP TABLE IF EXISTS `user_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_types` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `basic_user_type` varchar(50) NOT NULL,
  `core_access` text,
  `modules_access` text,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_types`
--

LOCK TABLES `user_types` WRITE;
/*!40000 ALTER TABLE `user_types` DISABLE KEYS */;
INSERT INTO `user_types` VALUES (1,'Director','administrator','a:23:{s:7:\"lessons\";s:6:\"change\";s:5:\"users\";s:6:\"change\";s:13:\"configuration\";s:6:\"hidden\";s:6:\"themes\";s:6:\"hidden\";s:11:\"logout_user\";s:6:\"change\";s:12:\"user_profile\";s:6:\"change\";s:10:\"user_types\";s:6:\"hidden\";s:6:\"groups\";s:6:\"change\";s:9:\"languages\";s:6:\"hidden\";s:11:\"version_key\";s:6:\"hidden\";s:11:\"maintenance\";s:6:\"hidden\";s:6:\"backup\";s:6:\"hidden\";s:7:\"modules\";s:6:\"hidden\";s:13:\"module_itself\";s:6:\"hidden\";s:10:\"statistics\";s:4:\"view\";s:7:\"archive\";s:6:\"change\";s:17:\"personal_messages\";s:6:\"change\";s:13:\"notifications\";s:6:\"change\";s:13:\"control_panel\";s:6:\"change\";s:9:\"dashboard\";s:6:\"change\";s:8:\"calendar\";s:6:\"change\";s:4:\"news\";s:6:\"change\";s:5:\"forum\";s:6:\"change\";}',NULL,1),(2,'Coordinador','administrator','a:23:{s:7:\"lessons\";s:6:\"change\";s:5:\"users\";s:6:\"change\";s:13:\"configuration\";s:6:\"hidden\";s:6:\"themes\";s:6:\"hidden\";s:11:\"logout_user\";s:6:\"change\";s:12:\"user_profile\";s:4:\"view\";s:10:\"user_types\";s:6:\"hidden\";s:6:\"groups\";s:6:\"hidden\";s:9:\"languages\";s:6:\"hidden\";s:11:\"version_key\";s:6:\"hidden\";s:11:\"maintenance\";s:6:\"hidden\";s:6:\"backup\";s:6:\"hidden\";s:7:\"modules\";s:6:\"hidden\";s:13:\"module_itself\";s:6:\"hidden\";s:10:\"statistics\";s:6:\"change\";s:7:\"archive\";s:6:\"change\";s:17:\"personal_messages\";s:6:\"change\";s:13:\"notifications\";s:6:\"change\";s:13:\"control_panel\";s:6:\"change\";s:9:\"dashboard\";s:6:\"change\";s:8:\"calendar\";s:6:\"change\";s:4:\"news\";s:4:\"view\";s:5:\"forum\";s:6:\"change\";}',NULL,1);
/*!40000 ALTER TABLE `user_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL,
  `password` char(32) NOT NULL,
  `email` varchar(150) NOT NULL,
  `languages_NAME` varchar(50) NOT NULL,
  `timezone` varchar(100) DEFAULT '',
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `comments` text,
  `user_type` varchar(50) NOT NULL DEFAULT 'student',
  `timestamp` int(10) unsigned NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `pending` tinyint(1) NOT NULL DEFAULT '0',
  `user_types_ID` mediumint(8) DEFAULT '0',
  `additional_accounts` text,
  `viewed_license` tinyint(1) DEFAULT '0',
  `status` varchar(255) DEFAULT '',
  `short_description` text,
  `balance` float DEFAULT '0',
  `archive` int(10) unsigned DEFAULT '0',
  `dashboard_positions` text,
  `need_mod_init` tinyint(1) DEFAULT '0',
  `autologin` char(32) DEFAULT NULL,
  `need_pwd_change` tinyint(1) DEFAULT '0',
  `last_login` int(10) unsigned NOT NULL DEFAULT '0',
  `simple_mode` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`login`),
  KEY `id` (`id`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','f8816e64886c2ce31ced6d0e6fc9c8e4','cmonterrosa@gmail.com','english','Europe/Helsinki','System','Administrator',1,NULL,'administrator',1365149958,NULL,0,0,'a:2:{i:0;s:7:\"student\";i:1;s:9:\"professor\";}',0,'',NULL,0,0,NULL,0,NULL,0,1398536405,0),(4,'cmonterrosa','f8816e64886c2ce31ced6d0e6fc9c8e4','elreyazucar@gmail.com','spanish','America/Mexico_City','Carlos ','Monterrosa',1,'','student',1396552326,'39',0,0,NULL,0,'','',0,0,NULL,0,NULL,0,1397152666,0),(5,'jvazquez','f8816e64886c2ce31ced6d0e6fc9c8e4','iscvazquez@gmail.com','spanish','America/Mexico_City','Jesus','Vázquez',1,'','professor',1396557923,'36',0,0,NULL,0,'','',0,0,NULL,0,NULL,0,1398360956,0),(6,'mulloa','f8816e64886c2ce31ced6d0e6fc9c8e4','iscvazquez@gmail.com','spanish','America/Mexico_City','Moisés','Ulloa',1,'','administrator',1397102904,'43',0,1,NULL,0,'','<p>Soy director de la institución</p>',0,0,NULL,0,NULL,0,1398439480,0),(2,'professor','f8816e64886c2ce31ced6d0e6fc9c8e4','cmonterrosa@gmail.com','english','Europe/Helsinki','Default','Professor',1,NULL,'professor',1365149958,NULL,0,0,'a:2:{i:0;s:5:\"admin\";i:1;s:7:\"student\";}',0,'',NULL,0,0,NULL,0,NULL,0,0,0),(3,'student','f8816e64886c2ce31ced6d0e6fc9c8e4','cmonterrosa@gmail.com','english','Europe/Helsinki','Default','Student',1,NULL,'student',1365149958,NULL,0,0,'a:2:{i:0;s:5:\"admin\";i:1;s:9:\"professor\";}',0,'',NULL,0,0,NULL,0,NULL,0,0,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_content`
--

DROP TABLE IF EXISTS `users_to_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_content` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `users_LOGIN` varchar(100) NOT NULL,
  `content_ID` mediumint(8) unsigned NOT NULL,
  `lessons_ID` mediumint(8) unsigned NOT NULL,
  `success_status` varchar(15) DEFAULT 'unknown',
  `timestamp` int(10) unsigned DEFAULT NULL,
  `score` float DEFAULT '0',
  `entry` varchar(15) DEFAULT '',
  `total_time` int(10) unsigned NOT NULL DEFAULT '0',
  `suspend_data` longtext,
  `archive` tinyint(1) NOT NULL DEFAULT '0',
  `time_start` int(10) unsigned DEFAULT NULL,
  `time_end` int(10) unsigned DEFAULT NULL,
  `pending` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_LOGIN` (`users_LOGIN`,`content_ID`,`lessons_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_content`
--

LOCK TABLES `users_to_content` WRITE;
/*!40000 ALTER TABLE `users_to_content` DISABLE KEYS */;
INSERT INTO `users_to_content` VALUES (13,'cmonterrosa',1044,11,'unknown',NULL,0,'',55,NULL,0,NULL,NULL,0),(17,'cmonterrosa',1046,12,'unknown',NULL,0,'',205,NULL,0,NULL,NULL,0);
/*!40000 ALTER TABLE `users_to_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_coupons`
--

DROP TABLE IF EXISTS `users_to_coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_ID` int(10) unsigned NOT NULL,
  `coupons_ID` int(10) unsigned NOT NULL,
  `payments_ID` int(10) unsigned NOT NULL,
  `products_list` text,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_coupons`
--

LOCK TABLES `users_to_coupons` WRITE;
/*!40000 ALTER TABLE `users_to_coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_to_coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_courses`
--

DROP TABLE IF EXISTS `users_to_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_courses` (
  `users_LOGIN` varchar(100) NOT NULL,
  `courses_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `archive` int(10) unsigned DEFAULT '0',
  `from_timestamp` int(10) unsigned DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0',
  `issued_certificate` text,
  `comments` text,
  `to_timestamp` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`users_LOGIN`,`courses_ID`),
  KEY `archive` (`archive`),
  KEY `users_LOGIN` (`users_LOGIN`),
  KEY `courses_ID` (`courses_ID`),
  KEY `from_timestamp` (`from_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_courses`
--

LOCK TABLES `users_to_courses` WRITE;
/*!40000 ALTER TABLE `users_to_courses` DISABLE KEYS */;
INSERT INTO `users_to_courses` VALUES ('cmonterrosa',9,1,0,1397110400,'student',1,100,'','El curso se completa automáticamente cuando todas los módulos sean completados',1397153717),('cmonterrosa',11,1,0,1397108192,'student',0,0,'','',0),('jvazquez',9,1,0,1397106530,'professor',0,0,'','',0),('jvazquez',10,1,0,1397108388,'professor',0,0,'','',0),('jvazquez',11,1,0,1397108388,'professor',0,0,'','',0);
/*!40000 ALTER TABLE `users_to_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_done_surveys`
--

DROP TABLE IF EXISTS `users_to_done_surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_done_surveys` (
  `surveys_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `users_LOGIN` varchar(100) NOT NULL DEFAULT '',
  `done` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`users_LOGIN`,`surveys_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_done_surveys`
--

LOCK TABLES `users_to_done_surveys` WRITE;
/*!40000 ALTER TABLE `users_to_done_surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_to_done_surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_groups`
--

DROP TABLE IF EXISTS `users_to_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_groups` (
  `groups_ID` mediumint(8) unsigned NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  PRIMARY KEY (`groups_ID`,`users_LOGIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_groups`
--

LOCK TABLES `users_to_groups` WRITE;
/*!40000 ALTER TABLE `users_to_groups` DISABLE KEYS */;
INSERT INTO `users_to_groups` VALUES (1,'cmonterrosa'),(1,'jvazquez');
/*!40000 ALTER TABLE `users_to_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_lessons`
--

DROP TABLE IF EXISTS `users_to_lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_lessons` (
  `users_LOGIN` varchar(100) NOT NULL,
  `lessons_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `archive` int(10) unsigned DEFAULT '0',
  `from_timestamp` int(10) unsigned DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `positions` text,
  `done_content` text,
  `current_unit` mediumint(8) unsigned DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `score` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `issued_certificate` blob,
  `comments` text,
  `to_timestamp` int(10) unsigned DEFAULT NULL,
  `access_counter` int(10) DEFAULT '0',
  PRIMARY KEY (`users_LOGIN`,`lessons_ID`),
  KEY `users_LOGIN` (`users_LOGIN`),
  KEY `lessons_ID` (`lessons_ID`),
  KEY `from_timestamp` (`from_timestamp`),
  KEY `archive` (`archive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_lessons`
--

LOCK TABLES `users_to_lessons` WRITE;
/*!40000 ALTER TABLE `users_to_lessons` DISABLE KEYS */;
INSERT INTO `users_to_lessons` VALUES ('cmonterrosa',6,1,1396555720,1396555718,'student','','',0,0,0,'','',0,0),('cmonterrosa',7,1,1396555720,1396555718,'student','','',0,0,0,'','',0,0),('cmonterrosa',8,1,1396555720,1396555718,'student','','',0,0,0,'','',0,0),('cmonterrosa',11,1,0,1397110400,'student','','a:1:{i:1044;s:4:\"1044\";}',1044,1,100,'','Auto completado en: 2014/04/10, 12:58:07',1397152687,0),('cmonterrosa',12,1,0,1397110400,'student','','a:1:{i:1046;s:4:\"1046\";}',1046,1,100,'','Auto completado en: 2014/04/10, 13:15:17',1397153717,0),('jvazquez',9,1,1397101927,1396558909,'professor','','',0,0,0,'','',0,0),('jvazquez',10,1,1397101923,1396558909,'professor',NULL,'',0,0,0,'','',0,0),('jvazquez',11,1,0,1397106530,'professor','','',0,0,0,NULL,'',0,0),('jvazquez',12,1,0,1397106530,'professor','','',0,0,0,NULL,'',0,0),('professor',1,1,0,1396549662,'professor',NULL,'',0,0,0,'','',0,0),('professor',2,1,0,1396549662,'professor',NULL,'',0,0,0,'','',0,0),('professor',3,1,1397101964,1396549662,'professor','','',0,0,0,'','',0,0),('professor',4,1,1397101954,1396549662,'professor','','',0,0,0,'','',0,0),('professor',5,1,1397101969,1396549662,'professor','','',0,0,0,'','',0,0),('professor',6,1,1397101950,1396549662,'professor',NULL,'',0,0,0,'','',0,0),('professor',7,1,1397101991,1396549662,'professor',NULL,'',0,0,0,'','',0,0),('professor',8,1,1397101933,1396549662,'professor',NULL,'',0,0,0,'','',0,0);
/*!40000 ALTER TABLE `users_to_lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_projects`
--

DROP TABLE IF EXISTS `users_to_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_projects` (
  `users_LOGIN` varchar(100) NOT NULL,
  `projects_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `comments` text,
  `grade` float DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `upload_timestamp` int(10) unsigned DEFAULT NULL,
  `last_comment` varchar(100) DEFAULT NULL,
  `professor_upload_filename` varchar(255) DEFAULT NULL,
  `text_grade` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`users_LOGIN`,`projects_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_projects`
--

LOCK TABLES `users_to_projects` WRITE;
/*!40000 ALTER TABLE `users_to_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_to_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_to_surveys`
--

DROP TABLE IF EXISTS `users_to_surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_to_surveys` (
  `surveys_ID` mediumint(8) unsigned NOT NULL,
  `users_LOGIN` varchar(100) NOT NULL,
  `last_access` int(10) unsigned DEFAULT NULL,
  `last_post` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`users_LOGIN`,`surveys_ID`),
  KEY `surveys_ID` (`surveys_ID`,`users_LOGIN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_to_surveys`
--

LOCK TABLES `users_to_surveys` WRITE;
/*!40000 ALTER TABLE `users_to_surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_to_surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `words`
--

DROP TABLE IF EXISTS `words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `words` (
  `groupid` varchar(10) NOT NULL DEFAULT '''lt''',
  `word` varchar(20) NOT NULL DEFAULT '',
  `question` text NOT NULL,
  PRIMARY KEY (`word`,`groupid`),
  KEY `groupid` (`groupid`),
  FULLTEXT KEY `word_3` (`word`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `words`
--

LOCK TABLES `words` WRITE;
/*!40000 ALTER TABLE `words` DISABLE KEYS */;
/*!40000 ALTER TABLE `words` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-04-28  9:12:27
