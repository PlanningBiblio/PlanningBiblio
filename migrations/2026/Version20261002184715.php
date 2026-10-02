<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261002184715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'MT51960: Remove HTML entities from the config table';
    }

    public function up(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $configValues = [
            ['Conges-Rappels-N1'           , '[["Mail-Planning","La cellule planning"],["mails_responsables","Les responsables hiérarchiques"]]'],
            ['Conges-Rappels-N2'           , '[["Mail-Planning","La cellule planning"],["mails_responsables","Les responsables hiérarchiques"]]'],
            ['Conges-Validation-N2'        , '[[0,"Validation directe autorisée"],[1,"Le congé doit être validé au niveau 1"]]'],
            ['Absences-notifications-A1'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['Absences-notifications-A2'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['Absences-notifications-A3'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['Absences-notifications-A4'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['PlanningHebdo-notifications1', '[[0,"Agents ayant le droit de valider les heures de présence au niveau 1"],[1,"Agents ayant le droit de valider les heures de présence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concerné"]]'],
            ['PlanningHebdo-notifications2', '[[0,"Agents ayant le droit de valider les heures de présence au niveau 1"],[1,"Agents ayant le droit de valider les heures de présence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concerné"]]'],
            ['PlanningHebdo-notifications3', '[[0,"Agents ayant le droit de valider les heures de présence au niveau 1"],[1,"Agents ayant le droit de valider les heures de présence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concerné"]]'],
            ['PlanningHebdo-notifications4', '[[0,"Agents ayant le droit de valider les heures de présence au niveau 1"],[1,"Agents ayant le droit de valider les heures de présence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concerné"]]'],
            ['PlanningHebdo-Validation-N2' , '[[0,"Validation directe autorisée"],[1,"Le planning doit être validé au niveau 1"]]'],
            ['ICS-Status1'                 , '[["CONFIRMED","Confirmés"],["ALL","Tous"]]'],
            ['ICS-Status2'                 , '[["CONFIRMED","Confirmés"],["ALL","Tous"]]'],
            ['ICS-Status3'                 , '[["CONFIRMED","Confirmés"],["ALL","Tous"]]'],
            ['Hamac-status'                , '[["1,2,3,5,6","Validées et en attente de validation"],["2","Validées"]]'],
            ['Absences-notifications-B1'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['Absences-notifications-B2'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['Absences-notifications-B3'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['Absences-notifications-B4'   , '[[0,"Agents ayant le droit de gérer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concerné"]]'],
            ['Absences-Validation-N2'      , '[[0,"Validation directe autorisée"],[1,"L\\\'absence doit être validé au niveau 1"]]'],
        ];

        foreach ($configValues as $elem) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `valeurs` = '{$elem[1]}' WHERE `nom` = '{$elem[0]}';");
        }

        $configComments = [
            ['Granularite'                        , 'Granularité des champs horaires.'],
            ['ClasseParService'                   , 'Classer les agents par service dans le menu déroulant du planning'],
            ['Alerte2SP'                          , 'Alerter si l\\\'agent fera 2 plages de service public de suite'],
            ['Conges-Recuperations'               , 'Traiter les récupérations comme les congés (Assembler), ou les traiter séparément (Dissocier)'],
            ['Recup-Agent'                        , 'Type de champ pour la récupération des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu déroulant</b>'],
            ['Recup-Uneparjour'                   , 'Autoriser une seule demande de récupération par jour'],
            ['Recup-DelaiDefaut'                  , 'Delai pour les demandes de récupération par défaut (en jours)'],
            ['Conges-Rappels'                     , 'Activer / Désactiver l\\\'envoi de rappels s\\\'il y a des congés non-validés'],
            ['Conges-Rappels-Jours'               , 'Nombre de jours à contrôler pour l\\\'envoi de rappels sur les congés non-validés'],
            ['Conges-Rappels-N1'                  , 'A qui envoyer les rappels sur les congés non-validés au niveau 1'],
            ['Conges-Rappels-N2'                  , 'A qui envoyer les rappels sur les congés non-validés au niveau 2'],
            ['Conges-Validation-N2'               , 'La validation niveau 2 des congés peut se faire directement ou doit attendre la validation niveau 1'],
            ['Absences-validation'                , 'Les absences doivent être validées par un administrateur avant d\\\'être prises en compte'],
            ['Absences-non-validees'              , 'Dans les plannings, afficher en rouge les agents pour lesquels une absence non-validée est enregistrée'],
            ['Absences-agent-preselection'        , 'Présélectionner l\\\'agent connecté lors de l\\\'ajout d\\\'une nouvelle absence.'],
            ['Absences-tous'                      , 'Autoriser l\\\'enregistrement d\\\'absences pour tous les agents en une fois'],
            ['Planning-sansRepas'                 , 'Afficher une notification pour les Sans Repas dans le menu déroulant et dans le planning'],
            ['Planning-dejaPlace'                 , 'Afficher une notification pour les agents déjà placé sur un poste dans le menu déroulant du planning'],
            ['Planning-Heures'                    , 'Afficher les heures à côté du nom des agents dans le menu du planning'],
            ['Planning-CommentairesToujoursActifs', 'Afficher la zone de commentaire même si le planning n\\\'est pas encore commencé.'],
            ['Absences-notifications-A2'          , 'Destinataires des notifications de modification d\\\'absences (Circuit A)'],
            ['Absences-notifications-titre'       , 'Titre personnalisé pour les notifications de nouvelles absences'],
            ['Absences-notifications-message'     , 'Message personnalisé pour les notifications de nouvelles absences'],
            ['Statistiques-Heures'                , 'Afficher des statistiques sur les créneaux horaires voulus. Les créneaux doivent être au format 00h00-00h00 et séparés par des ; Exemple : 19h00-20h00; 20h00-21h00; 21h00-22h00'],
            ['Affichage-theme'                    , 'Thème de l\\\'application.'],
            ['Affichage-titre'                    , 'Titre affiché sur la page d\\\'accueil'],
            ['Affichage-etages'                   , 'Afficher les étages des postes dans le planning'],
            ['Planning-SR-debut'                  , 'Heure de début pour la vérification des sans repas'],
            ['Planning-SR-fin'                    , 'Heure de fin pour la vérification des sans repas'],
            ['Planning-Absences-Heures-Hebdo'     , 'Prendre en compte les absences pour calculer le nombre d\\\'heures de SP à effectuer. (Module PlanningHebdo requis)'],
            ['PlanningHebdo-Pause2'               , '2 pauses dans une journée'],
            ['Planning-AppelDispo'                , 'Permettre l\\\'envoi d\\\'un mail aux agents disponibles pour leur demander s\\\'ils sont volontaires pour occuper le poste choisi.'],
            ['Planning-AppelDispoSujet'           , 'Sujet du mail pour les appels à disponibilité'],
            ['Planning-AppelDispoMessage'         , 'Corps du mail pour les appels à disponibilité'],
            ['LDAP-Host'                          , 'Nom d\\\'hôte ou adresse IP du serveur LDAP'],
            ['LDAP-Protocol'                      , 'Protocol utilisé'],
            ['LDAP-Filter'                        , 'Filtre LDAP. OpenLDAP essayez "(objectclass=inetorgperson)" , Active Directory essayez "(&(objectCategory=person)(objectClass=user))". Vous pouvez bien-sûr personnaliser votre filtre.'],
            ['LDAP-ID-Attribute'                  , 'Attribut d\\\'authentification (OpenLDAP : uid, Active Directory : samaccountname)'],
            ['LDAP-Matricule'                     , 'Attribut à importer dans le champ matricule (optionnel)'],
            ['CAS-Hostname'                       , 'Nom d\\\'hôte du serveur CAS'],
            ['CAS-CACert'                         , 'Chemin absolut du certificat de l\\\'Autorité de Certification. Si pas renseigné, l\\\'identité du serveur ne sera pas vérifiée.'],
            ['CAS-SSLVersion'                     , 'Version SSL/TLS à utiliser pour les échanges avec le serveur CAS'],
            ['CAS-URI-Logout'                     , 'Page de déconnexion CAS'],
            ['Rappels-Jours'                      , 'Nombre de jours à contrôler pour les rappels'],
            ['Rappels-Renfort'                    , 'Contrôler les postes de renfort lors des rappels'],
            ['IPBlocker-TimeChecked'              , 'Recherche les échecs d\\\'authentification lors des N dernières minutes. ( 0 = IPBlocker désactivé )'],
            ['IPBlocker-Attempts'                 , 'Nombre d\\\'échecs d\\\'authentification autorisés lors des N dernières minutes'],
            ['IPBlocker-Wait'                     , 'Temps de blocage de l\\\'IP en minutes'],
            ['ICS-Pattern1'                       , 'Motif d\\\'absence pour les événements importés du 1<sup>er</sup> serveur. Ex: Agenda Personnel'],
            ['ICS-Status1'                        , 'Importer tous les événements ou seulement les événements confirmés (attribut STATUS = CONFIRMED). Si "tous" est choisi, les événements non-confirmés seront enregistrés comme des absences en attente de validation'],
            ['ICS-Server2'                        , 'URL du 2<sup>ème</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics'],
            ['ICS-Pattern2'                       , 'Motif d\\\'absence pour les événements importés du 2<sup>ème</sup> serveur. Ex: Congés'],
            ['ICS-Status2'                        , 'Importer tous les événements ou seulement les événements confirmés (attribut STATUS = CONFIRMED). Si "tous" est choisi, les événements non-confirmés seront enregistrés comme des absences en attente de validation'],
            ['ICS-Server3'                        , 'Utiliser une URL définie pour chaque agent dans le menu Administration / Les agents'],
            ['ICS-Pattern3'                       , 'Motif d\\\'absence pour les événements importés depuis l\\\'URL définie dans la fiche des agents. Ex: Agenda personnel'],
            ['ICS-Status3'                        , 'Importer tous les événements ou seulement les événements confirmés (attribut STATUS = CONFIRMED). Si "tous" est choisi, les événements non-confirmés seront enregistrés comme des absences en attente de validation'],
            ['ICS-Export'                         , 'Autoriser l\\\'exportation des plages de service public sous forme de calendriers ICS. Un calendrier par agent, accessible à l\\\'adresse [SERVER]/ics/calendar.php?login=[login_de_l_agent]'],
            ['ICS-Code'                           , 'Protéger les calendriers ICS par des codes de façon à ce qu\\\'on ne puisse pas deviner les URLs. Si l\\\'option est activée, les URL seront du type : [SERVER]/ics/calendar.php?login=[login_de_l_agent]&code=[code_aléatoire]'],
            ['PlanningHebdo-CSV'                  , 'Emplacement du fichier CSV à importer (importation automatisée) Ex: /dossier/fichier.csv'],
            ['Agenda-Plannings-Non-Valides'       , 'Afficher ou non les plages de service public des plannings non validés dans les agendas.'],
            ['Planning-agents-volants'            , 'Utiliser le module "Agents volants" permettant de différencier un groupe d\\\'agents dans le planning'],
            ['Hamac-csv'                          , 'Chemin d\\\'accès au fichier CSV pour l\\\'importation des absences depuis Hamac'],
            ['Hamac-motif'                        , 'Motif pour les absences importés depuis Hamac. Ex: Hamac'],
            ['Hamac-status'                       , 'Importer les absences validées et en attente de validation ou seulement les absences validées.'],
            ['Absences-notifications-B2'          , 'Destinataires des notifications de modification d\\\'absences (Circuit B)'],
            ['Absences-DelaiSuppressionDocuments' , 'Les documents associés aux absences sont supprimés au-delà du nombre de jours définis par ce paramètre.'],
        ];

        foreach ($configComments as $elem) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `commentaires` = '{$elem[1]}' WHERE `nom` = '{$elem[0]}';");
        }
    }

    public function down(Schema $schema): void
    {
        $dbprefix = $_ENV['DATABASE_PREFIX'];

        $configValues = [
            ['Conges-Rappels-N1'           , '[["Mail-Planning","La cellule planning"],["mails_responsables","Les responsables hi&eacute;rarchiques"]]'],
            ['Conges-Rappels-N2'           , '[["Mail-Planning","La cellule planning"],["mails_responsables","Les responsables hi&eacute;rarchiques"]]'],
            ['Conges-Validation-N2'        , '[[0,"Validation directe autoris&eacute;e"],[1,"Le cong&eacute; doit &ecirc;tre valid&eacute; au niveau 1"]]'],
            ['Absences-notifications-A1'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['Absences-notifications-A2'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['Absences-notifications-A3'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['Absences-notifications-A4'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['PlanningHebdo-notifications1', '[[0,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1"],[1,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concern&eacute;"]]'],
            ['PlanningHebdo-notifications2', '[[0,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1"],[1,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concern&eacute;"]]'],
            ['PlanningHebdo-notifications3', '[[0,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1"],[1,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concern&eacute;"]]'],
            ['PlanningHebdo-notifications4', '[[0,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 1"],[1,"Agents ayant le droit de valider les heures de pr&eacute;sence au niveau 2"],[2,"Responsables directs"],[3,"Cellule planning"],[4,"Agent concern&eacute;"]]'],
            ['PlanningHebdo-Validation-N2' , '[[0,"Validation directe autoris&eacute;e"],[1,"Le planning doit &ecirc;tre valid&eacute; au niveau 1"]]'],
            ['ICS-Status1'                 , '[["CONFIRMED","Confirm&eacute;s"],["ALL","Tous"]]'],
            ['ICS-Status2'                 , '[["CONFIRMED","Confirm&eacute;s"],["ALL","Tous"]]'],
            ['ICS-Status3'                 , '[["CONFIRMED","Confirm&eacute;s"],["ALL","Tous"]]'],
            ['Hamac-status'                , '[["1,2,3,5,6","Valid&eacute;es et en attente de validation"],["2","Valid&eacute;es"]]'],
            ['Absences-notifications-B1'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['Absences-notifications-B2'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['Absences-notifications-B3'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['Absences-notifications-B4'   , '[[0,"Agents ayant le droit de g&eacute;rer les absences"],[1,"Responsables directs"],[2,"Cellule planning"],[3,"Agent concern&eacute;"]]'],
            ['Absences-Validation-N2'      , '[[0,"Validation directe autoris&eacute;e"],[1,"L\\\'absence doit &ecirc;tre valid&eacute; au niveau 1"]]'],
        ];

        foreach ($configValues as $elem) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `valeurs` = '{$elem[1]}' WHERE `nom` = '{$elem[0]}';");
        }

        $configComments = [
            ['Granularite'                        , 'Granularit&eacute; des champs horaires.'],
            ['ClasseParService'                   , 'Classer les agents par service dans le menu d&eacute;roulant du planning'],
            ['Alerte2SP'                          , 'Alerter si l&apos;agent fera 2 plages de service public de suite'],
            ['Conges-Recuperations'               , 'Traiter les r&eacute;cup&eacute;rations comme les cong&eacute;s (Assembler), ou les traiter s&eacute;par&eacute;ment (Dissocier)'],
            ['Recup-Agent'                        , 'Type de champ pour la r&eacute;cup&eacute;ration des samedis dans la fiche des agents.<br/>Rien [vide], champ <b>texte</b> ou <b>menu d&eacute;roulant</b>'],
            ['Recup-Uneparjour'                   , 'Autoriser une seule demande de r&eacute;cup&eacute;ration par jour'],
            ['Recup-DelaiDefaut'                  , 'Delai pour les demandes de récupération par d&eacute;faut (en jours)'],
            ['Conges-Rappels'                     , 'Activer / D&eacute;sactiver l&apos;envoi de rappels s&apos;il y a des cong&eacute;s non-valid&eacute;s'],
            ['Conges-Rappels-Jours'               , 'Nombre de jours &agrave; contr&ocirc;ler pour l&apos;envoi de rappels sur les cong&eacute;s non-valid&eacute;s'],
            ['Conges-Rappels-N1'                  , 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 1'],
            ['Conges-Rappels-N2'                  , 'A qui envoyer les rappels sur les cong&eacute;s non-valid&eacute;s au niveau 2'],
            ['Conges-Validation-N2'               , 'La validation niveau 2 des cong&eacute;s peut se faire directement ou doit attendre la validation niveau 1'],
            ['Absences-validation'                , 'Les absences doivent &ecirc;tre valid&eacute;es par un administrateur avant d&apos;&ecirc;tre prises en compte'],
            ['Absences-non-validees'              , 'Dans les plannings, afficher en rouge les agents pour lesquels une absence non-valid&eacute;e est enregistr&eacute;e'],
            ['Absences-agent-preselection'        , 'Présélectionner l&apos;agent connecté lors de l&apos;ajout d&apos;une nouvelle absence.'],
            ['Absences-tous'                      , 'Autoriser l&apos;enregistrement d&apos;absences pour tous les agents en une fois'],
            ['Planning-sansRepas'                 , 'Afficher une notification pour les Sans Repas dans le menu d&eacute;roulant et dans le planning'],
            ['Planning-dejaPlace'                 , 'Afficher une notification pour les agents d&eacute;j&agrave; plac&eacute; sur un poste dans le menu d&eacute;roulant du planning'],
            ['Planning-Heures'                    , 'Afficher les heures &agrave; c&ocirc;t&eacute; du nom des agents dans le menu du planning'],
            ['Planning-CommentairesToujoursActifs', 'Afficher la zone de commentaire m&ecirc;me si le planning n\\\'est pas encore commenc&eacute;.'],
            ['Absences-notifications-A2'          , 'Destinataires des notifications de modification d&apos;absences (Circuit A)'],
            ['Absences-notifications-titre'       , 'Titre personnalis&eacute; pour les notifications de nouvelles absences'],
            ['Absences-notifications-message'     , 'Message personnalis&eacute; pour les notifications de nouvelles absences'],
            ['Statistiques-Heures'                , 'Afficher des statistiques sur les cr&eacute;neaux horaires voulus. Les cr&eacute;neaux doivent &ecirc;tre au format 00h00-00h00 et s&eacute;par&eacute;s par des ; Exemple : 19h00-20h00; 20h00-21h00; 21h00-22h00'],
            ['Affichage-theme'                    , 'Th&egrave;me de l&apos;application.'],
            ['Affichage-titre'                    , 'Titre affich&eacute; sur la page d&apos;accueil'],
            ['Affichage-etages'                   , 'Afficher les &eacute;tages des postes dans le planning'],
            ['Planning-SR-debut'                  , 'Heure de d&eacute;but pour la v&eacute;rification des sans repas'],
            ['Planning-SR-fin'                    , 'Heure de fin pour la v&eacute;rification des sans repas'],
            ['Planning-Absences-Heures-Hebdo'     , 'Prendre en compte les absences pour calculer le nombre d&apos;heures de SP &agrave; effectuer. (Module PlanningHebdo requis)'],
            ['PlanningHebdo-Pause2'               , '2 pauses dans une journ&eacute;e'],
            ['Planning-AppelDispo'                , 'Permettre l&apos;envoi d&apos;un mail aux agents disponibles pour leur demander s&apos;ils sont volontaires pour occuper le poste choisi.'],
            ['Planning-AppelDispoSujet'           , 'Sujet du mail pour les appels &agrave; disponibilit&eacute;'],
            ['Planning-AppelDispoMessage'         , 'Corps du mail pour les appels &agrave; disponibilit&eacute;'],
            ['LDAP-Host'                          , 'Nom d&apos;h&ocirc;te ou adresse IP du serveur LDAP'],
            ['LDAP-Protocol'                      , 'Protocol utilis&eacute;'],
            ['LDAP-Filter'                        , 'Filtre LDAP. OpenLDAP essayez "(objectclass=inetorgperson)" , Active Directory essayez "(&(objectCategory=person)(objectClass=user))". Vous pouvez bien-s&ucirc;r personnaliser votre filtre.'],
            ['LDAP-ID-Attribute'                  , 'Attribut d&apos;authentification (OpenLDAP : uid, Active Directory : samaccountname)'],
            ['LDAP-Matricule'                     , 'Attribut &agrave; importer dans le champ matricule (optionnel)'],
            ['CAS-Hostname'                       , 'Nom d&apos;h&ocirc;te du serveur CAS'],
            ['CAS-CACert'                         , 'Chemin absolut du certificat de l&apos;Autorit&eacute; de Certification. Si pas renseign&eacute;, l&apos;identit&eacute; du serveur ne sera pas v&eacute;rifi&eacute;e.'],
            ['CAS-SSLVersion'                     , 'Version SSL/TLS &agrave; utiliser pour les &eacute;changes avec le serveur CAS'],
            ['CAS-URI-Logout'                     , 'Page de d&eacute;connexion CAS'],
            ['Rappels-Jours'                      , 'Nombre de jours &agrave; contr&ocirc;ler pour les rappels'],
            ['Rappels-Renfort'                    , 'Contr&ocirc;ler les postes de renfort lors des rappels'],
            ['IPBlocker-TimeChecked'              , 'Recherche les &eacute;checs d&apos;authentification lors des N derni&egrave;res minutes. ( 0 = IPBlocker d&eacute;sactiv&eacute; )'],
            ['IPBlocker-Attempts'                 , 'Nombre d&apos;&eacute;checs d&apos;authentification autoris&eacute;s lors des N derni&egrave;res minutes'],
            ['IPBlocker-Wait'                     , 'Temps de blocage de l&apos;IP en minutes'],
            ['ICS-Pattern1'                       , 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 1<sup>er</sup> serveur. Ex: Agenda Personnel'],
            ['ICS-Status1'                        , 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si "tous" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation'],
            ['ICS-Server2'                        , 'URL du 2<sup>&egrave;me</sup> serveur ICS avec la variable OpenURL entre crochets. Ex: http://server2.domain.com/holiday/[login].ics'],
            ['ICS-Pattern2'                       , 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s du 2<sup>&egrave;me</sup> serveur. Ex: Congés'],
            ['ICS-Status2'                        , 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si "tous" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation'],
            ['ICS-Server3'                        , 'Utiliser une URL d&eacute;finie pour chaque agent dans le menu Administration / Les agents'],
            ['ICS-Pattern3'                       , 'Motif d&apos;absence pour les &eacute;v&eacute;nements import&eacute;s depuis l&apos;URL d&eacute;finie dans la fiche des agents. Ex: Agenda personnel'],
            ['ICS-Status3'                        , 'Importer tous les &eacute;v&eacute;nements ou seulement les &eacute;v&eacute;nements confirm&eacute;s (attribut STATUS = CONFIRMED). Si "tous" est choisi, les &eacute;v&eacute;nements non-confirm&eacute;s seront enregistr&eacute;s comme des absences en attente de validation'],
            ['ICS-Export'                         , 'Autoriser l&apos;exportation des plages de service public sous forme de calendriers ICS. Un calendrier par agent, accessible &agrave; l&apos;adresse [SERVER]/ics/calendar.php?login=[login_de_l_agent]'],
            ['ICS-Code'                           , 'Prot&eacute;ger les calendriers ICS par des codes de façon &agrave; ce qu&apos;on ne puisse pas deviner les URLs. Si l&apos;option est activ&eacute;e, les URL seront du type : [SERVER]/ics/calendar.php?login=[login_de_l_agent]&amp;code=[code_al&eacute;atoire]'],
            ['PlanningHebdo-CSV'                  , 'Emplacement du fichier CSV &agrave; importer (importation automatis&eacute;e) Ex: /dossier/fichier.csv'],
            ['Agenda-Plannings-Non-Valides'       , 'Afficher ou non les plages de service public des plannings non valid&eacute;s dans les agendas.'],
            ['Planning-agents-volants'            , 'Utiliser le module "Agents volants" permettant de diff&eacute;rencier un groupe d&apos;agents dans le planning'],
            ['Hamac-csv'                          , 'Chemin d&apos;acc&egrave;s au fichier CSV pour l&apos;importation des absences depuis Hamac'],
            ['Hamac-motif'                        , 'Motif pour les absences import&eacute;s depuis Hamac. Ex: Hamac'],
            ['Hamac-status'                       , 'Importer les absences valid&eacute;es et en attente de validation ou seulement les absences valid&eacute;es.'],
            ['Absences-notifications-B2'          , 'Destinataires des notifications de modification d&apos;absences (Circuit B)'],
            ['Absences-DelaiSuppressionDocuments' , 'Les documents associ&eacute;s aux absences sont supprim&eacute;s au-del&agrave; du nombre de jours d&eacute;finis par ce param&egrave;tre.'],
        ];

        foreach ($configComments as $elem) {
            $this->addSql("UPDATE `{$dbprefix}config` SET `commentaires` = '{$elem[1]}' WHERE `nom` = '{$elem[0]}';");
        }        
    }
}
