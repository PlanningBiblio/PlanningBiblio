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
| breaktime | breaktime |
| calName | cal_name |
| categories | categories |
| category | categorie |
| change | modification or modif | type int (userId) |
| changeDate | modification | type DateTime |
| comment | texte or commentaires |
| current | actuel |
| day | jour |
| debit | debit |
| delete | supprime | type int (userId) |
| deleteDate | suppr_date |
| deletion | supprime | for Agents, type int, values 0, 1, 2 |
| departure | depart |
| employeeNumber | matricule |
| end | fin |
| entry | saisie_par | type int (userId) |
| entryDate | saisie | type datetime |
| exception | exception |
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
| key | cle |
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
| numberOfWeeks | nb_semaine | For WorkingHours |
| password | password |
| order | ordre |
| originId | id_origin or origin_id|
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
| regulationId | regul_id |
| service | service |
| reason | motif |
| refusal | refus | For Holiday |
| replace | remplace |
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

## Repositories

| Old | New | Comment |
| ---- | -------- | ------- |
| conges::fetchCredit | Agent::fetchCredits |
| conges::maj | Holiday::insert |
| personnel::delete | Agent::delete |
| personnel::fecth | Agent::get |
| planninghebdo::fetch | WorkingHours::get |

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

## Templates

| Old | New | Comment |
| ---- | -------- | ------- |
| #debut | .start-date| class used for the correct initialization of a datepicker's starting-date |
| #fin | .end-date| class used for the correct initialization of a datepicker's ending-date|
| #debut | .start-search| class used for the starting-date of a search form with search span limitation of one year|
| #fin | .end-search| class used for the ending-date of a search form with search span limitation of one year |


## Translation

Translation is now enabled in Planno !!  
It can be used is templates, controllers and even JS files.

The config/packages/translation.yaml file contains all the necessary information to configure the translation.
To change the main language, adjust the default_locale variable.

### Translation files
The translations files are located in the translations directory. Their are named using the following logic : {domain}.{locale}.po

Domains are group into wich translations can be organized. By default, all messages use the default 'messages' domain. Synfony also provides translation files for built-in validation and security messages in the equivalent domains. These files provides roughly 200 translation messages in 57 languages. Feel free to check out the available messages before adding a new one.  

To do so, you can run the following command :
`php bin/console debug:translation {locale}`  
This will list all the available translation messages for a locale, their domain, and their statuses.

The differents statuses :
- empty : the translation message is used somewhere in the code
- unused : the translation message is unused but valid
- missing : the translation message is used in the code but not defined in the translations files of this locale

`--only-unused` or `--only-missing` options can be added to the previous command to display only specific statuses.

The translation files use the following logic for every message :
- `msgid "..."` : the id of the message wich is actually the english version of it
- `msgstr "..." :` the translation in the desired language  

In english translation files, `msgid` and `msgstr` are equal.

```
## Exemple for french translation
msgid "Add"
msgstr "Ajouter"
```

### Fallback
English has been defined as the fallback language when a translation message is not found in the desired locale.  
If the message is missing, the id will be returned.

### Translation inside controllers and php files

```php
// ...
use Symfony\Contracts\Translation\TranslatorInterface;

public function index(TranslatorInterface $translator): Response
{
    $translated = $translator->trans('Symfony is great');
    // ...
}
```

### Translation inside templates and html files

```html
<div class="invalid-feedback">{% trans %}Symfony is great{% endtrans %}</div>
```

### Translation inside JS files

To enable translation inside JS file, a specific translation bundle has been added (BazingaJSTranslationBundle). It implements the Symfony TranslatorInterface and provides the same trans() method.

```js
Translator.trans('Synfony is great');
```

To use it, translation files need to be exposed to the frontend with the following command :
`php bin/console bazinga:js-translation:dump --format=js --merge-domains`  
This needs to be run every time new translation messages are added to files. It is now integrated in the `composer install` command.

### More info
- https://symfony.com/doc/current/translation.html 
- https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/Resources/doc/index.md 
