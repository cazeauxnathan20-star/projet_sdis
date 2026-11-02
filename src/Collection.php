<?php


class Collection
{
    private $elements;

    
    public function __construct()
    {
        $this->elements = array();
    }

    
    public function cardinal()
    {
        return count($this->elements);
    }

    public function contient($unObjet)
    {
        return $this->index($unObjet) != -1;
    }

   
    public function index($unObjet)
    {
        for ($i = 0; $i < count($this->elements); $i++) {
            if ($this->elements[$i] === $unObjet) {
                return $i;
            }
        }

        return -1;
    }

    
    public function extraire($unIndex)
    {
        return $this->elements[$unIndex];
    }

   
    public function ajouter($unObjet)
    {
        $this->elements[] = $unObjet;
    }

   
    public function enlever($unObjet)
    {
        $unIndex = $this->index($unObjet);

        if ($unIndex != -1) {
            unset($this->elements[$unIndex]);
            $this->elements = array_values($this->elements);
        }
    }
}