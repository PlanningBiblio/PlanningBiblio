# Planno

![Logo Planno](public/themes/default/images/logo-planno.svg "Logo Planno")

Planno est un logiciel libre développé en PHP-MySQL permettant de réaliser les plannings de service public

- Site web : https://www.planno.fr
- X (Twitter) : @jeromecombes , #Planno
- Facebook : facebook.com/PlanningBiblio
- Groupe Facebook : Les faiseurs de planning : https://www.facebook.com/groups/350347521813310

### Prérequis :

- Serveur Apache 2.2 ou supérieur / Nginx 1.10.3 ou supérieur
- PHP 8.2 ou 8.3
- MariaDB client/serveur 10 ou supérieur

- Extensions PHP :
  - Calendar
  - Mysqli
  - PDO
  - PDO-Mysql
  - Sockets
  - XML
  - CURL (si identification CAS)
  - LDAP (si utilisation avec un serveur LDAP)

### Licence AGPL

Planno est un logiciel libre : vous pouvez le redistribuer et/ou le modifier
suivant les termes de la "GNU AFFERO GENERAL PUBLIC LICENSE", telle que publiée par la 
Free Software Foundation (version 3 et au dela).

Planno est distribué dans l'espoir qu'il vous sera utile, mais SANS AUCUNE GARANTIE :
sans même la garantie implicite de COMMERCIALISABILITÉ ni d'ADÉQUATION À UN OBJECTIF PARTICULIER.
Consultez la Licence Générale Publique GNU pour plus de détails.

Vous devriez avoir reçu une copie de la licence avec ce programme (fichier LICENSE.md); 
si ce n'est pas le cas, consultez : https://www.gnu.org/licenses/agpl-3.0.html

### Ressources installées via composer:

- Apereo/phpcas
- Doctrine
- Phpmailer
- Symfony
- Twig

### Ressources intégrées au code :

- ics-parser
  - Licence MIT
  - https://github.com/u01jmg3/ics-parser
  - Martin Thoma (programming, bug fixing, project management)
  - Frank Gregor (programming, feedback, testing)
  - John Grogg (programming, addition of event recurrence handling)
  - [Jonathan Goode](https://github.com/u01jmg3) (programming, bug fixing, enhancement, coding standard)

- Fonction getFrenchHolidays du fichier src/Service/PublicHolidayService.php
  - permet de déterminer rapidement si un jour est férié
  - inspirée de la fonction jour_ferie créée par Olravet
  - Auteur         : Olravet
  - Date édition   : 05 Mai 2008
  - Website auteur : https://olravet.fr

- Fichier public/vendor/js/jquery-*.min.js
  - Bibliothèques JQuery
  - About jQuery : https://learn.jquery.com/about-jquery
  - Licence MIT : https://jquery.org/license

- Dossier public/vendor/DataTables*
  - Site Web : https://datatables.net
  - Licence MIT : https://www.datatables.net/license/mit

- Dossier public/vendor/*jquery-cookies*
  - GitHub : https://github.com/carhartl/jquery-cookie
  - Licence MIT
