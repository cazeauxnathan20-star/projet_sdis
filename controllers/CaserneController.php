<?php

class CaserneController
{
    private $repository;

    public function __construct(PompierRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $lignePeriode = $this->repository->trouverPeriode();
        if (!$lignePeriode) {
            return array('erreur' => null, 'periode' => null, 'etapes' => array());
        }

        $periode = new Periode($lignePeriode['datePeriode'], $lignePeriode['idTranche']);
        $caserne = new Caserne();
        $pompiers = array();

        foreach ($this->repository->trouverPompiersPourPeriode(
            $lignePeriode['idTranche'],
            $lignePeriode['datePeriode']
        ) as $ligne) {
            $pompier = new Pompier($ligne['nom'], $ligne['prenom'], $ligne['numeroBip']);
            $caserne->ajouterPompier($pompier);
            $pompiers[] = $pompier;

            if ($ligne['idDisponibilite'] == 3) {
                $periode->ajouterDisponible($pompier);
            } elseif ($ligne['idDisponibilite'] == 2) {
                $periode->ajouterAuTravail($pompier);
            }
        }

        $etapes = array();
        $statuts = $this->prendreStatuts($pompiers, $periode);
        $etapes[] = array(
            'titre' => 'Étape A : état initial',
            'pompiers' => $pompiers,
            'statutsAvant' => $statuts,
            'statutsApres' => $statuts
        );
        $etapes[] = $this->simulerAppel($caserne, $periode, $pompiers, 3, 'Étape B : appel d’une équipe de 3');
        $etapes[] = $this->simulerAppel($caserne, $periode, $pompiers, 10, 'Étape C : appel d’une équipe de 10');
        $etapes[] = $this->simulerAppel($caserne, $periode, $pompiers, 5, 'Étape D : troisième appel de 5');

        return array(
            'erreur' => null,
            'periode' => $lignePeriode,
            'etapes' => $etapes
        );
    }

    private function simulerAppel($caserne, $periode, $pompiers, $nombreDemande, $titre)
    {
        $statutsAvant = $this->prendreStatuts($pompiers, $periode);
        ob_start();
        $nombreAppele = $caserne->appelEquipe($periode, $nombreDemande);
        $bips = ob_get_clean();

        return array(
            'titre' => $titre,
            'pompiers' => $pompiers,
            'statutsAvant' => $statutsAvant,
            'statutsApres' => $this->prendreStatuts($pompiers, $periode),
            'nombreDemande' => $nombreDemande,
            'nombreAppele' => $nombreAppele,
            'bips' => $bips
        );
    }

    private function prendreStatuts($pompiers, $periode)
    {
        $statuts = array();
        foreach ($pompiers as $index => $pompier) {
            $statuts[$index] = $pompier->getStatut($periode);
        }

        return $statuts;
    }
}
