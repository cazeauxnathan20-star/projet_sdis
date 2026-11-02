<?php

$host = getenv('SDIS_DB_HOST') ?: 'localhost';
$port = getenv('SDIS_DB_PORT') ?: '3307';
$database = getenv('SDIS_DB_NAME') ?: 'sdis';
$username = getenv('SDIS_DB_USER') ?: 'root';
$password = getenv('SDIS_DB_PASSWORD') ?: '';

$connexion = new PDO(
    "mysql:host=$host;port=$port;dbname=$database;charset=utf8",
    $username,
    $password
);
$connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

return $connexion;
