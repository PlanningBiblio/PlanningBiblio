# Best practices and glossary

## Entities

### Best practices

* To create a new entity, use `bin/console make:entity`
* When you add a new field, create get and set methods. E.g.: new field `lastname` must be accompanied by `public function getLastname()` and `public function setLastname(string $lastname)`.
* To get an entity field (except in templates), use getXXX(). E.g.: `$agent->getLastname();`
* To set an entity field, use setXXX(). E.g.: `$agent->setLastname("new name");`
* To get an entity field in a template, use the field name without the "get". E.g.: `agent.lastname`
* See https://symfony.com/doc/current/doctrine.html

### Main entities Columns

| name | old name | comment |
| ---- | -------- | ------- |
| aCL | droits |
| active | actif |
| activities | activites |
| activity | activite |
| actualAnticipation | anticipation_actuel |
| actualCompTime | recup_actuel |
| actualCredit | solde_actuel |
| actualRemainder | relicat_actuel |
| annualCredit | conges_annuel |
| arrival | arrivee |
| attachmentNA | so |
| attachment1 | pj1 |
| attachment2 | pj2 |
| blocking | bloquant |
| calName | cal_name |
| categories | categories |
| category | categorie |
| change | modification | type int (userId) |
| comment | texte or commentaires |
| day | jour |
| debit | debit |
| delete | supprime | type int (userId) |
| deletion | supprime | for Agents, type int, values 0, 1, 2 |
| departure | depart |
| employeeNumber | matricule |
| end | fin |
| entry | saisie_par | type int (userId) |
| entryDate | saisie | type datetime |
| floor | etage |
| finished | end | for RecurringAbsence |
| firstname | prenom |
| group | groupe |
| groupId | groupe_id |
| halfDay | halfday | type int |
| halfDayEnd | end_halfday |
| halfDayStart | start_halfday |
| hamacCheck | check_hamac |
| holidayAnticipation | conges_anticipation |
| holidayCompTime | comp_time |
| holidayCredit | conges_credit |
| holidayRemainder | conges_reliquat |
| hours | heures |
| icsCheck | check_ics |
| iCalKey | ical_key |
| iCSCode | code_ics |
| icsUrl | url_ics |
| id | id |
| info | supprime | type int (userId) |
| information | informations |
| lastLogin | last_login |
| lastModified | last_modified |
| lastname | nom | for Agents |
| line | ligne |
| lock{1,2} | verrou{1,2} |
| login | login |
| lunch | lunch |
| name | nom |
| manager | responsable |
| managersMails | mails_responsables |
| mandatory | obligatoire |
| modelId | model_id |
| msGraphCheck | check_ms_graph |
| number | numero |
| password | password |
| originId | id_origin |
| otherReason | motif_autre |
| position | poste |
| previousAnticipation | anticipation_prec |
| previousCompTime | recup_prec |
| previousCredit | solde_prec |
| previousRemainder | relicat_prec |
| quotaSP | quota_sp |
| weeklyServiceHours | heures_hebdo | For Agent |
| quotaSP | quota_sp | For Position |
| rank | rang |
| recoveryMenu | recup |
| service | service |
| reason | motif |
| requestDate | demande |
| rRule| rrule |
| site | site |
| sites | sites |
| skills | postes | for Agents |
| start | debut |
| statistics | statistiques |
| status | etat, statut |
| table | tableau |
| teleworking | teleworking |
| user | agent |
| userId | perso_id or agent_id or agentId |
| validLevel1 | valide_n1 |
| validLevel1Date | validation_n1 |
| validLevel2 | valide or valide_n2 |
| validLevel2Date | validation or validation_n2 |
| value | valeur |
| values | valeurs |
| week | semaine |
| weeklyWorkingHours | heures_travail | For Agent |
| workingHours | temps |

## Migrations

> You should no longer use atomic_update and the maj.php file

* To create a new migration, use `bin/console doctrine:migrations:generate`
    * A new file will be created in src/Migrations/<current_year>/
    * Edit this file :
        * add your migration in the "up" function
        * add the opposite migration in the "down" function
        * add a description in the getDescription function
* To display and execute your migrations :
    * `bin/console doctrine:migrations:list` : show all migration, their status and their description
    * `bin/console doctrine:migrations:execute --up "App\Migrations\Version<migration_number>"` : execute the specified migration
    * `bin/console doctrine:migrations:execute --down "App\Migrations\Version<migration_number>"` : rollback the specified migration
    * `bin/console doctrine:migrations:migrate` : execute all pending migration
    * `bin/console doctrine:migrations:migrate first` : rollback all migrate

> NB: All pending migrations will be automatically executed during Planno updates (when the branch is released). We no longer need to add them to the maj.php file.
