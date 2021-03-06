/*
SQLyog Community
MySQL - 5.7.26-log : Database - ppa
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Data for the table `employees` */

insert  into `employees`(`id`,`code`,`name`,`phone`,`email`,`department_id`,`position_id`,`line_id`,`created_at`,`updated_at`) values
(1,'10057','Sagita',NULL,NULL,10,6,1,NULL,NULL),
(2,'10218','Arip Hartono',NULL,NULL,10,6,1,NULL,NULL),
(3,'10280','Icuk Sudarto',NULL,NULL,10,6,1,NULL,NULL),
(4,'30860','BIBIT SURYADI',NULL,NULL,10,6,1,NULL,NULL),
(5,'31090','TARYANA',NULL,NULL,10,6,1,NULL,NULL),
(6,'10437','Wawan Sarwan',NULL,NULL,10,6,1,NULL,NULL),
(7,'31065','PENDI S',NULL,NULL,10,6,1,NULL,NULL),
(8,'31071','YUDHI DANIE WIRAWAN',NULL,NULL,10,6,1,NULL,NULL),
(9,'10471','Bubun Burhanudin',NULL,NULL,10,4,1,NULL,NULL),
(10,'10076','Redi Priadi',NULL,NULL,10,4,1,NULL,NULL),
(11,'31073','AGUS SALIM',NULL,NULL,10,6,1,NULL,NULL),
(12,'31120','ZULFIKAR ALI AKBAR',NULL,NULL,10,6,1,NULL,NULL),
(13,'31135','ISMAIL',NULL,NULL,10,6,1,NULL,NULL),
(14,'10127','Akhmad Sahudin',NULL,NULL,10,6,1,NULL,NULL),
(15,'30891','MARDIAN',NULL,NULL,10,6,1,NULL,NULL),
(16,'30892','ZAITUR MIZA',NULL,NULL,10,6,1,NULL,NULL),
(17,'31125','NANA SUJANA',NULL,NULL,10,6,1,NULL,NULL),
(18,'31133','ILHAM',NULL,NULL,10,6,1,NULL,NULL),
(19,'31134','DEDI SETIADI',NULL,NULL,10,6,1,NULL,NULL),
(20,'10094','Wandi Suswanto',NULL,NULL,10,6,1,NULL,NULL),
(21,'10365','Usman Nurkotob',NULL,NULL,10,6,1,NULL,NULL),
(22,'10659','Andriawan',NULL,NULL,10,6,1,NULL,NULL),
(23,'30858','IMRON ROSADI',NULL,NULL,10,6,1,NULL,NULL),
(24,'30862','SAHRUL GUNAWAN',NULL,NULL,10,6,1,NULL,NULL),
(25,'30947','IDI KARMIDI MULYADI',NULL,NULL,10,6,1,NULL,NULL),
(26,'30958','SUGANDA',NULL,NULL,10,6,1,NULL,NULL),
(27,'31106','WAHYU SANTOSO',NULL,NULL,10,6,1,NULL,NULL),
(28,'10032','Lestari Ningsih',NULL,NULL,10,6,1,NULL,NULL),
(29,'10079','Dwi Hartanti',NULL,NULL,10,6,1,NULL,NULL),
(30,'10343','M. Abdul Muhi',NULL,NULL,10,6,1,NULL,NULL),
(31,'10415','Lilik Suprapto Supardi',NULL,NULL,10,6,1,NULL,NULL),
(32,'10400','Andri Rahmadiansyah',NULL,NULL,10,6,1,NULL,NULL),
(33,'10088','Adam Effendi',NULL,NULL,10,4,1,NULL,NULL),
(34,'30880','TAMBAH REjEKI',NULL,NULL,10,6,1,NULL,NULL),
(35,'10319','Taupik Hidayat',NULL,NULL,10,6,1,NULL,NULL),
(36,'30870','SEMUEL SAiYA',NULL,NULL,10,6,1,NULL,NULL),
(37,'31094','JULIUS EFENDI',NULL,NULL,10,6,1,NULL,NULL),
(38,'30857','OMAN BIN RADI S',NULL,NULL,10,6,1,NULL,NULL),
(39,'30875','NURUL IMAN TAUFIQ',NULL,NULL,10,6,1,NULL,NULL),
(40,'10363','Ade',NULL,NULL,10,6,1,NULL,NULL),
(41,'10266','Waryanto',NULL,NULL,10,6,1,NULL,NULL),
(42,'30964','ENDANG SAPUTRA',NULL,NULL,10,6,1,NULL,NULL),
(43,'31044','ROYADI',NULL,NULL,10,6,1,NULL,NULL),
(44,'30873','SURYADI BIN ACAM',NULL,NULL,10,6,1,NULL,NULL),
(45,'30936','ROSADA',NULL,NULL,10,6,1,NULL,NULL),
(46,'30869','NANA MULYANA',NULL,NULL,10,6,1,NULL,NULL),
(47,'30871','ALI NUR RAHMAT',NULL,NULL,10,6,1,NULL,NULL),
(48,'30876','HELDI GUSTIAN',NULL,NULL,10,6,1,NULL,NULL),
(49,'10317','Yanti',NULL,NULL,10,6,1,NULL,NULL),
(50,'10102','Muji Rahayu',NULL,NULL,10,6,1,NULL,NULL),
(51,'30884','SITI MASITOH',NULL,NULL,10,6,1,NULL,NULL),
(52,'31118','ERMA SALFIANI',NULL,NULL,10,6,1,NULL,NULL),
(53,'30883','DADAN WARDIANA',NULL,NULL,10,6,1,NULL,NULL),
(54,'30887','AHMAD FAUZI',NULL,NULL,10,6,1,NULL,NULL),
(55,'30963','MOKHAMAD AMIN',NULL,NULL,10,6,1,NULL,NULL),
(56,'31126','ADITYA FATHONI MU ARIF',NULL,NULL,10,6,1,NULL,NULL),
(57,'30997','ARIP HIDAYAT',NULL,NULL,10,6,1,NULL,NULL),
(58,'31038','KHAERUL UMAM',NULL,NULL,10,6,1,NULL,NULL),
(59,'31121','M IRFAN SETYADI',NULL,NULL,10,6,1,NULL,NULL),
(60,'30872','MOHAMMAD YUSUP TADZIRI',NULL,NULL,10,6,1,NULL,NULL),
(61,'31011','DIMAS FAUZI',NULL,NULL,10,6,1,NULL,NULL),
(62,'30868','KARNADI',NULL,NULL,10,6,1,NULL,NULL),
(63,'30879','ARIPIN',NULL,NULL,10,6,1,NULL,NULL),
(64,'31050','AGUS PERMANA',NULL,NULL,10,6,1,NULL,NULL),
(65,'31104','ISAN SUNANDAR',NULL,NULL,10,6,1,NULL,NULL),
(66,'31105','DADAN',NULL,NULL,10,6,1,NULL,NULL),
(67,'30932','DADANG YUSUF HAMDANI',NULL,NULL,10,6,1,NULL,NULL),
(68,'30937','ASEP RISWAN',NULL,NULL,10,6,1,NULL,NULL),
(69,'30952','ALFIAN',NULL,NULL,10,6,1,NULL,NULL),
(70,'31127','WAHYU IRAWAN BIN CARTIMAN',NULL,NULL,10,6,1,NULL,NULL),
(71,'10277','Tuwuh Susilo',NULL,NULL,10,6,1,NULL,NULL),
(72,'10521','Rohman',NULL,NULL,10,6,1,NULL,NULL),
(73,'10534','Hamdani',NULL,NULL,10,6,1,NULL,NULL),
(74,'10675','Taufik Hidayat',NULL,NULL,10,6,1,NULL,NULL),
(75,'30863','HENDRA GUNAWAN',NULL,NULL,10,6,1,NULL,NULL),
(76,'30946','TRI WAHYUDI',NULL,NULL,10,6,1,NULL,NULL),
(77,'31014','RISKI SAEFULLOH',NULL,NULL,10,6,1,NULL,NULL),
(78,'31042','PIKI ALAN',NULL,NULL,10,6,1,NULL,NULL),
(79,'31075','RIZKY HALALUDIN',NULL,NULL,10,6,1,NULL,NULL),
(80,'31122','TRIAS AGUNG PRAKOSA',NULL,NULL,10,6,1,NULL,NULL),
(81,'31123','ASEP GUNAWAN',NULL,NULL,10,6,1,NULL,NULL),
(82,'31013','HERY PRATAMA PUTRA',NULL,NULL,10,6,1,NULL,NULL),
(83,'31124','SUGIANTO',NULL,NULL,10,6,1,NULL,NULL),
(84,'31030','LEO CHANDRA',NULL,NULL,10,6,1,NULL,NULL),
(85,'30970','SANUDIN',NULL,NULL,10,6,1,NULL,NULL),
(86,'30953','DIKI WAHYUDI',NULL,NULL,10,6,1,NULL,NULL),
(87,'10139','Omin Sutarmin',NULL,NULL,10,6,1,NULL,NULL),
(88,'10457','Hafizin',NULL,NULL,10,6,1,NULL,NULL),
(89,'10484','Karwanto',NULL,NULL,10,6,1,NULL,NULL),
(90,'10502','Dede Suhendar',NULL,NULL,10,6,1,NULL,NULL),
(91,'10052','Pipin Aripin',NULL,NULL,10,6,1,NULL,NULL),
(92,'10265','Ahmad Fauji',NULL,NULL,10,6,1,NULL,NULL),
(93,'30911','Iryan Adipura',NULL,NULL,10,6,1,NULL,NULL),
(94,'31045','INDRA PAMUNGKAS',NULL,NULL,10,6,1,NULL,NULL),
(95,'10022','Deni Purnama',NULL,NULL,10,10,1,NULL,NULL),
(96,'10399','Gandi',NULL,NULL,10,6,1,NULL,NULL),
(97,'10426','Dedy Agus Setiyawan',NULL,NULL,10,6,1,NULL,NULL),
(98,'10546','Asep Sonjaya',NULL,NULL,10,6,1,NULL,NULL),
(99,'30859','AGUS HERMAWAN',NULL,NULL,10,6,1,NULL,NULL),
(100,'30944','ADE GUNAWAN',NULL,NULL,10,6,1,NULL,NULL),
(101,'30954','ALIP ABDUL MUCHI',NULL,NULL,10,6,1,NULL,NULL),
(102,'30877','DIMAS MATHORRIZKY',NULL,NULL,10,6,1,NULL,NULL),
(103,'31061','IRWAN SYAHRONI',NULL,NULL,10,6,1,NULL,NULL),
(104,'10255','Tomi',NULL,NULL,10,4,2,NULL,NULL),
(105,'10183','Dede Sanusi',NULL,NULL,10,6,2,NULL,NULL),
(106,'10303','Dede sasmita',NULL,NULL,10,6,2,NULL,NULL),
(107,'10351','Ace',NULL,NULL,10,6,2,NULL,NULL),
(108,'10419','Tri Minarno',NULL,NULL,10,6,2,NULL,NULL),
(109,'10219','Iwan',NULL,NULL,12,6,3,NULL,NULL),
(110,'10275','Hendri Suhendri',NULL,NULL,12,6,3,NULL,NULL),
(111,'10281','Teguh Prasetya',NULL,NULL,12,6,3,NULL,NULL),
(112,'10519','Nofianto',NULL,NULL,12,6,3,NULL,NULL),
(113,'10014','Maesaroh',NULL,NULL,11,6,5,NULL,NULL),
(114,'10118','Sanita',NULL,NULL,11,4,5,NULL,NULL),
(115,'10142','Ahmad Riyanto',NULL,NULL,11,4,5,NULL,NULL),
(116,'10123','Cece Sunardi',NULL,NULL,11,4,5,NULL,NULL),
(117,'30831','Sahrodi',NULL,NULL,11,6,5,NULL,NULL),
(118,'30840','MUHAMAD ALIZEN',NULL,NULL,11,6,5,NULL,NULL),
(119,'30843','ANGGA PRIBADI',NULL,NULL,11,6,5,NULL,NULL),
(120,'30861','YOGI ISKANDAR',NULL,NULL,11,6,5,NULL,NULL),
(121,'31079','ROSIKIN',NULL,NULL,11,6,5,NULL,NULL),
(122,'31080','ANDI KURNIA',NULL,NULL,11,6,5,NULL,NULL),
(123,'31082','EKO PRAMONO',NULL,NULL,11,6,5,NULL,NULL),
(124,'31083','TATANG SUMARLIN',NULL,NULL,11,6,5,NULL,NULL),
(125,'31092','IRWAN HERMAWAN',NULL,NULL,11,6,5,NULL,NULL),
(126,'31101','ANGGUN GUNAWAN',NULL,NULL,11,6,5,NULL,NULL),
(127,'31110','SYUKRON ALI HAMDI',NULL,NULL,11,6,5,NULL,NULL),
(128,'31111','EGI SALAM',NULL,NULL,11,6,5,NULL,NULL),
(129,'31114','ANANG KURNIA',NULL,NULL,11,6,5,NULL,NULL),
(130,'31115','ARIF YANTO',NULL,NULL,11,6,5,NULL,NULL),
(131,'31117','EGI SAEPULOH',NULL,NULL,11,6,5,NULL,NULL),
(132,'30855','KHUMAEDI ADNAN',NULL,NULL,10,6,5,NULL,NULL),
(133,'10131','Wiyanti',NULL,NULL,11,6,5,NULL,NULL),
(134,'10910','Yadi Mulyana',NULL,NULL,11,6,5,NULL,NULL),
(135,'10409','Dede Rusmanto',NULL,NULL,11,6,5,NULL,NULL),
(136,'10447','Mizan Hakim',NULL,NULL,11,6,5,NULL,NULL),
(137,'10445','Indra Rukmana',NULL,NULL,11,6,5,NULL,NULL),
(138,'10116','Karsim',NULL,NULL,11,6,5,NULL,NULL),
(139,'10346','Juriansah',NULL,NULL,11,6,5,NULL,NULL),
(140,'10051','Sriyono',NULL,NULL,11,10,5,NULL,NULL),
(141,'30822','RAHYUDI NUGRAHA',NULL,NULL,10,6,7,NULL,NULL),
(142,'30824','SUHANA',NULL,NULL,10,6,7,NULL,NULL),
(143,'30826','Dhika Tree Soendawa',NULL,NULL,10,6,7,NULL,NULL),
(144,'30829','HENDRO KURNIAWAN',NULL,NULL,10,6,7,NULL,NULL),
(145,'10327','Rahman',NULL,NULL,10,6,7,NULL,NULL),
(146,'10152','Wawan Mubarak Ahmad',NULL,NULL,10,6,7,NULL,NULL),
(147,'10204','Rohidin',NULL,NULL,10,6,7,NULL,NULL),
(148,'10356','Andi Triyadi',NULL,NULL,10,6,7,NULL,NULL),
(149,'10229','Komarudin',NULL,NULL,11,6,9,NULL,NULL),
(150,'10423','Persojo',NULL,NULL,11,6,9,NULL,NULL),
(151,'31063','ADI YURIATNA',NULL,NULL,11,6,10,NULL,NULL),
(152,'10208','Mulyana Solehudin',NULL,NULL,11,4,10,NULL,NULL),
(153,'10158','Emin',NULL,NULL,11,4,11,NULL,NULL),
(154,'10202','Cecep Ahyad',NULL,NULL,11,6,11,NULL,NULL),
(155,'10213','Agung Nugroho',NULL,NULL,11,6,11,NULL,NULL),
(156,'10209','Suherman',NULL,NULL,11,6,11,NULL,NULL),
(157,'10326','M. Ade Hamzen',NULL,NULL,11,6,11,NULL,NULL),
(158,'10066','Windu Hartoyo',NULL,NULL,11,10,11,NULL,NULL),
(159,'31046','PUJI SETIADI',NULL,NULL,11,6,13,NULL,NULL),
(160,'31078','FADLY',NULL,NULL,11,6,13,NULL,NULL),
(161,'30834','Agus Setiawan',NULL,NULL,11,6,13,NULL,NULL),
(162,'30888','ABDUL RAHMANSYAH',NULL,NULL,11,6,13,NULL,NULL),
(163,'31069','ARIH HIDAYAT',NULL,NULL,11,6,13,NULL,NULL),
(164,'10214','Andri Setiawan',NULL,NULL,11,4,13,NULL,NULL),
(165,'10261','Wahyudin',NULL,NULL,10,6,13,NULL,NULL),
(166,'10067','Subiantoro',NULL,NULL,11,9,13,NULL,NULL),
(167,'30832','SAHRUL GUNAWAN',NULL,NULL,11,6,13,NULL,NULL),
(168,'31024','IMAM TABRONI',NULL,NULL,11,6,13,NULL,NULL),
(169,'31039','ENGKAY BIN JAE',NULL,NULL,11,6,13,NULL,NULL),
(170,'31047','IIF IRPANSYAH',NULL,NULL,11,6,13,NULL,NULL),
(171,'31062','YUDI ARIYANTO',NULL,NULL,11,6,13,NULL,NULL),
(172,'30916','EDI ABDUL HADI',NULL,NULL,11,6,13,NULL,NULL),
(173,'30922','MOH ALPATAH',NULL,NULL,11,6,13,NULL,NULL),
(174,'31019','MULYANA',NULL,NULL,11,6,13,NULL,NULL),
(175,'31087','ASEP ALI YAJID',NULL,NULL,11,6,13,NULL,NULL),
(176,'31096','TARYA RUSYANA',NULL,NULL,11,6,13,NULL,NULL),
(177,'10436','Asep Ridwan Hanapi',NULL,NULL,10,4,14,NULL,NULL),
(178,'31088','DEDEN SARIFUDIN',NULL,NULL,10,6,14,NULL,NULL),
(179,'31108','ROHMANNUDIN',NULL,NULL,10,6,14,NULL,NULL),
(180,'31109','DIDIN SUROJUDIN',NULL,NULL,10,6,14,NULL,NULL),
(181,'10381','Arsyad Ramzi',NULL,NULL,10,4,14,NULL,NULL),
(182,'10364','Ruri',NULL,NULL,10,6,14,NULL,NULL),
(183,'10418','Eki Satrio',NULL,NULL,10,6,14,NULL,NULL),
(184,'10463','Engkus Kusaeri',NULL,NULL,10,6,14,NULL,NULL),
(185,'10133','Wasdi Iswanto',NULL,NULL,10,6,14,NULL,NULL),
(186,'10150','Sudiana',NULL,NULL,10,6,14,NULL,NULL),
(187,'10505','Sandy Nurahman',NULL,NULL,10,6,14,NULL,NULL),
(188,'30844','AGUS WINDARTO',NULL,NULL,10,6,14,NULL,NULL),
(189,'30845','RENDI MARDIKA',NULL,NULL,10,6,14,NULL,NULL),
(190,'30846','AGENG WAHIDIN',NULL,NULL,10,6,14,NULL,NULL),
(191,'30990','ADIT TIYA MARDIYANTO',NULL,NULL,10,6,14,NULL,NULL),
(192,'31066','M AJIJI JAENUDIN',NULL,NULL,10,6,14,NULL,NULL),
(193,'30878','IMAM BUHORI',NULL,NULL,10,6,14,NULL,NULL),
(194,'30928','SULAEMAN',NULL,NULL,10,6,14,NULL,NULL),
(195,'31059','TRI SANTOSO',NULL,NULL,10,6,14,NULL,NULL),
(196,'31086','SURYADI',NULL,NULL,10,6,14,NULL,NULL),
(197,'30938','RUSTAM MAJI',NULL,NULL,10,6,14,NULL,NULL),
(198,'30971','ASEP',NULL,NULL,10,6,14,NULL,NULL),
(199,'30972','RIAN ARIA SUSANTO',NULL,NULL,10,6,14,NULL,NULL),
(200,'30976','NURDI',NULL,NULL,10,6,14,NULL,NULL),
(201,'30918','SURAFI',NULL,NULL,10,6,14,NULL,NULL),
(202,'30975','SOBFIRMANSAH',NULL,NULL,10,6,14,NULL,NULL),
(203,'31043','DARSUM SAPUTRA',NULL,NULL,10,6,14,NULL,NULL),
(204,'31099','WANDA MUBAROK',NULL,NULL,10,6,14,NULL,NULL),
(205,'30921','JAUHARUDIN',NULL,NULL,10,6,14,NULL,NULL),
(206,'30981','ADITIAR NUR RIZKI',NULL,NULL,10,6,14,NULL,NULL),
(207,'10694','Asep Sapaat',NULL,NULL,10,9,14,NULL,NULL),
(208,'30980','ARI KURNIAWAN',NULL,NULL,10,6,14,NULL,NULL),
(209,'30987','SUTRISNO',NULL,NULL,10,6,14,NULL,NULL),
(210,'30989','RIDWAN SYAPUTRA',NULL,NULL,10,6,14,NULL,NULL),
(211,'30851','ARYO',NULL,NULL,10,6,15,NULL,NULL),
(212,'30852','SUGIYONO',NULL,NULL,10,6,15,NULL,NULL),
(213,'30950','WASNADI',NULL,NULL,10,6,15,NULL,NULL),
(214,'31067','UNDA JUANDA',NULL,NULL,10,6,16,NULL,NULL),
(215,'10264','BANGBANG SETIAWAN',NULL,NULL,12,6,16,NULL,NULL),
(216,'10153','Iing Solihin',NULL,NULL,2,6,NULL,NULL,NULL),
(217,'20821','Cindy Clara Simamora',NULL,NULL,4,9,NULL,NULL,NULL),
(218,'10107','Siti Musrifah',NULL,NULL,2,6,NULL,NULL,NULL),
(219,'10086','Eka Winarti',NULL,NULL,4,9,NULL,NULL,NULL),
(220,'10120','Windawati',NULL,NULL,4,6,NULL,NULL,NULL),
(221,'10344','Siti Nuraisah',NULL,NULL,4,6,NULL,NULL,NULL),
(222,'20818','Siti Masitoh',NULL,NULL,4,6,NULL,NULL,NULL),
(223,'10074','Munandar',NULL,NULL,5,6,NULL,NULL,NULL),
(224,'10361','Achmad Ridwan Zailani',NULL,NULL,14,6,NULL,NULL,NULL),
(225,'10788','Moh. Saoki',NULL,NULL,2,6,NULL,NULL,NULL),
(226,'10031','Yuna Tristianto',NULL,NULL,4,2,NULL,NULL,NULL),
(227,'10043','Iman Firmansyah',NULL,NULL,4,2,NULL,NULL,NULL),
(228,'20790','Tatang',NULL,NULL,4,2,NULL,NULL,NULL),
(229,'31006','JULYANTO SIMANJUNTAK',NULL,NULL,11,6,NULL,NULL,NULL),
(230,'HRN','Harun',NULL,NULL,1,1,NULL,NULL,NULL),
(231,'30842','Tarwidi',NULL,NULL,4,6,NULL,NULL,NULL),
(232,'30854','SAMSUDIN',NULL,NULL,3,6,NULL,NULL,NULL),
(233,'30929','RATINAH',NULL,NULL,3,6,NULL,NULL,NULL),
(234,'31048','AGUS SUPRIYATNO',NULL,NULL,3,6,NULL,NULL,NULL),
(235,'10291','Sarman Hermawan',NULL,NULL,14,6,NULL,NULL,NULL),
(236,'10252','Ladi Suandi',NULL,NULL,5,6,NULL,NULL,NULL),
(237,'30903','Sarip',NULL,NULL,4,6,NULL,NULL,NULL),
(238,'30904','Asep Hidayat',NULL,NULL,4,6,NULL,NULL,NULL),
(239,'30905','Alfian',NULL,NULL,4,6,NULL,NULL,NULL),
(240,'30906','IIP SAIFUL BAHRI',NULL,NULL,4,6,NULL,NULL,NULL),
(241,'30907','ACEP HARYANTO',NULL,NULL,4,6,NULL,NULL,NULL),
(242,'30955','FAJAR RAMELAN',NULL,NULL,4,6,NULL,NULL,NULL),
(243,'31132','SUGI P',NULL,NULL,4,6,NULL,NULL,NULL),
(244,'20808','Tinuk Istiyawati',NULL,NULL,8,9,NULL,NULL,NULL),
(245,'20820','Nur Chinta Adriana Putri',NULL,NULL,8,9,NULL,NULL,NULL),
(246,'10113','Wawan Kurniawan',NULL,NULL,11,4,NULL,NULL,NULL),
(247,'10072','Nana Suharum',NULL,NULL,14,4,NULL,NULL,NULL),
(248,'10065','M.Tohir',NULL,NULL,4,4,NULL,NULL,NULL),
(249,'10373','Timin Saefulloh',NULL,NULL,4,4,NULL,NULL,NULL),
(250,'10027','Ichsan Jamaludin',NULL,NULL,14,4,NULL,NULL,NULL),
(251,'10110','Eddi Nuryanto',NULL,NULL,11,4,NULL,NULL,NULL),
(252,'10055','Subur Widodo',NULL,NULL,6,4,NULL,NULL,NULL),
(253,'11102','WELLY SULAYMAN',NULL,NULL,4,5,NULL,NULL,NULL),
(254,'11097','SURIPTO',NULL,NULL,14,5,NULL,NULL,NULL),
(255,'10670','Tang Liana',NULL,NULL,2,5,NULL,NULL,NULL),
(256,'10802','Andreas Charles S.',NULL,NULL,9,5,NULL,NULL,NULL),
(257,'10199','Sumitronis Wijaya',NULL,NULL,4,5,NULL,NULL,NULL),
(258,'10809','EKO HENDRAWAN',NULL,NULL,14,5,NULL,NULL,NULL),
(259,'ONO','SHOZO ONODA',NULL,NULL,4,3,NULL,NULL,NULL),
(260,'21137','SITI AISYAH',NULL,NULL,4,NULL,NULL,NULL,NULL),
(261,'10025','Agus Heriyanto',NULL,NULL,5,4,NULL,NULL,NULL),
(262,'10035','Iwan Irawan',NULL,NULL,5,6,NULL,NULL,NULL),
(263,'10787','Madi Prihana',NULL,NULL,5,6,NULL,NULL,NULL),
(264,'20804','Sadar Iskandar M. Bin Darwan',NULL,NULL,5,6,NULL,NULL,NULL),
(265,'21138','Nuryaman',NULL,NULL,5,6,NULL,NULL,NULL),
(266,'30847','ANDEN SASMITA',NULL,NULL,5,6,NULL,NULL,NULL),
(267,'30908','Muhtadin',NULL,NULL,5,6,NULL,NULL,NULL),
(268,'21031','YULI ASTUTI',NULL,NULL,4,6,NULL,NULL,NULL),
(269,'20995','RASIM ACING',NULL,NULL,5,6,NULL,NULL,NULL),
(270,'20814','ANDRI SAPRI YANTO GUMAY,ST',NULL,NULL,14,6,NULL,NULL,NULL),
(271,'20815','SRI SUTARMO',NULL,NULL,14,6,NULL,NULL,NULL),
(272,'20819','Div Agusto Mendayun',NULL,NULL,14,6,NULL,NULL,NULL),
(273,'21058','WENDI AFRIZAL',NULL,NULL,14,6,NULL,NULL,NULL),
(274,'10298','Endang',NULL,NULL,9,6,NULL,NULL,NULL),
(275,'10299','Maulana Sopian',NULL,NULL,9,6,NULL,NULL,NULL),
(276,'10300','Ronika',NULL,NULL,9,6,NULL,NULL,NULL),
(277,'10053','Budi Suffin',NULL,NULL,4,6,NULL,NULL,NULL),
(278,'10099','Adim Miharja',NULL,NULL,4,6,NULL,NULL,NULL),
(279,'10100','Ngahadi',NULL,NULL,4,6,NULL,NULL,NULL),
(280,'10136','Nunung Iskandar',NULL,NULL,4,6,NULL,NULL,NULL),
(281,'10324','Rustani',NULL,NULL,4,6,NULL,NULL,NULL),
(282,'21084','SUPRAPTO BUDI SUSILO',NULL,NULL,4,6,NULL,NULL,NULL),
(283,'10091','Saifudin',NULL,NULL,10,6,NULL,NULL,NULL),
(284,'10287','Solahudin',NULL,NULL,3,6,NULL,NULL,NULL),
(285,'21085','AGUS KUSWORO',NULL,NULL,4,6,NULL,NULL,NULL),
(286,'30934','MUHAMAD IQBAL',NULL,NULL,4,6,NULL,NULL,NULL),
(287,'21139','Abdul Kadir Hasan',NULL,NULL,11,6,NULL,NULL,NULL),
(288,'31068','SUHENDRA',NULL,NULL,11,6,NULL,NULL,NULL),
(289,'10292','Wahyudi',NULL,NULL,11,6,NULL,NULL,NULL),
(290,'10417','Jajang Bahrudin',NULL,NULL,11,6,NULL,NULL,NULL),
(291,'31093','MUHAMAD JOHAN EDI SUSANTO',NULL,NULL,11,6,NULL,NULL,NULL),
(292,'30999','AGUNG SAPUTRA',NULL,NULL,11,6,NULL,NULL,NULL),
(293,'31055','ABDUL MALIK IBRAHIM',NULL,NULL,11,6,NULL,NULL,NULL),
(294,'10240','Muhyi',NULL,NULL,11,6,NULL,NULL,NULL),
(295,'31000','FATHUR ROHMAN WAHID',NULL,NULL,11,6,NULL,NULL,NULL),
(296,'31021','ADE AHMAD KURNIAWAN',NULL,NULL,11,6,NULL,NULL,NULL),
(297,'31022','AGUS SALIM',NULL,NULL,11,6,NULL,NULL,NULL),
(298,'31112','RIAN YANUAR',NULL,NULL,11,6,NULL,NULL,NULL),
(299,'31129','ASEP MUHAMAD',NULL,NULL,11,6,NULL,NULL,NULL),
(300,'10181','Tanti Nurlianti',NULL,NULL,11,6,NULL,NULL,NULL),
(301,'10187','Agustina Melinda Simalango',NULL,NULL,11,6,NULL,NULL,NULL),
(302,'10256','Sukarman Darmawan',NULL,NULL,11,6,NULL,NULL,NULL),
(303,'10453','Titin Winingsih',NULL,NULL,11,6,NULL,NULL,NULL),
(304,'30913','RODIANTO',NULL,NULL,11,6,NULL,NULL,NULL),
(305,'30974','ANGGA WAHYUDI',NULL,NULL,11,6,NULL,NULL,NULL),
(306,'31009','NURYANA HIDAYAT',NULL,NULL,11,6,NULL,NULL,NULL),
(307,'31010','MEI SETIAWATI',NULL,NULL,11,6,NULL,NULL,NULL),
(308,'31054','REZA DZUL GAWAM',NULL,NULL,11,6,NULL,NULL,NULL),
(309,'31113','ADI JAYA',NULL,NULL,11,6,NULL,NULL,NULL),
(310,'31119','CEPY MARYANA',NULL,NULL,11,6,NULL,NULL,NULL),
(311,'31128','ANGGA MOH F',NULL,NULL,11,6,NULL,NULL,NULL),
(312,'31130','BUDIMAN',NULL,NULL,11,6,NULL,NULL,NULL),
(313,'31131','NUNU KARYA NUGRAHA',NULL,NULL,11,6,NULL,NULL,NULL),
(314,'10746','Nana Suparna',NULL,NULL,6,6,NULL,NULL,NULL),
(315,'10021','Sutrisna',NULL,NULL,14,4,NULL,NULL,NULL),
(316,'10149','Nuroni',NULL,NULL,6,6,NULL,NULL,NULL),
(317,'10193','Mus Mulyadi',NULL,NULL,6,6,NULL,NULL,NULL),
(318,'10196','Hendra',NULL,NULL,6,6,NULL,NULL,NULL),
(319,'10228','Wahyu',NULL,NULL,6,6,NULL,NULL,NULL),
(320,'10232','Ajat Sudrajat Chromate',NULL,NULL,6,6,NULL,NULL,NULL),
(321,'10251','Cecep Hidayat',NULL,NULL,6,6,NULL,NULL,NULL),
(322,'10278','Endang Kusnadi',NULL,NULL,6,9,NULL,NULL,NULL),
(323,'10312','Rinto Susanto',NULL,NULL,6,6,NULL,NULL,NULL),
(324,'10597','Sawita',NULL,NULL,6,6,NULL,NULL,NULL),
(325,'10650','Muhammad Rustam',NULL,NULL,6,6,NULL,NULL,NULL),
(326,'30889','Rudi Setiawan',NULL,NULL,6,6,NULL,NULL,NULL),
(327,'30986','HERMANTO',NULL,NULL,6,6,NULL,NULL,NULL),
(328,'31012','AJIJUL MALIK',NULL,NULL,6,6,NULL,NULL,NULL),
(329,'31056','AGUS SETIONO',NULL,NULL,6,6,NULL,NULL,NULL),
(330,'10222','Murti Kusumawati',NULL,NULL,2,9,NULL,NULL,NULL),
(331,'21140','Mustafikin',NULL,NULL,13,6,NULL,NULL,NULL),
(332,'31070','VICKY DWI PRATAMA',NULL,NULL,13,6,NULL,NULL,NULL),
(333,'31116','OKY ODANG',NULL,NULL,13,6,NULL,NULL,NULL),
(334,'10135','Ellisa Herry Widiastuti',NULL,NULL,3,6,NULL,NULL,NULL),
(335,'10018','Suratman',NULL,NULL,3,7,NULL,NULL,NULL),
(336,'10049','Sudarso',NULL,NULL,3,7,NULL,NULL,NULL),
(337,'10806','Tardjo Bugiseno',NULL,NULL,2,8,NULL,NULL,NULL),
(338,'10811','Aprianto Edy Kurniawan',NULL,NULL,4,9,NULL,NULL,NULL),
(339,'10734','Esteria Hotnauli H',NULL,NULL,2,9,NULL,NULL,NULL),
(340,'20909','Rossy Evarista A',NULL,NULL,2,9,NULL,NULL,NULL),
(341,'10304','Maranata Paulina Pandiangan',NULL,NULL,2,9,NULL,NULL,NULL),
(342,'10792','Mas\'ud',NULL,NULL,3,10,NULL,NULL,NULL),
(343,'10671','Saeiful Huda',NULL,NULL,4,10,NULL,NULL,NULL),
(344,'10064','Eko Waluyo',NULL,NULL,5,10,NULL,NULL,NULL),
(345,'30893','Lahagu Lumban Tobing',NULL,NULL,4,6,NULL,NULL,NULL),
(346,'30894','Erikson',NULL,NULL,4,6,NULL,NULL,NULL),
(347,'30895','Kasri/Boim',NULL,NULL,4,6,NULL,NULL,NULL),
(348,'30896','Ahmad Faisal',NULL,NULL,4,6,NULL,NULL,NULL),
(349,'30897','Opik',NULL,NULL,4,6,NULL,NULL,NULL),
(350,'30898','Rudi Firmansyah',NULL,NULL,4,6,NULL,NULL,NULL),
(351,'30900','HENRA PARDOSI',NULL,NULL,4,6,NULL,NULL,NULL),
(352,'30901','AMRAN',NULL,NULL,4,6,NULL,NULL,NULL),
(353,'30924','SUGI HARYANTO',NULL,NULL,4,6,NULL,NULL,NULL),
(354,'30926','LAMSAR NABABAN',NULL,NULL,4,6,NULL,NULL,NULL),
(355,'31028','AGUS SALIM',NULL,NULL,4,6,NULL,NULL,NULL),
(356,'31064','AGUS',NULL,NULL,4,6,NULL,NULL,NULL),
(357,'31089','AMIN SUMARMIN',NULL,NULL,4,6,NULL,NULL,NULL),
(358,'31136','SARTO',NULL,NULL,4,6,NULL,NULL,NULL),
(359,'Maman','MAMAN',NULL,NULL,3,11,NULL,NULL,NULL),
(360,'10005','Harun Muhyi',NULL,NULL,5,6,NULL,NULL,NULL),
(361,'10039','Anto Sugianto',NULL,NULL,5,6,NULL,NULL,NULL),
(362,'10104','Adim Saepudin',NULL,NULL,4,6,NULL,NULL,NULL),
(363,'10147','Ihsanuloh Munir',NULL,NULL,15,6,NULL,NULL,NULL),
(364,'10253','Saya Sunarya',NULL,NULL,15,6,NULL,NULL,NULL),
(365,'10301','Ade Wahyudin',NULL,NULL,15,6,NULL,NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
