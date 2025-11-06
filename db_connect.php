<?php
$config = include('config.php');

$con = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
