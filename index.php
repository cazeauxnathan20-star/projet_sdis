<?php

require_once __DIR__ . '/models/Collection.php';
require_once __DIR__ . '/models/Pompier.php';
require_once __DIR__ . '/models/Periode.php';
require_once __DIR__ . '/models/Caserne.php';
require_once __DIR__ . '/models/PompierRepository.php';
require_once __DIR__ . '/controllers/CaserneController.php';

try {
    $connexion = require __DIR__ . '/config/database.php';
    $controller = new CaserneController(new PompierRepository($connexion));
    $donnees = $controller->index();
} catch (PDOException $erreur) {
    $donnees = array(
        'erreur' => $erreur->getMessage(),
        'periode' => null,
        'etapes' => array()
    );
}

require __DIR__ . '/views/caserne.php';
