<?php
require("../config.php");

$res = $mysqli_connection->query("
	CREATE TABLE `data` (
		`id` int(6) unsigned NOT NULL AUTO_INCREMENT, 
		`username` varchar(30) DEFAULT NULL, 
		`filename` varchar(100) DEFAULT NULL, 
		`task` varchar(30) DEFAULT NULL, 
		`data_json` longtext, 
		`image_base64` longtext, 
		`comments` text, 
		`ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, 
		`status` int(11) DEFAULT NULL, 
		PRIMARY KEY (`id`), 
		KEY `idex_task` (`task`), 
		KEY `idex_file_user` (`filename`,`username`))")

?>
