<?php
/**
 * Created by PhpStorm.
 * User: F1L
 * Date: 14.03.2019
 * Time: 13:14
 */
include('config.php');
$connection = connect($server, $database, $user, $password);
function connect($server, $database, $user, $password)
{
    try {
        $connection = new PDO("mysql:host=$server;dbname=$database", $user, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {    
        echo "Connection failed: " . $e->getMessage();
    }
    return $connection;
}
?>