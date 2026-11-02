<?php


class Periode
{
    private $laDate;
    private $laTranche;
    private $enMission;
    private $auTravail;
    private $disponible;

   
    public function __construct($uneDate, $uneTranche)
    {
        $this->laDate = $uneDate;
        $this->laTranche = $uneTranche;
        $this->enMission = new Collection();
        $this->auTravail = new Collection();
        $this->disponible = new Collection();
    }

    public function getStatut($unPompier)
    {
        if ($this->enMission->contient($unPompier)) {
            return 'm';
        }

        if ($this->auTravail->contient($unPompier)) {
            return 't';
        }

        if ($this->disponible->contient($unPompier)) {
            return 'd';
        }

        return '';
    }

    public function missionner($unPompier)
    {
        $this->disponible->enlever($unPompier);
        $this->auTravail->enlever($unPompier);
        $this->enMission->ajouter($unPompier);
    }

   
    public function selectEquipe($nbPompiers)
    {
        $uneEquipe = new Collection();

        for ($i = 0; $i < $this->disponible->cardinal() && $uneEquipe->cardinal() < $nbPompiers; $i++) {
            $uneEquipe->ajouter($this->disponible->extraire($i));
        }

        for ($i = 0; $i < $this->auTravail->cardinal() && $uneEquipe->cardinal() < $nbPompiers; $i++) {
            $uneEquipe->ajouter($this->auTravail->extraire($i));
        }

        return $uneEquipe;
    }

    
    public function ajouterDisponible($unPompier)
    {
        $this->disponible->ajouter($unPompier);
    }

    public function ajouterAuTravail($unPompier)
    {
        $this->auTravail->ajouter($unPompier);
    }

    public function ajouterEnMission($unPompier)
    {
        $this->enMission->ajouter($unPompier);
    }
}