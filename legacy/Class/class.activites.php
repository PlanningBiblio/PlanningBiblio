<?php
/**
Description :
Classe activites : contient les fonctions de recherches des activites
Page appelée par les pages du dossier activites
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé

class activites
{
    public $id=null;
    public $elements=array();
    public $deleted=null;
    public $CSRFToken = null;

    public function __construct()
    {
    }

    public function delete()
    {
        $db=new db();
        $db->CSRFToken = $this->CSRFToken;
        $db->update("activites", array("supprime"=>"SYSDATE"), array("id"=>$this->id));
    }

    public function fetch()
    {
        $activites=array();
        $db=new db();
        if ($this->deleted) {
            $db->select2("activites");
        } else {
            $db->select2("activites", null, array("supprime"=>null));
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
