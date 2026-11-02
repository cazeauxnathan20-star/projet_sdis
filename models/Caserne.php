<?php


class Caserne
{
    private $lesPompiers;

   
    public function __construct()
    {
        $this->lesPompiers = new Collection();
    }

   
    public function ajouterPompier($unPompier)
    {
        $this->lesPompiers->ajouter($unPompier);
    }

    
    private function appeler($numeroBip)
    {
        echo "Appel du bip : " . $numeroBip . PHP_EOL;
    }

    
    public function appelEquipe($unePeriode, $nbPompiers)
    {
        $uneEquipe = $unePeriode->selectEquipe($nbPompiers);

        for ($i = 0; $i < $uneEquipe->cardinal(); $i++) {
            $unPompier = $uneEquipe->extraire($i);
            $unPompier->missionner($unePeriode);
            $this->appeler($unPompier->getNumeroBip());
        }

        return $uneEquipe->cardinal();
    }
}