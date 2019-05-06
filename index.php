<?php
require("config.php");
if (isset($_POST["id"]) && !empty($_POST["id"])) {
	$stmt = $mysqli_connection->prepare("UPDATE $TABLE SET comments = ?, data_json = ?, image_base64 = ?, ts=NOW(), status = 'done'  WHERE id = ?");
	$stmt->bind_param("ssss", $_POST["comments"], $_POST["data"], $_POST["pngfile"],  $_POST["id"]);
	$stmt->execute();
}

if ((isset($_POST["username"]) && !empty($_POST["username"]))) {
	$user = $_POST["username"];
	$id = null;
	$left = 0;
	$filename = null;
	$task = null;
	$res = $mysqli_connection->query("SELECT id, filename, task FROM $TABLE WHERE username = '$user' and status IS NULL ORDER BY id ASC ");
	while ($row = $res->fetch_row()) {
		if ($id == null) {
			$id = $row[0];
			$filename = $row[1];
			$task = $row[2];
		}
		$left += 1;
	}
	$imgfile = "images/$filename";
	$taskdescipt = $TASKS[$task][1];
	$mode = $TASKS[$task][0];
}


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Crowdsource Segmenter</title>
  
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css" integrity="sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi" crossorigin="anonymous">
  <script type='text/javascript' src='http://code.jquery.com/jquery-2.0.2.js'></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.3.7/js/tether.min.js" integrity="sha384-XTs3FgkjiBgo8qjEjBk0tGmf3wPrWtA6coPfQDfFEY8AnYJwjalXCiosYRBIBZX8" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js" integrity="sha384-BLiI7JTZm+JWlgKa0M0kGRpJbF2J8q+qreVrKBC47e3K6BW78kGLrCkeRX6I9RoK" crossorigin="anonymous"></script>
  <script type='text/javascript' src="js/jquery.json.min.js"></script>
  <script type='text/javascript' src="js/purl.js"></script>
<?php if ($mode == "line") { ?>
  <script type='text/javascript' src="js/line.js"></script>
<?php } else { ?>
  <script type='text/javascript' src="js/poly.js"></script>
<?php } ?>
  <style type='text/css'>
          #container {
          position: relative;
      }
      #imageView {
          border: 1px solid #000;
      }
      canvas {
          background-repeat:no-repeat;
          background-position:center;
      }
  </style>

</head>
<body>
<form id="loginform" method="POST" action="index.php">
	<input type="hidden" id="lgusername" name="username" value="" />
</form>
<div class="container">
<nav class="navbar ">
<a class="navbar-brand" href="#">Segment: <?=$taskdescript?></a>
  <span class="navbar-text float-xs-left">
	Created by Aaron Lee 2016, 2017, logged in as <span id="loggedin"></span>. <a href="#" onClick="clearCookie()">Logout</a>
  </span>
</nav>

<div class="row">
	<div class="col-md-2">
		<div class="btn-group" role="group" >
		    <input class="btn btn-secondary btn-sm" type="button" value="Undo" id="undobtn" />
		    <input class="btn btn-secondary btn-sm" type="button" value="Redo" id="redobtn" />
		</div>
	</div>
	<div class="col-md-1 text-xs-right">
		<input class="btn btn-danger  btn-sm" type="button" value="Clear all areas" id="clr" />
	</div>
</div>
<div class="row" style="margin-top:5px">
	<div class="col-md-6">
<?php if ($id == null) { ?>
<h4>No images left!</h4>
<?php } else { ?>
<h4><?=$left?> images left</h4>
		<div id="container">
		    <canvas id="imageView">
			<p>Unfortunately, your browser is currently unsupported by our web application. We are sorry for the inconvenience. Please use one of the supported browsers listed below.</p>
			<p>Supported browsers: <a href="http://chrome.google.com">Chrome</a>, <a href="http://www.opera.com">Opera</a>, <a href="http://www.mozilla.com">Firefox</a>, <a href="http://www.apple.com/safari">Safari</a>, and <a href="http://www.konqueror.org">Konqueror</a>.</p>
		    </canvas>
		</div>
<?php } ?>
	</div>
	<div class="col-md-6">
		<div id="instructions">
		<h4>Segment <?=$taskdescipt?></h4>
			<p>Click and drag using your mouse to outline the areas of interest. If no such pathology is present then just click submit.</p>
		</div>
		<div id="submit">
			<form method="POST" action="index.php">
			<input type="hidden" id="id" name="id" value="<?=$id?>" />
			<input type="hidden" id="username" name="username" value="" />
			<input type="hidden" id="pngfile" name="pngfile" value="" />
			<input type="hidden" id="data" name="data" value="" />
			<input type="hidden" id="totaltime" name="totaltime" value="" />
			<input type="hidden" id="imgurl" name="imgurl" value="" />
			Comments:
			<textarea class="form-control" name="comments" id="comments" rows="3"></textarea>
			<br/>
			<input class="btn btn-primary" type="submit" size="23" value="Submit" />
			</form>
			<br/>
		</div>
		<div>Debug: Image id: <a href="<?=$imgfile?>"><?=$id?></a> <br/> </div>
		<div>Stats: <br/><span id="statsid" ></span> </div>
		<div>Examples:<br/><div id="examples">
<?php

foreach ($EXAMPLES[$task] as $img) {
?>
<img style="width:40%" src="examples/<?=$img?>" />
<?php
}
?>
		</div></div>
	</div>
</div>
<div id="trash"></div> 
</div> <!--container -->
<script type='text/javascript'>//<![CDATA[ 

function clearCookie() {
    document.cookie = "username=;Path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;"
    window.location.href = "index.php";
}

function setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";Path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function checkCookie() {
    console.log(document.cookie);
    var user=getCookie("username");
    console.log(document.cookie);
    if (user != "") {
        //alert("Welcome again " + user);
	<?php if (! ((isset($_POST["username"]) && !empty($_POST["username"]))) ) { ?>
          $("#lgusername").val(user);
	  $("#loginform").submit();
	<?php } ?>
    } else {
        //alert("Getting new" + user);
       while (user == "") {
          user = prompt("Please enter your name:","");
          if (user != "" && user != null) {
              setCookie("username", user, 30);
          }
          var user=getCookie("username");
          $("#lgusername").val(user);
	  $("#loginform").submit();
       }
    }
}

$(window).load(function(){
    if (<?php if ($id == null) { print "true"; } else { print "false"; } ?>) {
    checkCookie();
    $("#loggedin").html(getCookie("username"));
	    return;
    }
init("<?=$imgfile?>");
// vim:set spell spl=en fo=wan1croql tw=80 ts=2 sw=2 sts=2 sta et ai cin fenc=utf-8 ff=unix:
});//]]>  

</script>

</body>


</html>

