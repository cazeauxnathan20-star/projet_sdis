<?php

$echapper = function ($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
};

$texteStatut = function ($statut) {
    if ($statut === 'd') {
        return 'Disponible';
    }
    if ($statut === 't') {
        return 'Au travail';
    }
    if ($statut === 'm') {
        return 'En mission';
    }

    return 'Inconnu';
};

$afficherEtape = function ($etape) use ($echapper, $texteStatut) {
    ?>
    <section class="etape">
        <h2><?php echo $echapper($etape['titre']); ?></h2>
        <table>
            <thead><tr><th>Nom</th><th>Prénom</th><th>N° bip</th><th>Statut avant</th><th>Statut après</th></tr></thead>
            <tbody>
            <?php foreach ($etape['pompiers'] as $index => $pompier) { ?>
                <tr>
                    <td><?php echo $echapper($pompier->getNom()); ?></td>
                    <td><?php echo $echapper($pompier->getPrenom()); ?></td>
                    <td><?php echo $echapper($pompier->getNumeroBip()); ?></td>
                    <td><span class="statut statut-<?php echo $echapper($etape['statutsAvant'][$index]); ?>"><?php echo $echapper($texteStatut($etape['statutsAvant'][$index])); ?></span></td>
                    <td><span class="statut statut-<?php echo $echapper($etape['statutsApres'][$index]); ?>"><?php echo $echapper($texteStatut($etape['statutsApres'][$index])); ?></span></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </section>
    <?php
};

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

<?php if ($donnees['erreur'] !== null) { ?>
    <p class="message erreur">Impossible d’accéder à la base de données. Vérifiez que MySQL est démarré et que le port de connexion est correct.</p>
    <p>Détail : <?php echo $echapper($donnees['erreur']); ?></p>
<?php } elseif (!$donnees['periode']) { ?>
    <p class="message">Aucune période ne contient à la fois des pompiers disponibles et des pompiers au travail.</p>
<?php } else { ?>
    <p class="periode">
        Période choisie : <strong><?php echo $echapper($donnees['periode']['datePeriode']); ?></strong>,
        tranche <strong><?php echo $echapper($donnees['periode']['idTranche']); ?></strong>.
        Pompiers de garde : <?php echo (int) $donnees['periode']['nbPompiers']; ?>,
        dont <?php echo (int) $donnees['periode']['nbDisponibles']; ?> disponibles et
        <?php echo (int) $donnees['periode']['nbAuTravail']; ?> au travail.
    </p>

    <?php foreach ($donnees['etapes'] as $indexEtape => $etape) { ?>
        <?php if ($indexEtape === 0) { ?>
            <?php $afficherEtape($etape); ?>
        <?php } else { ?>
            <section class="etape">
                <h2><?php echo $echapper($etape['titre']); ?></h2>
                <p><?php echo $indexEtape === 1 ? 'Nombre de pompiers appelés :' : 'Nombre réellement appelé :'; ?>
                    <strong><?php echo (int) $etape['nombreAppele']; ?></strong>
                </p>
                <p>Bips appelés :</p>
                <pre class="bips"><?php echo $echapper($etape['bips']); ?></pre>
                <?php if ($indexEtape === 1 && $etape['nombreAppele'] < 3) { ?>
                    <p>Équipe incomplète : il n’y avait pas trois pompiers mobilisables.</p>
                <?php } elseif ($indexEtape === 2 && $etape['nombreAppele'] < 10) { ?>
                    <p>Équipe incomplète : tous les postes demandés n’ont pas pu être pourvus.</p>
                <?php } elseif ($indexEtape === 3 && $etape['nombreAppele'] === 0) { ?>
                    <p>Résultat attendu : aucun pompier n'est resélectionné, car tous sont en mission.</p>
                <?php } elseif ($indexEtape === 3) { ?>
                    <p>Il reste des pompiers mobilisables après les étapes B et C. Les deux appels précédents ne pouvaient en appeler que 13 au maximum.</p>
                <?php } ?>
            </section>
            <?php
            $etapeStatuts = $etape;
            $etapeStatuts['titre'] = 'Statuts après l’étape ' . chr(65 + $indexEtape);
            $afficherEtape($etapeStatuts);
            ?>
        <?php } ?>
    <?php } ?>
<?php } ?>
</main>
</body>
</html>
