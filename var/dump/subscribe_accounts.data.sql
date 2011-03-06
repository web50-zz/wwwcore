LOCK TABLES `subscribe_accounts` WRITE;
/*!40000 ALTER TABLE `subscribe_accounts` DISABLE KEYS */;
INSERT INTO `subscribe_accounts` VALUES (1,1,'admin','*4ACFE3202A5FF5CF467898FC58AAB1D615029441','Administrator','unknown@unknown.ru','ru_RU','e91355e959839c4f912be99af2575242','2010-07-29 18:04:43','91.122.35.164'),(2,0,'devel','*8F23662C357D1E7A6214097613335C88FE8BC390','sibscribe user A','all.universe9@gmail.com','ru_RU','0d826dd3d84475a60882019169c05baa','2010-07-30 01:08:01','94.246.126.81'),(4,0,'user_1','*3698E332F65FD723BCE0B7C869554991B0DA418B','subscriber100','m.podgornov@web50.ru','ru_RU','','0000-00-00 00:00:00',''),(7,0,'','','','a.litvinenko@web50.ru','','','0000-00-00 00:00:00',''),(8,0,'','','','f.pozdnyakov@web50.ru','','','0000-00-00 00:00:00','');
/*!40000 ALTER TABLE `subscribe_accounts` ENABLE KEYS */;
UNLOCK TABLES;
