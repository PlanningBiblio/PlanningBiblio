<?php

use App\Model\Agent;
use App\Model\Access;
use PHPUnit\Framework\TestCase;

class AgentTest extends TestCase
{
    public function testAdd() {
        global $entityManager;
        $agent = $entityManager->find(Agent::class, 1);

        $this->assertEquals('Administrateur', $agent->nom());
        $this->assertEquals('admin', $agent->login());
    }

    public function testCanAccess() {

        $access = new Access();
        $access->groupe_id(99);

        $access_bad = new Access();
        $access_bad->groupe_id(201);

        $agent = $this->createAgent();

        $this->assertTrue($agent->can_access(array($access)));
        $this->assertFalse($agent->can_access(array($access_bad)));
    }

    private function createAgent($data = array()) {
        $default_postes = '["1","2","3","4","5","6","7","8","9","10","11","12"]';

        $agent = new Agent();
        $agent->nom(isset($data['nom']) ? $data['nom'] : 'Kirby');
        $agent->prenom(isset($data['prenom']) ? $data['prenom'] : 'Linford');
        $agent->mail(isset($data['mail']) ? $data['mail'] : 'linford.kirby@foo.example');
        $agent->statut(isset($data['statut']) ? $data['statut'] : 'Biblioth&eacute;caire');
        $agent->categorie(isset($data['categorie']) ? $data['categorie'] : 'Titulaire');
        $agent->service(isset($data['service']) ? $data['service'] : 'P&ocirc;le informatique');
        $agent->arrivee(isset($data['arrivee']) ? $data['arrivee'] : new DateTime('2021-01-01'));
        $agent->depart(isset($data['depart']) ? $data['depart'] : new DateTime('2999-01-01'));
        $agent->postes(isset($data['postes']) ? $data['postes'] : $default_postes);
        $agent->actif(isset($data['actif']) ? $data['actif'] : 'Actif');
        $agent->login(isset($data['login']) ? $data['login'] : 'lkirby');
        $agent->droits(isset($data['droits']) ? $data['droits'] : array('99', '100'));
        $agent->password(isset($data['password']) ? $data['password'] : '$2y$10$789zzEm');
        $agent->commentaires(isset($data['commentaires']) ? $data['commentaires'] : 'foo');
        $agent->last_login(isset($data['last_login']) ? $data['last_login'] : new DateTime('2021-03-10 13:21:11'));
        $agent->heures_hebdo(isset($data['heures_hebdo']) ? $data['heures_hebdo'] : '');
        $agent->heures_travail(isset($data['heures_travail']) ? $data['heures_travail'] : '35');
        $agent->sites(isset($data['sites']) ? $data['sites'] : '["1"]');
        $agent->temps(isset($data['temps']) ? $data['temps'] : '');
        $agent->informations(isset($data['informations']) ? $data['informations'] : '');
        $agent->recup(isset($data['recup']) ? $data['recup'] : 0);
        $agent->supprime(isset($data['supprime']) ? $data['supprime'] : 0);
        $agent->mails_responsables(isset($data['mails_responsables']) ? $data['mails_responsables'] : '');
        $agent->matricule(isset($data['matricule']) ? $data['matricule'] : '');
        $agent->check_hamac(isset($data['check_hamac']) ? $data['check_hamac'] : 0);

        global $entityManager;
        $entityManager->persist($agent);
        $entityManager->flush();

        return $agent;
    }
}
