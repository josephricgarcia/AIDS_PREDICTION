<?php
    $database = 'aids_ps';
    $port = '3306';
    $username = "root";
    $password = "";
    $hostname = "localhost";
    $dbhandle = mysqli_connect($hostname, $username, $password, $database, $port) or die("Unable to connect to MySQL");
echo "";
    $selected = mysqli_select_db($dbhandle, $database) or die("Could not connect to database");


?>