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
| activities | activites |
| activity | activite |
| actualAnticipation | anticipation_actuel |
| actualCompTime | recup_actuel |
| actualCredit | solde_actuel |
| actualRemainder | relicat_actuel |
| annualCredit | conges_annuel |
| blocking | bloquant |
| categories | categories |
| category | categorie |
| change | modification | type int (userId) |
| comment | texte or commentaires |
| day | jour |
| debit | debit |
| delete | supprime | type int (userId) |
| deletion | supprime | for Agents, type int, values 0, 1, 2 |
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
| hours | heures |
| iCSCode | code_ics |
| id | id |
| info | supprime | type int (userId) |
| lastname | nom | for Agents |
| line | ligne |
| lock{1,2} | verrou{1,2} |
| login | login |
| lunch | lunch |
| name | nom |
| manager | responsable |
| mandatory | obligatoire |
| modelId | model_id |
| number | numero |
| position | poste |
| previousAnticipation | anticipation_prec |
| previousCompTime | recup_prec |
| previousCredit | solde_prec |
| previousRemainder | relicat_prec |
| quotaSP | quota_sp |
| rank | rang |
| site | site |
| sites | sites |
| skills | postes | for Agents |
| start | debut |
| statistics | statistiques |
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

> NB: All pending migrations will be automatically executed during Planno updates. We no longer need to add then to the maj.php file.
