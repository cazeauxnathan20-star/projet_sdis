<?php


class Pompier
{
    private $nom;
    private $prenom;
    private $numeroBip;

    
    public function __construct($nom, $prenom, $numeroBip)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->numeroBip = $numeroBip;
    }

   
    public function getNumeroBip()
    {
        return $this->numeroBip;
    }

    public function getNom()
    {
        return $this->nom;
    }

   
    public function getPrenom()
    {
        return $this->prenom;
    }

    
    public function getStatut($unePeriode)
    {
        return $unePeriode->getStatut($this);
    }

    
    public function missionner($unePeriode)
    {
        $unePeriode->missionner($this);
    }
}