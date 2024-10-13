<?php
    $host = "localhost";
    $dbname = "sis";
    $user = "root";
    $pass = "";

    try
    {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;",$user,$pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        echo"Connection Successful";
    }

    catch(PDOException $e)
    {
        die("Could not connect to the database".$e->getMessage());
    }
?>