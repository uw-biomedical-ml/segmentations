<?php
require("config.php");

if (isset($_POST["id"]) && !empty($_POST["id"])) {
	$stmt = $mysqli_connection->prepare("UPDATE data SET username = ?, comments = ?, data_json = ?, image_base64 = ?, ts=NOW()  WHERE id = ?");

	$stmt->bind_param("sssss", $_POST["username"], $_POST["comments"], $_POST["data"], $_POST["pngfile"], $_POST["id"]);
	$stmt->execute();
}

$files = [];
$res = $mysqli_connection->query("SELECT id, filename, username FROM data WHERE username IS NOT NULL AND task = '$TASK' ");
while ($row = $res->fetch_row()) {
	if (!array_key_exists($row[1], $files)) {
		$files[$row[1]] = [];
	}
	array_push($files[$row[1]], $row[2]);
}

# prioritize segmentations that need replicates
$ignore = []; # images that have already been done by user or have full number of replicates
$found = null;
foreach ($files as $file => $users) {
	if (count($users) != $REPLICATES) {
		if ((isset($_POST["username"]) && !empty($_POST["username"]))) {
			$alreadydone = false;
			foreach ($users as $user) {
				if ($user == $_POST["username"]) {
					$alreadydone = true;
					break;
				}
			}
			if (! $alreadydone) {
				$found = $file;
				break;
			} else {
				$ignore[$file] = 1;
			}
		} else {
			$ignore[$file] = 1;
		}
	}
}

# if found from above, load correct jobid, else pick a random image to segment
if ($found != null) {
	$res2 = $mysqli_connection->prepare("SELECT id, filename, task FROM data WHERE username IS NULL AND filename = ? AND task = '$TASK'");
	$res2->bind_param("s", $found);
	$res2->execute();

	$res2->execute();

	$res2->bind_result($id, $file, $task);
	$res2->fetch();
} else {
	$id = null;
	
	$res = $mysqli_connection->query("SELECT id, filename, username FROM data WHERE username IS NULL AND task = '$TASK' ORDER BY rand()");
	while ($row = $res->fetch_row()) {
		if (!array_key_exists($file, $ignore)) {
			$id = $row[0];
			$file = $row[1];
			$task = $row[2];
			break;
		}
	}
}


$imgfile = "images/$file";
$task = strtoupper($task);


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
<script type='text/javascript'>//<![CDATA[ 

function clearCookie() {
    document.cookie = "";
}

function setCookie(cname,cvalue,exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
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
    var user=getCookie("username");
    if (user != "") {
        //alert("Welcome again " + user);
    } else {
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
// Keep everything in anonymous function, called on window load.
//$(window).load(function(){
var canvas, context, tool;
var mousexarr = [];
var mouseyarr = [];
var mousetime = [];
var startTime;
var imgH;
var imgW;
var imgurl;
var debug;

var omnihistory = []
var omnipointer = -1;

function fillPolygon() {
	context.clearRect(0, 0, canvas.width, canvas.height);
	context.fillStyle =  "rgba(255, 0, 255, 0.5)";
	context.strokeStyle =  "rgba(255, 0, 255, 0.5)";
	for (var i = 1 ; i <= omnipointer; i++) {
		context.beginPath();
		var j = 0;
		context.moveTo(omnihistory[i].mousex[j], omnihistory[i].mousey[j]);
		for (j = 1 ; j < omnihistory[i].mousex.length ; j++) {
			context.lineTo(omnihistory[i].mousex[j], omnihistory[i].mousey[j]);
		}
		context.closePath();
		context.fill();
		context.stroke();
	}
	context.beginPath();
	var j = 0;
	context.moveTo(mousexarr[j], mouseyarr[j]);
	for (j = 1 ; j < mousexarr.length ; j++) {
		context.lineTo(mousexarr[j], mouseyarr[j]);
	}
	context.closePath();
	context.fill();
	context.stroke();
}


function saveState() {
	fillPolygon();
	var image = context.getImageData(0,0, imgW, imgH);
	var imageData = image.data;
	for(var i=3; i < imageData.length; i+=4) {
		if (imageData[i-3] == 0 && imageData[i-2] == 0 && imageData[i-1] == 0) {
			imageData[i] == 255;
		}
	}
	image.data = imageData;
	context.putImageData(image, 0, 0);

	omnipointer++;

	var epoch = {};
	epoch.canvasdata = canvas.toDataURL();
	epoch.mousex = mousexarr;
	epoch.mousey = mouseyarr;
	epoch.mousetime = mousetime;
	if (omnipointer > omnihistory.length - 1) {
		omnihistory.push(epoch);
	} else {
		omnihistory[omnipointer] = epoch;
		//destroy the other timeline
		while (omnihistory.length > omnipointer + 1) {
			omnihistory.pop();
		}
	}
	updateDisp();

}

function restoreState(index) {
	var state = omnihistory[index].canvasdata;
	var imgtemp = new Image();
	imgtemp.src = state;

	imgtemp.onload = function() {
		context.clearRect(0, 0, canvas.width, canvas.height);
		context.drawImage(imgtemp, 0, 0);
		updateDisp();
	};
}

function undofunc() {
	if (omnipointer == -1) {
		return;
	}
	omnipointer--;
	if (omnipointer < 0) {
		omnipointer = 0;
		return;
	}
	restoreState(omnipointer);
}

function redofunc() {
	omnipointer++;
	if (omnipointer >  omnihistory.length - 1) {
		omnipointer--;
		return;
	}
	restoreState(omnipointer);
}


function eraseCanvas() {
    var m = confirm("Want to clear?");
    if (m) {
        context.clearRect(0, 0, context.canvas.width, context.canvas.height);
        mousexarr = [];
        mouseyarr = [];
        mousetime = [];
	omnihistory = [];
	omnipointer = -1;
        initCanvas();
    }
}

function initCanvas() {
    //context.font = "bold 20px sans-serif";
    //context.textAlign = "center";
    //context.fillText("Start", paddingx / 2, imgH / 2 + paddingy);
    //context.fillText("Finish", 1.5 * paddingx + imgW, imgH / 2 + paddingy);
    $("#statsid").html("");
    //$("#submit").hide();
}

function init() {
    if (<?php if ($id == null) { print "true"; } else { print "false"; } ?>) {
    checkCookie();
    $("#loggedin").html(getCookie("username"));
	    return;
    }
    // Get image url from params
    //imgurl = "images/"+$.url().param('img');
	imgurl = "<?=$imgfile?>";
    $("#imgurl").val(imgurl);
    
    // Set background URL
    $("#imageView").css('background-image', 'url(' + imgurl + ')');
    var img = new Image;
    $(img).load(function () {
        imgH = img.height;
        imgW = img.width;
        $("#imageView").attr({
            'width': imgW ,
            'height': imgH
        });

        initCanvas();
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

    // Pencil tool instance.
    tool = new tool_pencil();

    // Attach the mousedown, mousemove and mouseup event listeners.
    canvas.addEventListener('mousedown', ev_canvas, false);
    canvas.addEventListener('mousemove', ev_canvas, false);
    canvas.addEventListener('mouseup', ev_canvas, false);

    // Button handlers 
    document.getElementById('clr').onclick = eraseCanvas;
    document.getElementById('undobtn').onclick = undofunc;
    document.getElementById('redobtn').onclick = redofunc;
    checkCookie();
    $("#loggedin").html(getCookie("username"));
    $("#username").val(getCookie("username"));
}

// This painting tool works like a drawing pencil which tracks the mouse 
// movements.
function tool_pencil() {
    var tool = this;
    this.started = false;

    // This is called when you start holding down the mouse button.
    // This starts the pencil drawing.
    this.mousedown = function (ev) {
	    if (tool.started) {
		    tool.mousemove(ev);
		    ev._x = mousexarr[0];
		    ev._y = mouseyarr[0];
		    tool.mousemove(ev);
		    tool.started = false;
		    var d = new Date();
		    mousexarr.push(ev._x );
		    mouseyarr.push(ev._y );
		    mousetime.push(d.getTime() - startTime);
		    saveState();
		    //updateDisp();

	    } else {
		if (omnihistory.length == 0) saveState();
		context.strokeStyle = '#00ff00';
		context.beginPath();
		context.moveTo(ev._x, ev._y);
		tool.started = true;
		mousexarr = [ev._x ];
		mouseyarr = [ev._y ];
		mousetime = [0];
		var d = new Date();
		startTime = d.getTime();
	    }
    };

    // This function is called every time you move the mouse. Obviously, it only 
    // draws if the tool.started state is set to true (when you are holding down 
    // the mouse button).
    this.mousemove = function (ev) {
        if (tool.started) {
            context.lineTo(ev._x, ev._y);
            context.stroke();
            var d = new Date();
            mousexarr.push(ev._x );
            mouseyarr.push(ev._y );
            mousetime.push(d.getTime() - startTime);
        }
    };

    // This is called when you release the mouse button.
    this.mouseup = function (ev) {
        if (tool.started) {
            tool.mousemove(ev);
            ev._x = mousexarr[0];
            ev._y = mouseyarr[0];
            tool.mousemove(ev);
            tool.started = false;
            var d = new Date();
            mousexarr.push(ev._x );
            mouseyarr.push(ev._y );
            mousetime.push(d.getTime() - startTime);
	    saveState();
	    //updateDisp();
        }
    };
}
	
	function updateDisp() {
        var totaltime = 0;
		var outtext = "<ul class='list-group row'>";
		var tooutput = [];
        for (var i = 1; i <= omnipointer; i++) {
		outtext += "<li  class='list-group-item col-xs-4'>Area " + i + ": <span style='color:red'>" + ( Math.round( omnihistory[i].mousetime[omnihistory[i].mousetime.length - 1] / 100) / 10 ) + " s</span></li>\n";
		totaltime += omnihistory[i].mousetime[omnihistory[i].mousetime.length - 1];
		var datum = {};
		datum.mousex = omnihistory[i].mousex;
		datum.mousey = omnihistory[i].mousey;
		datum.mousetime = omnihistory[i].mousetime;
		tooutput.push(datum);
        }
		var outtext = "";
		var allgood = 0;
		outtext += "</ul> <ul><li>Number of Areas: <span style='color:green'>" + omnipointer + " </span> </li>";
		outtext += "<li> Avgerage time spent per area: <span style='color:red'>" + Math.round(totaltime / ( omnipointer * 100)) / 10 + " s </span></li></ul>";
		$("#statsid").html(outtext);

                $("#pngfile").val(canvas.toDataURL().split(";base64,")[1]);
                $("#data").val($.toJSON(tooutput));
                $("#totaltime").val(totaltime / 1000);
                //$("#submit").show();
	}

// The general-purpose event handler. This function just determines the mouse 
// position relative to the canvas element.
function ev_canvas(ev) {
    if (ev.layerX || ev.layerX == 0) { // Firefox
        ev._x = ev.layerX;
        ev._y = ev.layerY;
    } else if (ev.offsetX || ev.offsetX == 0) { // Opera
        ev._x = ev.offsetX;
        ev._y = ev.offsetY;
    }
    if (tool.started) {
		//updateDisp();
    }

    // Call the event handler of the tool.
    var func = tool[ev.type];
    if (func) {
        func(ev);
    }
}

$(window).load(function(){
init();


// vim:set spell spl=en fo=wan1croql tw=80 ts=2 sw=2 sts=2 sta et ai cin fenc=utf-8 ff=unix:
});//]]>  

</script>


</head>
<body>
<form id="loginform" method="POST" action="index.php">
	<input type="hidden" id="lgusername" name="username" value="" />
</form>
<div class="container">
<nav class="navbar ">
<a class="navbar-brand" href="#">Segment: <?=$TASK?></a>
  <span class="navbar-text float-xs-left">
	Created by Aaron Lee 2016, 2017, logged in as <span id="loggedin"></span>
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
	<div class="col-md-9">
<?php if ($id == null) { ?>
<h4>No images left!</h4>

<?php } else { ?>
		<div id="container">
		    <canvas id="imageView">
			<p>Unfortunately, your browser is currently unsupported by our web application. We are sorry for the inconvenience. Please use one of the supported browsers listed below.</p>
			<p>Supported browsers: <a href="http://chrome.google.com">Chrome</a>, <a href="http://www.opera.com">Opera</a>, <a href="http://www.mozilla.com">Firefox</a>, <a href="http://www.apple.com/safari">Safari</a>, and <a href="http://www.konqueror.org">Konqueror</a>.</p>
		    </canvas>
		</div>
<?php } ?>
	</div>
	<div class="col-md-3">
		<div id="instructions">
		<h4>Segment <?=$TASK?></h4>
			<p>Click and drag using your mouse to outline the areas of interest. Please carefully study the examples below. If no such pathology is present then just click submit. If it is an <b>invalid image</b> please type into comment box "INVALID." </p>
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
			<a href="index.php"><button class="btn btn-secondary">Get new image</button></a>
			<br/>
		</div>
		<div>Debug: Image id: <a href="<?=$imgfile?>"><?=$id?></a> <br/> </div>
		<div>Stats: <br/><span id="statsid" ></span> </div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<h4>Examples</h4>
		<hr/>
<img src="example1.jpg"/> <br/>
	</div>
</div>
<div id="trash"></div> 
</div> <!--container -->
</body>


</html>

