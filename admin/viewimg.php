<?php
require("../config.php");

$id = $_GET["id"];
$id = intval($id);

$res = $mysqli_connection->query("SELECT image_base64 FROM $TABLE WHERE id = $id");
$row = $res->fetch_row();
$data = base64_decode($row[0]);

$im = imagecreatefromstring($data);
if ($im !== false) {
    header('Content-Type: image/png');
    imagepng($im);
    imagedestroy($im);
}
else {
    echo 'An error occurred.';
}
?>
