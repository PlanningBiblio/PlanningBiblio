<?php

$tables = array (
    array(
        'name' => 'absences',
        'fields' => array(
            'cal_name',
            'commentaires',
            'etat',
            'groupe',
            'ical_key',
            'motif',
            'motif_autre',
            'rrule',
            'uid',
        ),
    ),
    array(
        'name' => 'absences_infos',
        'fields' => array(
            'texte',
        ),
    ),
    array(
        'name' => 'absences_recurrentes',
        'fields' => array(
            'event',
            'uid',
        ),
    ),
    array(
        'name' => 'acces',
        'fields' => array(
            'categorie',
            'groupe',
            'nom',
            'page',
        ),
    ),
    array(
        'name' => 'activites',
        'fields' => array(
            'nom',
        ),
    ),
    array(
        'name' => 'appel_dispo',
        'fields' => array(
            'date',
            'debut',
            'destinataires',
            'fin',
            'message',
            'sujet',
        ),
    ),
    array(
        'name' => 'config',
        'fields' => array(
            'categorie',
            'commentaires',
            'nom',
            'type',
            'valeur',
            'valeurs',
        ),
    ),
    array(
        'name' => 'conges',
        'fields' => array(
            'commentaires',
            'debit',
            'heures',
            'refus',
        ),
    ),
    array(
        'name' => 'conges_cet',
        'fields' => array(
            'annee',
            'commentaires',
            'refus',
        ),
    ),
    array(
        'name' => 'conges_infos',
        'fields' => array(
            'texte',
        ),
    ),
    array(
        'name' => 'cron',
        'fields' => array(
            'comand',
            'comments',
            'dom',
            'dow',
            'h',
            'm',
            'mon',
        ),
    ),
    array(
        'name' => 'heures_absences',
        'fields' => array(
            'heures',
        ),
    ),
    array(
        'name' => 'heures_sp',
        'fields' => array(
            'heures',
        ),
    ),
    array(
        'name' => 'hidden_tables',
        'fields' => array(
            'hidden_tables',
        ),
    ),
    array(
        'name' => 'infos',
        'fields' => array(
            'texte',
        ),
    ),
    array(
        'name' => 'ip_blocker',
        'fields' => array(
            'ip',
            'login',
            'status',
        ),
    ),
    array(
        'name' => 'jours_feries',
        'fields' => array(
            'annee',
            'commentaire',
            'nom',
        ),
    ),
    array(
        'name' => 'lignes',
        'fields' => array(
            'nom',
        ),
    ),
    array(
        'name' => 'log',
        'fields' => array(
            'msg',
            'program',
        ),
    ),
    array(
        'name' => 'menu',
        'fields' => array(
            'condition',
            'titre',
            'url',
        ),
    ),
    array(
        'name' => 'personnel',
        'fields' => array(
            'actif',
            'categorie',
            'check_ics',
            'code_ics',
            'commentaires',
            'droits',
            'heures_hebdo',
            'informations',
            'login',
            'mail',
            'mails_responsables',
            'matricule',
            'nom',
            'password',
            'postes',
            'prenom',
            'recup',
            'service',
            'sites',
            'statut',
            'temps',
            'url_ics',
        ),
    ),
    array(
        'name' => 'pl_notes',
        'fields' => array(
            'text',
        ),
    ),
    array(
        'name' => 'pl_notifications',
        'fields' => array(
            'data',
            'date',
        ),
    ),
    array(
        'name' => 'pl_poste_lignes',
        'fields' => array(
            'poste',
        ),
    ),
    array(
        'name' => 'pl_poste_modeles',
        'fields' => array(
            'commentaire',
            'jour',
            'nom',
            'tableau',
        ),
    ),
    array(
        'name' => 'pl_poste_modeles_tab',
        'fields' => array(
            'nom',
        ),
    ),
    array(
        'name' => 'pl_poste_tab',
        'fields' => array(
            'nom',
        ),
    ),
    array(
        'name' => 'pl_poste_tab_grp',
        'fields' => array(
            'nom',
        ),
    ),
    array(
        'name' => 'planning_hebdo',
        'fields' => array(
            'cle',
            'temps',
        ),
    ),
    array(
        'name' => 'postes',
        'fields' => array(
            'activites',
            'categories',
            'etage',
            'groupe',
            'nom',
            'obligatoire',
        ),
    ),
    array(
        'name' => 'recuperations',
        'fields' => array(
            'commentaires',
            'etat',
            'refus',
        ),
    ),
    array(
        'name' => 'select_abs',
        'fields' => array(
            'valeur',
        ),
    ),
    array(
        'name' => 'select_categories',
        'fields' => array(
            'valeur',
        ),
    ),
    array(
        'name' => 'select_etages',
        'fields' => array(
            'valeur',
        ),
    ),
    array(
        'name' => 'select_groupes',
        'fields' => array(
            'valeur',
        ),
    ),
    array(
        'name' => 'select_services',
        'fields' => array(
            'couleur',
            'valeur',
        ),
    ),
    array(
        'name' => 'select_statuts',
        'fields' => array(
            'couleur',
            'valeur',
        ),
    ),
);

$new2old = array(
    'á' => 'Ã¡',
    'À' => 'Ã€',
    'ä' => 'Ã¤',
    'Ä' => 'Ã„',
    'ã' => 'Ã£',
    'å' => 'Ã¥',
    'Å' => 'Ã…',
    'æ' => 'Ã¦',
    'Æ' => 'Ã†',
    'ç' => 'Ã§',
    'Ç' => 'Ã‡',
    'é' => 'Ã©',
    'É' => 'Ã‰',
    'è' => 'Ã¨',
    'È' => 'Ãˆ',
    'ê' => 'Ãª',
    'Ê' => 'ÃŠ',
    'ë' => 'Ã«',
    'Ë' => 'Ã‹',
    'í' => 'Ã-­­',
    'ì' => 'Ã¬',
    'Ì' => 'ÃŒ',
    'î' => 'Ã®',
    'Î' => 'ÃŽ',
    'ï' => 'Ã¯',
    'ñ' => 'Ã±',
    'Ñ' => 'Ã‘',
    'ó' => 'Ã³',
    'Ó' => 'Ã“',
    'ò' => 'Ã²',
    'Ò' => 'Ã’',
    'ô' => 'Ã´',
    'Ô' => 'Ã”',
    'ö' => 'Ã¶',
    'Ö' => 'Ã–',
    'õ' => 'Ãµ',
    'Õ' => 'Ã•',
    'ø' => 'Ã¸',
    'Ø' => 'Ã˜',
    'œ' => 'Å“',
    'Œ' => 'Å’',
    'ß' => 'ÃŸ',
    'ú' => 'Ãº',
    'Ú' => 'Ãš',
    'ù' => 'Ã¹',
    'Ù' => 'Ã™',
    'û' => 'Ã»',
    'Û' => 'Ã›',
    'ü' => 'Ã¼',
    'Ü' => 'Ãœ',
    'ÿ' => 'Ã¿',
    '€' => 'â‚¬',
    '’' => 'â€™',
    '‚' => 'â€š',
    'ƒ' => 'Æ’',
    '„' => 'â€ž',
    '…' => 'â€¦',
    '‡' => 'â€¡',
    'ˆ' => 'Ë†',
    '‰' => 'â€°',
    'Š' => 'Å ',
    '‹' => 'â€¹',
    'Ž' => 'Å½',
    '‘' => 'â€˜',
    '“' => 'â€œ',
    '•' => 'â€¢',
    '–' => 'â€“',
    '—' => 'â€”',
    '˜' => 'Ëœ',
    '™' => 'â„¢',
    'š' => 'Å¡',
    '›' => 'â€º',
    'ž' => 'Å¾',
    'Ÿ' => 'Å¸',
    '¡' => 'Â¡',
    '¢' => 'Â¢',
    '£' => 'Â£',
    '¤' => 'Â¤',
    '¥' => 'Â¥',
    '¦' => 'Â¦',
    '§' => 'Â§',
    '¨' => 'Â¨',
    '©' => 'Â©',
    'ª' => 'Âª',
    '«' => 'Â«',
    '¬' => 'Â¬',
    '®' => 'Â®',
    '¯' => 'Â¯',
    '°' => 'Â°',
    '±' => 'Â±',
    '²' => 'Â²',
    '³' => 'Â³',
    '´' => 'Â´',
    'µ' => 'Âµ',
    '¶' => 'Â¶',
    '·' => 'Â·',
    '¸' => 'Â¸',
    '¹' => 'Â¹',
    'º' => 'Âº',
    '»' => 'Â»',
    '¼' => 'Â¼',
    '½' => 'Â½',
    '¾' => 'Â¾',
    '¿' => 'Â¿',
    'à' => 'Ã ',
    '†' => 'â€ ',
    '”' => 'â€',
    'â' => 'Ã¢',
    'Â' => 'Ã‚',
    'Ã' => 'Ãƒ',
);

$old_characters = array();
$new_characters = array();

foreach ($new2old as $k => $v) {
    $old_characters[] = $v;
    $new_characters[] = $k;
}

foreach ($tables as $table) {

    $name = $table['name'];
    $fields = $table['fields'];

    $sql[] = "ALTER TABLE `{$dbprefix}{$name}` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";

    $fields_query = '`id`, `' . join('`, `', $table['fields']) . '`';

    $req = "SELECT $fields_query FROM `{$dbprefix}{$name}`;";

    $db = new db();
    $db->query($req);

    if ($db->result) {
        foreach ($db->result as $elem) {

            foreach ($fields as $field) {

                $value = $elem[$field];
                $origin = $value;
                $value = str_replace($old_characters, $new_characters, $value);

                $test = mb_detect_encoding($value, 'UTF-8', true);

                if ($test === false) {
                    $value = utf8_encode($value);
                }

                if ($origin != $value) {
                    $sql[] = "UPDATE `{$dbprefix}{$name}` SET `$field` = '$value' WHERE `id`='{$elem['id']}';";
                }
            }
        }
    }
}