<?php

$version=$argv[0];
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../init_entitymanager.php';
require_once __DIR__ . '/../include/function.php';
require_once __DIR__ . '/../plugins/plugins.php';
require_once __DIR__ . '/class.absences.php';
require_once __DIR__ . '/../personnel/class.personnel.php';

use App\Model\AbsenceDocument;

$CSRFToken = CSRFToken();

if (!$config['Absences-DelaiSuppressionDocuments'] || $config['Absences-DelaiSuppressionDocuments'] == 0) {
    logs("Suppression des anciens documents d'absences d&eacute;sactiv&eacute;e", "Absences-DelaiSuppressionDocuments", $CSRFToken);
    exit;
}


$limitdate = new \Datetime();
$limitdate->sub(new DateInterval('P' . $config['Absences-DelaiSuppressionDocuments'] . 'D'));

$qb = $entityManager->createQueryBuilder();
$qb->select('a')
       ->from(AbsenceDocument::class, 'a')
       ->where('a.date < :limitdate')
       ->setParameter('limitdate', $limitdate);
 
$absdocs = $qb->getQuery()->getResult();
foreach ($absdocs as $ad) {
    $ad->deleteFile();
    $entityManager->remove($ad);
}
$entityManager->flush();
