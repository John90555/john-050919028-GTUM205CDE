<?php
$server = "localhost:3306";
$user = "root";
$pwd = "";
$db = "recruitment";

$con = new mysqli($server, $user, $pwd, $db);
header("Content-Type: text/html");
if($con->connect_errno) {
response(400,"error","database connecting error");
exit();
}
$currency = "GHC";
$site = $_SERVER['HTTP_HOST'];