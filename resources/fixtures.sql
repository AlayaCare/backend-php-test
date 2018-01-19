--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'user1','$2y$13$dv/srAhn78NoG8wGyKPNcO8n9b/awG1EUWtHuHFvDbw8rIAJXorKi'),(2,'user2','user2'),(3,'user3','user3');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Dumping data for table `todos`
--

LOCK TABLES `todos` WRITE;
/*!40000 ALTER TABLE `todos` DISABLE KEYS */;
INSERT INTO `todos` VALUES (1,'Install Doctrine Migrations','To get rich features such as the migration scheme diif','New','2018-01-15 15:08:03','2018-01-15 15:08:03',1),(2,'Route Variable Converters','Use convert route variable {id} to todo object and/or validate it is a positive integer,','New','2018-01-15 15:21:22','2018-01-15 15:21:22',1),(3,'CSS polish','Fix some \'quick and dirty\' styling','New','2018-01-15 15:25:42','2018-01-15 15:25:42',1),(4,'Add Registration form','A registration form may be useful!','New','2018-01-15 15:28:19','2018-01-15 15:28:19',1),(5,'Diet for todo controller','Make a superclass controller class, thinning out the child-to-be todo controller and preparing for future development (well at least in theory!)','New','2018-01-15 15:31:49','2018-01-15 15:31:49',1),(6,'Tests','Write tests of course','New','2018-01-15 15:32:44','2018-01-15 15:32:44',1),(7,'Fully integrate Vue.js','Bind form elements and implement AJAX calls','New','2018-01-15 15:33:58','2018-01-15 15:33:58',1),(8,'Migrate to Symfony!!','Migrate to Symfony as Silex2 is now officially approaching end of life (in June) as announced on the 12th of January. This is the most theoretical to do task of all!!','Cancelled','2018-01-15 15:38:09','2018-01-15 15:42:46',1);
/*!40000 ALTER TABLE `todos` ENABLE KEYS */;
UNLOCK TABLES;
