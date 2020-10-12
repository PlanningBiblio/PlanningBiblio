<?php


ini_set("display_errors", 0);

// Includes
require_once(__DIR__ . '/../../init_ajax.php');

use Symfony\Component\HttpFoundation\Request;

use App\Model\HiddenSites;

$request = Request::createFromGlobals();
$perso_id = $request->get('login_id');
$op = $request->get('op');
$site = $request->get('site');

$entityManager = $GLOBALS['entityManager'];
$hidden_for_agent = $entityManager
    ->getRepository(HiddenSites::class)
    ->findOneBy(array('perso_id' => $perso_id));

$hidden_sites = array();
if ($hidden_for_agent) {
  $hidden_sites = $hidden_for_agent->hidden_sites() ?? '';
  $hidden_sites = $hidden_sites ? explode(';', $hidden_sites) : array();
}

if ($op == 'add' && !in_array($site, $hidden_sites) ) {
    $hidden_sites[] = $site;
}

if ($op == 'remove') {
    if (($key = array_search($site, $hidden_sites)) !== false) {
        unset($hidden_sites[$key]);
    }
}

if (!$hidden_for_agent) {
    $hidden_for_agent = new HiddenSites();
    $hidden_for_agent->perso_id($perso_id);
}

$hidden_sites = implode(';', $hidden_sites);
$hidden_for_agent->hidden_sites($hidden_sites);
$entityManager->persist($hidden_for_agent);
$entityManager->flush();

echo json_encode("");
