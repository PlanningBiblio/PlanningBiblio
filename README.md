Planning Biblio
===============

Planning Biblio est un logiciel libre développé en PHP-MySQL permettant de réaliser les plannings de service public

- Site web : http://www.planningbiblio.fr
- Twitter : @jeromecombes , #PlanningBiblio
- Facebook : facebook.com/PlanningBiblio
- Groupe Facebook : Les faiseurs de planning : https://www.facebook.com/groups/350347521813310

===============
Prérequis :

- Serveur Apache 2.2 ou supérieur
- PHP 5.3 ou supérieur
- MySQL client/serveur 5 ou supérieur

- Extensions PHP :
  - Calendar
  - Mcrypt
  - Mysqli
  - PDO
  - PDO-Mysql
  - CURL (si identification CAS)
  - LDAP (si utilisation avec un serveur LDAP)
  - Symfony/HttpFoundation
  - phpmailer/phpmailer
  - apereo/phpCAS

===============
Copyright (C) 2011-2018 - Jérôme Combes

Planning Biblio est un logiciel libre : vous pouvez le redistribuer et/ou le modifier
suivant les termes de la "GNU General Public License", telle que publiée par la 
Free Software Foundation (version 2 et au dela).

Planning Biblio est distribué dans l'espoir qu'il vous sera utile, mais SANS AUCUNE GARANTIE :
sans même la garantie implicite de COMMERCIALISABILITÉ ni d'ADÉQUATION À UN OBJECTIF PARTICULIER.
Consultez la Licence Générale Publique GNU pour plus de détails.

Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec ce programme (fichier LICENSE); 
si ce n'est pas le cas, consultez : http://www.gnu.org/licenses

===============
Ressources :

Dossier vendor/ics-parser
 - Licence MIT : http://www.datatables.net/license/mit
 - https://github.com/johngrogg/ics-parser
 - Martin Thoma (programming, bug fixing, project management)
 - Frank Gregor (programming, feedback, testing)
 - John Grogg (programming, addition of event recurrence handling)
 - [Jonathan Goode](https://github.com/u01jmg3) (programming, bug fixing, enhancement, coding standard)

Fichier include/feries.php
 - contient la fonction jour_ferie permettant de déterminer rapidement si un jour est férié (fêtes...
 - a été modifié pour prendre en paramètre la date au format YYYY-MM-DD et pour retourner le nom du jour ferié
 - URL            : http://www.phpsources.org/scripts382-PHP.htm
 - Auteur         : Olravet
 - Date édition   : 05 Mai 2008
 - Website auteur : http://olravet.fr/

Fichier vendor/jquery-*.min.js
 - Bibliothèques JQuery
 - About jQuery : http://learn.jquery.com/about-jquery
 - Licence MIT : https://jquery.org/license

Fichiers et dossiers vendor/jquery-ui-*, themes/default/jquery-ui-min.css
 - Bibliothèques et thèmes JQuery-UI
 - About jQuery UI : http://jqueryui.com/about
 - Licence MIT : https://jquery.org/license

Dossier DataTables*
 - Site Web : http://www.datatables.net/
 - Licence MIT : http://www.datatables.net/license/mit

JQuery-cookies
 - GitHub : https://github.com/carhartl/jquery-cookie
 - Licence MIT