<?php
require("../config.php");

$files = scandir("../images/");

foreach ($files as $file) {
	if (strpos($file, ".png") !== false or strpos($file, ".jpg") !== false) {
		for ($i = 0; $i < $REPLICATES; $i++) {
			$res = $mysqli_connection->query("insert into data (filename, task) values ('$file', '$TASK')");
		}
	}
}

?>
