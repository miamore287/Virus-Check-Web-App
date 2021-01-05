<?php

require_once 'loginFinal.php';
$connection = new mysqli($hn, $un, $pw, $db);
if($connection->connect_error)die(mysql_fatal_error("Error");

$query = "CREATE TABLE usersTable (
	username VARCHAR(100) NOT NULL,
	password VARCHAR(100) NOT NULL,
	PRIMARY KEY(id)
)";

$query = "CREATE TABLE malwareTable (
	name VARCHAR(100) NOT NULL,
	virus VARCHAR(100) NOT NULL,
	PRIMARY KEY(id)
)";
$stmt = $connection->prepare('INSERT INTO malwareTable VALUES(?,?)');
$stmt->bind_param('ss',$name, $virus);

$name = 'Trojan';
$virus = 'KPTROJAN123VCYWZ456U';

$stmt->execute();

$connection->close();
?>