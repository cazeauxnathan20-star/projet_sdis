<?php

class PompierRepository
{
    private $connexion;

    public function __construct(PDO $connexion)
    {
        $this->connexion = $connexion;
    }

    public function trouverPeriode()
    {
        $requete = $this->connexion->prepare(
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
        $requete->execute(array(
            'deGarde' => 1,
            'disponible' => 3,
            'auTravail' => 2
        ));

        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    public function trouverPompiersPourPeriode($idTranche, $datePeriode)
    {
        $requete = $this->connexion->prepare(
            'SELECT DISTINCT v.id, v.nom, v.prenom, v.numeroBip, a.idDisponibilite
             FROM VOLONTAIRE v
             JOIN AVOIR_ACTIVITE a ON a.idVolontaire = v.id
             WHERE a.idTranche = :idTranche AND a.datePeriode = :datePeriode
               AND a.deGarde = :deGarde AND a.idDisponibilite IN (:disponible, :auTravail)
             ORDER BY a.idDisponibilite DESC, v.nom ASC, v.prenom ASC'
        );
        $requete->execute(array(
            'idTranche' => $idTranche,
            'datePeriode' => $datePeriode,
            'deGarde' => 1,
            'disponible' => 3,
            'auTravail' => 2
        ));

        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }
}
