<?php
require("../config.php");

$files = scandir("../images/");
foreach ($GRADERS as $grader) {
	shuffle($files);
	for ($ri =0 ; $ri < $REPEAT; $ri++) {
		foreach (array_keys($TASKS) as $task) {
			foreach ($files as $file) {
				if (strpos($file, "train-") === false and (strpos($file, ".png") !== false or strpos($file, ".jpg") !== false)) {
					print_r([$file, $task, $grader]);
					$res = $mysqli_connection->query("insert into $TABLE (filename, task, username) values ('$file', '$task', '$grader')");
				}
			}
		}
	}
}

?>
