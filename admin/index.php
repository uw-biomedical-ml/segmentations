<?php
require("../config.php");

$res = $mysqli_connection->query("SELECT id, ts, filename, username, task, status FROM data where username IS NOT NULL ORDER BY ts desc ");

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Segementation Results</title>
    <!-- Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css" integrity="sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi" crossorigin="anonymous">
  <script type='text/javascript' src='http://code.jquery.com/jquery-2.0.2.js'></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js" integrity="sha384-XTs3FgkjiBgo8qjEjBk0tGmf3wPrWtA6coPfQDfFEY8AnYJwjalXCiosYRBIBZX8" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js" integrity="sha384-BLiI7JTZm+JWlgKa0M0kGRpJbF2J8q+qreVrKBC47e3K6BW78kGLrCkeRX6I9RoK" crossorigin="anonymous"></script>
  </head>
  <body>
    <div class='container'>

<div class="row">
<div class=" col-12">
<br/>
<br/>
<a href="reviewresult.php">Review Results</a>
<br/>
<br/>
<br/>
<h4>Table of results</h4>

<table class="table table-striped">
<thead><tr><th>id</th><th>Timestamp</th><th>Filename</th><th>Task</th><th>Username</th><th>Status</th></tr></thead>
<tbody>
<?php
while ($row = $res->fetch_row()) {
	print "<tr>\n";

	print "<td><a href='viewresult.php?id=$row[0]'>" . $row[0] . "</a></td>\n";
	print "<td><a href='viewresult.php?id=$row[0]'>" . $row[1] . "</a></td>\n";
	print "<td><a href='viewresult.php?id=$row[0]'>" . $row[2] . "</a></td>\n";
	print "<td><a href='viewresult.php?id=$row[0]'>" . $row[4] . "</a></td>\n";
	print "<td><a href='viewresult.php?id=$row[0]'>" . $row[3] . "</a></td>\n";
	if ( $row[5] == "" or $row[5] == null) {
		print "<td>Unreviewed</td>\n";
	} else if ($row[5] == 0)  {
		print "<td>Accepted</td>\n";
	} else if ($row[5] == 1)  {
		print "<td>Rejected, needs to be made available again</td>\n";
	}

	print "</tr>\n";

}

$mysqli_connection->close();

?>
</tbody>
</table>
</div>
</div>
<div>
</body>
</html>
