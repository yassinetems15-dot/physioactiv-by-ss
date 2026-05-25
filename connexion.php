<?php
$host = "localhost";
$port = "3307";
$user = "root";
$password = "";
$database = "cabinet_kine";

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
  die("Erreur de connexion : " . mysqli_connect_error());
}
?>