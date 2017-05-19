<?php
require("../config.php");


if (isset($_GET["id"])) {
	$status = 1; # reject
	if ($_GET["action"] == "accept") {
		$status = 0;
	}
	$gid = intval($_GET["id"]);
	$res = $mysqli_connection->query("UPDATE data SET status = $status WHERE id= $gid ");
}

$res = $mysqli_connection->query("SELECT count(*) FROM data WHERE status IS NULL and username IS NOT NULL");
$row = $res->fetch_row();
$todo = $row[0];

$res = $mysqli_connection->query("SELECT filename, data_json, task, id, username, comments FROM data WHERE status IS NULL and username IS NOT NULL LIMIT 1");
$row = $res->fetch_row();
$id = $row[3];
$task = strtoupper($row[2]);

if ($row[1] == "") {
	$row[1] = "null";
}


?>
<!DOCTYPE html>
<html>
  <head>
    <title>Verify images</title>
    <!-- Bootstrap -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script type='text/javascript' src='http://code.jquery.com/jquery-2.0.2.js'></script>
  </head>
  <body>
    <div class='container'>

    <h1><?=$todo?> images left to review, reviewing <?=$id?></h1>
<div class="row">
<div class=" col-md-offset-3 col-md-6">
<a href="reviewresult.php?id=<?=$id?>&action=accept">Accept</a> | 
<a href="reviewresult.php?id=<?=$id?>&action=reject">Reject</a>
<!--
<br/>
<pre><? print_r($_GET);?></pre>
-->
</div>
</div>
<div class="row">
<div class=" col-md-offset-3 col-md-6">
<h1>Segment <?=$task?>, done by <?=$row[4]?></h1>
<pre><? print($row[5]);?></pre>
		<div id="container">
		    <canvas id="imageView">
			<p>Unfortunately, your browser is currently unsupported by our web application. We are sorry for the inconvenience. Please use one of the supported browsers listed below.</p>
			<p>Supported browsers: <a href="http://chrome.google.com">Chrome</a>, <a href="http://www.opera.com">Opera</a>, <a href="http://www.mozilla.com">Firefox</a>, <a href="http://www.apple.com/safari">Safari</a>, and <a href="http://www.konqueror.org">Konqueror</a>.</p>
		    </canvas>
		</div>
<img src="viewimg.php?id=<?=$id?>">
</div>
</div>
<div>
</body>
<script>
var data = <?=$row[1]?>;
var imgurl = "../images/<?=$row[0]?>";
var context;
var canvas;
var imgH;
var imgW;

var curi = 0;
var curj = 0;

function drawnextline() {
  if (curj == 0) {
	context.strokeStyle = '#00ff00';
	context.beginPath();
	context.moveTo(data[curi].mousex[curj], data[curi].mousey[curj]);
  } else {
	context.lineTo(data[curi].mousex[curj], data[curi].mousey[curj]);
	context.stroke();
  }

  var curtime = data[curi].mousetime[curj];
  curj++;
  if (curj >= data[curi].mousex.length) {
    curj = 0;
    curi++;
    if (curi >= data.length) {
	context.clearRect(0, 0, canvas.width, canvas.height);
      curi = 0;
    }
  }
  timeout = data[curi].mousetime[curj] - curtime;
  if (curj == 0) timeout = 500;
  setTimeout( drawnextline , timeout);
}

function init() {
    $("#imageView").css('background-image', 'url(' + imgurl + ')');
    var img = new Image;
    $(img).load(function () {
        imgH = img.height;
        imgW = img.width;
        $("#imageView").attr({
            'width': imgW ,
            'height': imgH
        });

	if (!(data === null)) {
		drawnextline();
	}
    });
    img.src = imgurl;

    // Find the canvas element.
    canvas = document.getElementById('imageView');
    if (!canvas) {
        alert('Error: I cannot find the canvas element!');
        return;
    }

    if (!canvas.getContext) {
        alert('Error: no canvas.getContext!');
        return;
    }

    // Get the 2D canvas context.
    context = canvas.getContext('2d');
    if (!context) {
        alert('Error: failed to getContext!');
        return;
    }
}

$(window).load(function(){ init(); });

</script>
</html>
