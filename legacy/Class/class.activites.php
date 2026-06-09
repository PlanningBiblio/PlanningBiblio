<?php
/**
Description :
Classe activites : contient les fonctions de recherches des activites
Page appelée par les pages du dossier activites
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé

class activites
{
    public $id;
    public $elements=array();
    public $deleted;
    public $CSRFToken;

    public function __construct()
    {
    }

    public function delete(): void
    {
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("activites", array("supprime"=>"SYSDATE"), array("id"=>$this->id));
    }

    public function fetch(): void
    {
        $activites=array();
        $db=new db();
        $networkId = $_SESSION['_sf2_attributes']['networkId'] ?? $_SESSION['networkId'] ?? 1;
        if ($this->deleted) {
            $db->select2("activites", "*", ['network_id' => $networkId]);
        } else {
            $db->select2("activites", null, ["supprime"=>null, 'network_id' => $networkId]);
        }
      
        if ($db->result) {
            $activites=$db->result;
        }
    
        usort($activites, "cmp_nom");
    
        $tmp=array();
        foreach ($activites as $elem) {
            $tmp[$elem['id']]=$elem;
        }
        $activites=$tmp;
        $this->elements=$activites;
    }
}
