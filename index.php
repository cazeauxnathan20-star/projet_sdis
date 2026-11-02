<?php

require_once __DIR__ . '/src/Collection.php';
require_once __DIR__ . '/src/Pompier.php';
require_once __DIR__ . '/src/Periode.php';
require_once __DIR__ . '/src/Caserne.php';


function texteStatut($unStatut)
{
    if ($unStatut == 'd') {
        return 'Disponible';
    }

    if ($unStatut == 't') {
        return 'Au travail';
    }

    if ($unStatut == 'm') {
        return 'En mission';
    }

    return 'Inconnu';
}


function prendreStatuts($lesPompiers, $unePeriode)
{
    $lesStatuts = array();

    foreach ($lesPompiers as $i => $unPompier) {
        $lesStatuts[$i] = $unPompier->getStatut($unePeriode);
    }

    return $lesStatuts;
}


function afficherEtape($unTitre, $lesPompiers, $statutsAvant, $statutsApres)
{
    echo '<section class="etape">';
    echo '<h2>' . htmlspecialchars($unTitre, ENT_QUOTES, 'UTF-8') . '</h2>';
    echo '<table>';
    echo '<thead><tr><th>Nom</th><th>Prénom</th><th>N° bip</th><th>Statut avant</th><th>Statut après</th></tr></thead>';
    echo '<tbody>';

    foreach ($lesPompiers as $i => $unPompier) {
        $statutAvant = $statutsAvant[$i];
        $statutApres = $statutsApres[$i];

        echo '<tr>';
        echo '<td>' . htmlspecialchars($unPompier->getNom(), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($unPompier->getPrenom(), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($unPompier->getNumeroBip(), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td><span class="statut statut-' . htmlspecialchars($statutAvant, ENT_QUOTES, 'UTF-8') . '">' . texteStatut($statutAvant) . '</span></td>';
        echo '<td><span class="statut statut-' . htmlspecialchars($statutApres, ENT_QUOTES, 'UTF-8') . '">' . texteStatut($statutApres) . '</span></td>';
        echo '</tr>';
    }

    echo '</tbody></table></section>';
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test des équipes de pompiers</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; }
        th, td { padding: 5px; }
        .statut-d { color: green; }
        .statut-t { color: orange; }
        .statut-m { color: red; }
    </style>
</head>
<body>
<main>
    <h1>Simulation d'appel d'équipe</h1>
    <p class="regle">Priorité : pompiers disponibles, puis pompiers au travail. Un pompier en mission n'est jamais resélectionné.</p>

<?php
try {
    $connexion = new PDO('mysql:host=localhost;port=3307;dbname=sdis;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $requetePeriode = $connexion->prepare(
        'SELECT a.idTranche, a.datePeriode,
                COUNT(DISTINCT v.id) AS nbPompiers,
                COUNT(DISTINCT CASE WHEN a.idDisponibilite = 3 THEN v.id END) AS nbDisponibles,
                COUNT(DISTINCT CASE WHEN a.idDisponibilite = 2 THEN v.id END) AS nbAuTravail
         FROM VOLONTAIRE v
         JOIN AVOIR_ACTIVITE a ON a.idVolontaire = v.id
         WHERE a.deGarde = :deGarde AND a.idDisponibilite IN (:disponible, :auTravail)
         GROUP BY a.idTranche, a.datePeriode
         HAVING COUNT(DISTINCT CASE WHEN a.idDisponibilite = 3 THEN v.id END) > 0
            AND COUNT(DISTINCT CASE WHEN a.idDisponibilite = 2 THEN v.id END) > 0
         ORDER BY nbPompiers DESC, a.datePeriode ASC, a.idTranche ASC
         LIMIT 1'
    );
    $requetePeriode->execute(array(
        'deGarde' => 1,
        'disponible' => 3,
        'auTravail' => 2
    ));
    $lignePeriode = $requetePeriode->fetch(PDO::FETCH_ASSOC);

    if ($lignePeriode) {
        $unePeriode = new Periode($lignePeriode['datePeriode'], $lignePeriode['idTranche']);
        $uneCaserne = new Caserne();
        $lesPompiers = array();

        
        $requetePompiers = $connexion->prepare(
            'SELECT DISTINCT v.id, v.nom, v.prenom, v.numeroBip, a.idDisponibilite
             FROM VOLONTAIRE v
             JOIN AVOIR_ACTIVITE a ON a.idVolontaire = v.id
             WHERE a.idTranche = :idTranche AND a.datePeriode = :datePeriode
               AND a.deGarde = :deGarde AND a.idDisponibilite IN (:disponible, :auTravail)
             ORDER BY a.idDisponibilite DESC, v.nom ASC, v.prenom ASC'
        );
        $requetePompiers->execute(array(
            'idTranche' => $lignePeriode['idTranche'],
            'datePeriode' => $lignePeriode['datePeriode'],
            'deGarde' => 1,
            'disponible' => 3,
            'auTravail' => 2
        ));

        while ($ligne = $requetePompiers->fetch(PDO::FETCH_ASSOC)) {
            $unPompier = new Pompier($ligne['nom'], $ligne['prenom'], $ligne['numeroBip']);
            $uneCaserne->ajouterPompier($unPompier);
            $lesPompiers[] = $unPompier;

            if ($ligne['idDisponibilite'] == 3) {
                $unePeriode->ajouterDisponible($unPompier);
            } elseif ($ligne['idDisponibilite'] == 2) {
                $unePeriode->ajouterAuTravail($unPompier);
            }
        }
    }
} catch (PDOException $erreur) {
    echo '<p class="message erreur">Impossible d’accéder à la base de données. Vérifiez que MySQL est démarré et que le port de connexion est correct.</p>';
    echo '<p>Détail : ' . htmlspecialchars($erreur->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</main></body></html>';
    exit;
}

if (!$lignePeriode) {
    echo '<p class="message">Aucune période ne contient à la fois des pompiers disponibles et des pompiers au travail.</p>';
    echo '</main></body></html>';
    exit;
}
?>

    <p class="periode">
        Période choisie : <strong><?php echo htmlspecialchars($lignePeriode['datePeriode'], ENT_QUOTES, 'UTF-8'); ?></strong>,
        tranche <strong><?php echo htmlspecialchars($lignePeriode['idTranche'], ENT_QUOTES, 'UTF-8'); ?></strong>.
        Pompiers de garde : <?php echo (int) $lignePeriode['nbPompiers']; ?>,
        dont <?php echo (int) $lignePeriode['nbDisponibles']; ?> disponibles et
        <?php echo (int) $lignePeriode['nbAuTravail']; ?> au travail.
    </p>

<?php
$statutsInitiaux = prendreStatuts($lesPompiers, $unePeriode);
afficherEtape('Étape A : état initial', $lesPompiers, $statutsInitiaux, $statutsInitiaux);


$statutsAvantB = prendreStatuts($lesPompiers, $unePeriode);
ob_start();
$nbAppelesB = $uneCaserne->appelEquipe($unePeriode, 3);
$bipsB = ob_get_clean();
$statutsApresB = prendreStatuts($lesPompiers, $unePeriode);
?>
    <section class="etape">
        <h2>Étape B : appel d'une équipe de 3</h2>
        <p>Nombre de pompiers appelés : <strong><?php echo $nbAppelesB; ?></strong></p>
        <p>Bips appelés :</p>
        <pre class="bips"><?php echo htmlspecialchars($bipsB, ENT_QUOTES, 'UTF-8'); ?></pre>
        <?php if ($nbAppelesB < 3) { echo '<p>Équipe incomplète : il n’y avait pas trois pompiers mobilisables.</p>'; } ?>
    </section>
<?php
afficherEtape('Statuts après l’étape B', $lesPompiers, $statutsAvantB, $statutsApresB);


$statutsAvantC = prendreStatuts($lesPompiers, $unePeriode);
ob_start();
$nbAppelesC = $uneCaserne->appelEquipe($unePeriode, 10);
$bipsC = ob_get_clean();
$statutsApresC = prendreStatuts($lesPompiers, $unePeriode);
?>
    <section class="etape">
        <h2>Étape C : appel d'une équipe de 10</h2>
        <p>Nombre réellement appelé : <strong><?php echo $nbAppelesC; ?></strong></p>
        <p>Bips appelés :</p>
        <pre class="bips"><?php echo htmlspecialchars($bipsC, ENT_QUOTES, 'UTF-8'); ?></pre>
        <?php if ($nbAppelesC < 10) { echo '<p>Équipe incomplète : tous les postes demandés n’ont pas pu être pourvus.</p>'; } ?>
    </section>
<?php
afficherEtape('Statuts après l’étape C', $lesPompiers, $statutsAvantC, $statutsApresC);


$statutsAvantD = prendreStatuts($lesPompiers, $unePeriode);
ob_start();
$nbAppelesD = $uneCaserne->appelEquipe($unePeriode, 5);
$bipsD = ob_get_clean();
$statutsApresD = prendreStatuts($lesPompiers, $unePeriode);
?>
    <section class="etape">
        <h2>Étape D : troisième appel de 5</h2>
        <p>Nombre réellement appelé : <strong><?php echo $nbAppelesD; ?></strong></p>
        <p>Bips appelés :</p>
        <pre class="bips"><?php echo htmlspecialchars($bipsD, ENT_QUOTES, 'UTF-8'); ?></pre>
        <?php if ($nbAppelesD == 0) { ?>
            <p>Résultat attendu : aucun pompier n'est resélectionné, car tous sont en mission.</p>
        <?php } else { ?>
            <p>Il reste des pompiers mobilisables après les étapes B et C. Les deux appels précédents ne pouvaient en appeler que 13 au maximum.</p>
        <?php } ?>
    </section>
<?php
afficherEtape('Statuts après l’étape D', $lesPompiers, $statutsAvantD, $statutsApresD);
?>
</main>
</body>
</html>