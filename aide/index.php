<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : aide/index.php
Création : mai 2011
Dernière modification : 4 décembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche l'aide en ligne

Page appelée par la page index.php
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}
?>

<h3>Aide en ligne</h3>

<a id='a_retour' href='#'>Retour</a>

<div id='aide'>
<ol>
<li><a href="#absences">Absences</a>
	<ol>
	<li><a href='#abs_voir'>Voir les absences</a></li>
	<li><a href='#abs_ajouter'>Ajouter une absence</a></li>
	<li><a href='#abs_infos'>Informations</a></li>
	</ol>
	</li>
<li><a href="#agenda">Agenda</a></li>
<li><a href="#planning">Planning</a>
	<ol>
	<li><a href='#pl_consul'>Consultation</a></li>
	<li><a href='#pl_modif'>Modification</a></li>
	<li><a href='#pl_modele'>Utilisation des modèles</a></li>
	<li><a href='#pl_efface'>Effacer un planning</a></li>
	<li><a href='#pl_valid'>Validation</a></li>
	<li><a href='#pl_imprime'>Impression</a></li>
	</ol>
	</li>
<li><a href="#config_planning">Configuration du planning</a>
	<ol>
	<li><a href="#config_avertissement">Avertissement</a></li>
	<li><a href="#config_tableau">Création d'un nouveau tableau (Copie)</a></li>
	<li><a href="#config_horaires">Configuration des horaires</a></li>
	<li><a href="#config_lignes">Configuration des lignes</a></li>
	<li><a href="#config_groupes">Les groupes</a></li>
	<li><a href="#config_lignes_sep">Les lignes de séparation</a></li>
	</ol>
	</li>
<li><a href='#agents'>Les agents</a>
	<ol>
	<li><a href='#agent_ajout'>Ajout d'un agent</a></li>
	<li><a href='#agent_modif'>Modification de la fiche d'un agent</a></li>
	<li><a href='#agent_pass'>Modification du mot de passe</a></li>
	<li><a href='#agent_supp'>Suppression d'un agent</a></li>
	</ol>
</li>
<li><a href='#postes'>Les postes</a>
	<ol>
	<li><a href='#poste_ajout'>Ajout d'un poste</a></li>
	<li><a href='#poste_modif'>Modification d'un poste</a></li>
	<li><a href='#poste_supp'>Suppression d'un poste</a></li>
	</ol>
</li>
<li><a href="#activites">Les activités</a></li>
<li><a href="#statistiques">Statistiques</a>
	<ol>
	<li><a href='#stat_temps'>Feuille de temps</a></li>
	<li><a href='#stat_agent'>Statistiques par agent</a></li>
	<li><a href='#stat_service'>Statistiques par service</a></li>
	<li><a href='#stat_statut'>Statistiques par statut</a></li>
	<li><a href='#stat_poste'>Statistiques par poste</a></li>
	<li><a href='#stat_renfort'>Statistiques par postes de renfort</a></li>
	<li><a href='#stat_synthese'>Statistiques par postes (Synthèse)</a></li>
	<li><a href='#stat_samedis'>Statistiques par samedis</a></li>
	</ol>
	</li>
<li><a href="#informations">Les informations</a></li>
<li><a href="#fermeture">Jours de fermeture</a></li>
</ol>


<br/><br/>
<a name='absences'></a>
<h3>1) Absences</h3>
<a name='abs_voir'></a>
<h4>1.1) Voir les absences</h4>
<u><b>Si vous n'avez pas le droit de gérer les absences :</b></u><br/>
Vous pouvez voir la liste de vos absences entre 2 dates.<br/>
Sélectionnez la date de début et la date de fin à l'aide des calendriers puis validez en cliquant sur "OK".<br/>
La liste de vos absences durant cette période s'affiche avec les dates de début, de fin, le motif de l'absence et un commentaire éventuel.<br/><br/>
Si vous avez le droit de modifier vos absences, vous pouvez cliquer sur l'icône <img src='img/modif.png' alt='Feuille de papier et crayon' /> 
sur la ligne de l'absence que vous souhaitez modifier, vous verrez alors apparaître le détail de l'absence et vous pourrez la modifier.<br/>
<br/>
<u><b>Si vous avez le droit de gérer les absences :</b></u><br/>
Vous pouvez voir la liste des absents entre 2 dates.<br/>
Sélectionnez la date de début et la date de fin à l'aide des calendriers puis validez en cliquant sur "OK".<br/>
La liste des absents durant cette période s'affiche avec les dates de début, de fin, le nom de l'agent, le motif de l'absence et un commentaire éventuel.<br/>
Vous pouvez afficher les absences d'une personne en particulier en tapant son nom (ou prénom) dans le champ "agent" puis en cliquant sur "OK".<br/>
Vous pouvez modifier les informations relatives à une absence en cliquant sur l'icône <img src='img/modif.png' alt='Feuille de papier et crayon' /> en début de ligne.<br/>


<a name='abs_ajouter'></a>
<h4>1.2) Ajouter une absence</h4>
<u><b>Si vous avez le droit d'enregistrer vos propres absences</b></u><br/>
Vous permet d'enregistrer une absence à l'avance si elle est prévue.<br/>
Choisissez les dates de début et de fin à l'aide des calendriers, si besoin : les heures de début et de fin, 
le motif de l'absence (ex: Formation) et un commentaire éventuel.<br/>
Cliquez sur "Valider", une page de confirmation s'affiche, si tout est correct, cliquez de nouveau sur "Valider".<br/>

<br/>
<u><b>Si vous avez le droit de gérer les absences :</b></u><br/>
Vous permet d'enregistrer les absences des agents.<br/> 
Choisissez le nom de l'agent dans le menu déroulant, les dates de début et de fin à l'aide des calendriers, si besoin : les heures 
de début et de fin, le motif de l'absence (ex: Formation) et un commentaire éventuel.<br/>
Cliquez sur "Valider", une page de confirmation s'affiche, si tout est correct, cliquez de nouveau sur "Valider".<br/>

<a name='abs_infos'></a>
<h4>1.3) Informations</h4>
Permet aux agents ayant le droit de gérer les absences de diffuser des messages aux personnes voulant prendre des congés pendant une période définie.<br/>
Sélectionnez la date de début et de fin et le message à communiquer dans le champ "texte".<br/>
Validez puis confirmez.<br/>
Le message sera affiché pendant la période choisie dans le cadre permettant d'ajouter une absence.<br/>

<a name='agenda'></a>
<h3>2.) Agenda</h3>
Permet d'afficher votre agenda entre 2 dates.<br/>
Sélectionnez la date de début et de fin à l'aide des calendriers puis validez en cliquant sur "OK".<br/>
Vos heures de pr&eacute;sence, vos absences et la liste des postes occupés s'affichent avec les heures de début et de fin.<br/>
<u>Si vous en avez le droits</u>, vous pouvez voir les agendas des autres agents. 
Vous pouvez dans ce cas s&eacute;lectionnez le nom de  l'agent dans un menu déroulant.

<a name='planning'></a>
<h3>3.) Planning</h3>
<a name='pl_consul'></a>
<h4>3.1) Consultation</h4>
Permet à tous les agents de consulter le planning.<br/>
Si votre biblioth&egrave;que a plusieurs sites, s&eacute;lectionnez le site voulu dans le sous menu "Planning".<br/>
Sélectionnez la date voulue à l'aide du calendrier ou du jour de la semaine et le planning choisi s'affiche.<br/>
Par défaut, le planning du jour courant du premier site est affiché.<br/>
Si le planning n'est pas terminé, le message "Le planning du [date] n'est pas validé" s'affiche.<br/>
Sinon, un tableau composé en lignes du nom des postes et en colonnes des horaires s'affiche.<br/>
Dans les cellules apparaissent le nom des agents.<br/>
Selon la configuration de l'application, la liste des absents et/ou des présents est affiché en bas du planning.

<a name='pl_modif'></a>
<h4>3.2) Modification</h4>
Les agents ayant le droits de modifier les plannings ont accès aux plannings qu'ils soient validés ou non.<br/>
Pour commencer, vous devez choisir un tableau ou un groupe de tableaux dans les menus déroulant 
(Plus d'infos : voir <a href='#config_planning'>4) Configuration du planning</a>).<br/>
Si vous ajoutez un tableau, il sera affecté au jour courant. Si vous choisissez un groupe, les tableaux de 
ce groupe seront affectés à chacun des jours de la semaine (du lundi au samedi/dimanche).<br/>
Après validation, un tableau vide s'affiche.<br/><br/>

Lors d'un clic-droit dans les cellules, un menu apparaît.<br/>
Ce menu est constitué de la liste des agents disponibles et qualifiés pour le poste choisi ou de la liste des services (selon la configuration de l'application).<br/>
Si la liste des services s'affiche, en passant la souris devant le nom d'un service, les agents appartenant à ce service, disponibles  et qualifiés pour le poste choisi apparaîssent dans un sous menu.<br/>
En cliquant sur le nom d'un agent, il est placé dans la cellule.<br/>
Dans le menu déroulant, face au nom des agents, sont affichées les heures faites par jour, par semaine et le nombre d'heures que l'agent doit faire par semaine.<br/><br/>

En fonction des heures faites et à faire, la couleur de la cellule change :
<ul style='margin-top:0px;'>
<li>Vert : le quota d'heure par semaine sera atteint à plus ou moins 30 minutes.</li>
<li>Rouge : soit l'agent fera plus de 7 heures par jours, soit l'agent dépassera son quota de plus de 30 minutes.</li>
<li>Noir : Le quota ne sera pas atteint.</li>
</ul>
Si un agent a déjà effectué le poste choisi dans la journée, il apparaîtra en rouge dans le menu avec le message 
"(DP)" (Déjà Placé).<br/>
Si un agent est placé en continu entre 11h30 et 14h30, le message "(SR)" (Sans Repas) apparaîtra à coté de son nom 
dans les cellules concernées par ces horaires. Dans ce cas, il apparaîtra en rouge avec le message "(SR)" dans le menu.<br/>
<br/>
Si un agent est placé dans une cellule, vous pouvez :
<ul style='margin-top:0px;'>
<li>le supprimer en choisissant "Supprimer" dans le menu.</li>
<li>le barrer en choisissant "Barrer", dans ce cas, il est considéré comme prévu à ce poste mais absent.</li>
<li>le remplacer par un autre agent en choisissant un autre agent dans la liste.</li>
<li>le barrer ET le remplacer en cliquant sur la croix rouge <font style='color:red;font-weight:bold;'>x</font> face au nom du nouvel agent.</li>
<li>ajouter un 2<sup>ème</sup> agent en cliquant sur la croix bleue <font style='color:blue;font-weight:bold;'>+</font> face au nom du nouvel agent.</li>
</ul>

<a name='pl_modele'></a>
<h4>3.3) Utilisation des modèles</h4>
Vous pouvez enregistrer comme modèle le planning du jour ou de la semaine en cliquant sur l'icône représentant 
une disquette (en haut à droite). De cette façon, le planning réalisé est copié et vous pourrez utiliser cette 
copie pour un autre jour ou une autre semaine.<br/>
Pour récupérer un modèle, cliquez sur l'icône représentant un dossier jaune. Sélectionnez le nom du modèle à 
importer dans le menu déroulant.<br/>
Lorsque le nom est suivi de "(semaine)", il s'agit d'un planning d'une semaine complète. Dans ce cas, les plannings 
du lundi au samedi/dimanche de la semaine courante seront remplacés par ceux du modèle.<br/>
Lorsque le nom n'est pas suivi de "(semaine)", seul le jour courant sera remplacé.<br/>
Attention, si vous avez déjà saisi des informations dans le planning, elles seront perdues.<br/>
Pour modifier le nom d'un modèle ou en supprimer un, rendez-vous dans le menu "Administration - Les Modèles".

<a name='pl_efface'></a>
<h4>3.4) Effacer un planning</h4>
Vous pouvez effacer le planning en cliquant sur la croix rouge (en haut à droite).<br/>

<a name='pl_valid'></a>
<h4>3.5) Validation</h4>
Une fois votre planning terminé, vous devez le valider afin d'en donner l'accès (en lecture seule) à l'ensemble des agents.<br/>
Pour ceci, cliquez sur l'icône représentant un cadenas en haut à droite.<br/>
Une fois validé, le planning n'est plus modifiable. Votre nom ainsi que la date et l'heure de validation apparaissent en haut à droite du planning.<br/>
Si vous devez modifier votre planning, cliquez sur l'icône représentant un cadenas ouvert.
Modifiez le planning et validez-le de nouveau afin de le rendre accessible. Le nom, la date et l'heure de validation sont mis à jour.<br/>

<a name='pl_imprime'></a>
<h4>3.6) Impression</h4>
Vous pouvez imprimer le planning en cliquant sur l'icône représentant une imprimante (en haut à droite) 
ou en utilisant la commande d'impression de votre navigateur (Fichier / Imprimer ou Ctrl+P).<br/>
Il est recommandé d'utiliser Firefox pour l'impression du planning.<br/>
Avant la première impression, vous devez configurer votre navigateur pour que les couleurs d'arrière plan s'impriment.
Pour ceci, dans Firefox, allez dans fichier, mise en page et cochez "Imprimer le fond de page (couleur et images)".<br/>

<a name="config_planning"></a>
<h3>4) Configuration du planning</h3>

<a name="config_avertissement"></a>
<h4>4.1) Avertissement</h4>
Vous pouvez modifier les horaires et les lignes des plannings mais il est vivement conseillé de ne pas modifier 
un tableau en cours d'utilisation car vous risquez de perdre les informations enregistrées (affectation des agents) 
si vous supprimez une ligne ou modifiez une plage horaire.<br/>
Soyez donc très prudent.<br/>
L'ajout d'une nouvelle ligne ne pose pas de problème.<br/>
Vous pouvez copier un tableau existant et modifier le nouveau tableau de façon à ne pas affecter les plannings en cours d'utilisation.<br/>

<a name="config_tableau"></a>
<h4>4.2) Création d'un nouveau tableau (Copie)</h4>
Pour créer un nouveau tableau, rendez-vous dans le menu Administration - Les tableaux.<br/>
Choisissez un tableau dans la liste et cliquez sur l'icône <img src='img/copy.png' alt='Copie' />.<br/>
Saisissez le nom du nouveau tableau et cliquez sur "Copier".<br/>
Votre nouveau tableau apparaît dans la liste.<br/>


<a name="config_horaires"></a>
<h4>4.3) Configuration des horaires</h4>
Repérez le tableau à modifier dans la liste. Cliquez sur l'icône <img src='img/modif.png' alt='Modifier' /> se trouvant devant ce tableau.<br/>
Modifiez les horaires à l'aide des menus déroulant. Vous devez respecter les règles suivantes :
<ul>
<li>Tous les tableaux doivent commencer et finir aux mêmes heures (ex: début : 9h, fin 22h pour tous les tableaux).</li>
<li>Il ne doit pas y avoir de blanc entre 2 plages horaires (ex : 1<sup>ère</sup> plage : 9h-10h, la 2<sup>de</sup> doit commencer par 10h, etc ...).</li>
</ul>
Vous pouvez ajouter des menus déroulant en cliquant sur les signes <img src='img/add.gif' alt='+' />.<br/>
Lorsque vous avez terminé, cliquez sur "<b>Valider</b>" et passez à la configuration des lignes (Onglet lignes).
<a name="config_lignes"></a>
<h4>4.4) Configuration des lignes</h4>
Marquez les noms voulus dans la première colonne des lignes marrons (devant les horaires).<br/>
Ajoutez des lignes en cliquant sur les signes <img src='img/add.gif' alt='+' />.<br/>
Choisissez le nom du poste ou une ligne de séparation dans les menus déroulant pour chaque ligne.<br/>
Vous pouvez "griser" des cellules en cochant les cases "G". Ceci permet de marquer la cellule pour que personne n'y soit placé.<br/>
Vous pouvez supprimer des lignes en cliquant sur le signe <img src='img/drop.gif' alt='Supprimer'/>. 
Ne supprimer pas de ligne dans un tableau en cours d'utilisation ou qui a été utilisé car si des agents y sont placés, vous ne les verrez plus dans les plannings concernés.<br/>
Une fois terminé, cliquez sur "<b>Valider</b>".<br/>

<a name="config_groupes"></a>
<h4>4.5) Les groupes</h4>
Vous pouvez créer un groupe de tableaux de façon à affecter tel ou tel tableau à chacun des jours de 
la semaine.<br/>
De cette façon, lors de la création du planning du premier jour de la semaine, vous choisirez un 
groupe et les tableaux seront affectés du lundi au samedi (ou dimanche).

<a name="config_lignes_sep"></a>
<h4>4.6) Les lignes de séparation</h4>
Vous pouvez ajouter, modifier, supprimer des lignes de séparation dans le menu "Administration - Les tableaux".<br/>



<a name="agents"></a>
<h3>5) Les agents</h3>
Dans le menu "Administration/Les agents", vous pouvez voir la liste de tous les agents enregistrés dans l'application.<br/>
Vous pouvez filtrer les agents de "service public" et les agents "administratifs" (ne faisant pas de service public).<br/>
Vous pouvez également rechercher un agent en tapant son nom dans le cadre "Rechercher".<br/>

<a name="agent_ajout"></a>
<h4>5.1 ) Ajout d'un agent</h4>
Pour ajouter un agent, cliquez sur le bouton « Ajouter » puis remplissez le formulaire.<br/><br/>
<b><u>1<sup>er</sup> onglet : Informations générales</u></b>
<br/>Les champs nom, prénom et e-mail sont obligatoires.
<br/>Choisissez le statut, le service de rattachement et le nombre d'heures hebdomadaires que l'agent doit faire en service public.
<br/>Dans le menu déroulant « Service public / Administratif » choisissez
« Service public » pour toutes les personnes qui pourront apparaître dans les
plannings, choisissez « administratif » pour les personnes qui doivent agir
sur le planning (gestion des congés, absences, ... ) mais qui n'apparaîtront pas dans les
plannings.<br/>
Complétez le champ "E-mail du responsable" si vous souhaitez qu'il soit notifié des absences de l'agent. (Selon la configuration de l'application).
<br/>
Le champ "Récupération du samedi" est soit une zone de texte dans laquelle vous pouvez saisir des notes, soit un menu déroulant avec les 
options "Prime" et "Temps" (selon la configuration de l'application). S'il s'agit d'un menu déroulant, choisissez l'option voulue par 
l'agent. Cela permettra de calculer le temps ou les primes à reverser à l'agent dans le menu "statistiques"/"Samedis".<br/><br/>

<b><u>2<sup>ème</sup> onglet : Les activités</u></b><br/>
Dans le 2<sup>ème</sup> onglet, vous devez affecter une liste d'activités à l'agent.<br/>
Sélectionnez dans la liste de gauche les activités que pourra effectuer l'agent puis cliquez sur "Attribuer".<br/>
Pour retirer une activité, sélectionnez-la dans la liste de droite puis cliquez sur "Supprimer".<br/>

<br/>
<b><u>3<sup>ème</sup> onglet : L'emploi du temps</u></b><br/>
Dans le 3<sup>ème</sup> onglet, vous devez renseigner l'emploi du temps général de l'agent (heures de présence à la bibliothèque).<br/>
Pour chaque journée (du lundi au samedi/dimanche), sélectionnez les heures d'arrivée et de départ ainsi que les heures de début et de fin de pause.<br/>

<br/>
<b><u>4<sup>ème</sup> onglet : Les droits d'accès</u></b><br/>
Dans le 4<sup>ème</sup> onglet, vous devez renseigner les droits d'accès à l'application.<br/>
Par défaut, un agent peut ajouter ses absences, voir son agenda et accéder au planning en lecture seule. 
Dans ce cas, vous ne devez rien cocher. Vous pouvez ajouter l'accès à la modification de absences de l'agent, la gestion des absences 
(de tous les agents), la gestion du personnel, des postes, la modification du planning et l'accès aux statistiques. 
Le débogage permet d'afficher des informations supplémentaires lors de l'utilisation de l'application (ex : Numéro "ID" des agents ou 
des postes dans les listes, nom des postes et horaires dans le menu déroulant du planning.)<br/>

<br/>
<u><b>Validation</b></u><br/>
Une fois les informations saisies, validez le formulaire (bouton "Valider" en haut à droite).

<a name="agent_modif"></a>
<h4>5.2 ) Modification de la fiche d'un agent</h4>
A partir de la liste des agents, cliquez sur l'icône <img src='img/modif.png' alt='Modifier' /> devant le nom de l'agent choisi.<br/>
Un formulaire s'ouvre avec les informations relatives à l'agent.<br/>
Pour plus d'informations, référez-vous à l'article "Ajout d'un agent".

<a name="agent_pass"></a>
<h4>5.3 ) Modification du mot de passe d'un agent</h4>
La fonction de modification du mot de passe ne doit être utilisée que si l'agent le
demande (oubli ou non-réception).
<br/>Ouvrez la fiche de l'agent (voir Modification de la fiche d'un agent)
<br/>Vérifiez que l'adresse e-mail est bien renseignée. Si elle ne l'est pas, entrez
l'adresse de l'agent puis cliquez sur Valider. Revenez ensuite sur la fiche pour modifier le
mot de passe.
<br/>Cliquez sur « Changer le mot de passe », un nouveau mot de passe sera alors
généré et envoyé par email à l'agent.
<br/>Vous pouvez ensuite fermer la fiche en cliquant sur Annuler ou Valider.

<a name="agent_supp"></a>
<h4>5.4 ) Suppression d'un agent</h4>
Trouvez l'agent dans la liste.
<br/>Cliquez sur la corbeille.
<br/>Remplissez le champ Date de départ puis validez.

<a name="postes"></a>
<h3>6) Les postes</h3>
Dans le menu "Administration/Les postes", vous pouvez voir la liste de tous les postes.<br/>
Vous pouvez rechercher un poste en tapant son nom dans le cadre "Rechercher" puis en cliquant sur "OK".<br/>

<a name="poste_ajout"></a>
<h4>6.1 ) Ajout d'un poste</h4>
Pour ajouter un poste, cliquez sur le bouton « Ajouter » puis complétez le formulaire.<br/>
Remplissez le nom du poste, choisissez son site (si configuration multisites), son étage.<br/>
Cochez la case "Obligatoire" s'il est obligatoire ou "Renfort" s'il s'agit d'un poste de renfort.<br/>
Cochez la case "Non" face à "Bloquant" si vous souhaitez pouvoir placer un agent sur ce poste et sur un autre en même temps.<br/> 
Cochez la case "Non" face à "Statistiques" si vous ne souhaitez pas voir apparaître ce poste dans les statistiques.<br/>
A droite, cochez les activités liées au poste.<br/>
Validez.

<a name="poste_modif"></a>
<h4>6.2 ) Modification d'un poste</h4>
Trouvez le poste dans la liste, cliquez sur l'icône <img src='img/modif.png' alt='Modifier' />
devant le nom du poste à modifier.<br/>
Modifier le formulaire (référez-vous à l'article "Ajout d'un poste" pour plus d'informations).

<a name="poste_supp"></a>
<h4>6.3 ) Suppression d'un poste</h4>
Pour supprimer un poste, cliquez sur l'icône représentant une corbeille devant le nom du poste dans la liste.<br/>
Si un poste est utilisé dans un tableau, il n'est pas possible de le supprimer (la corbeille n'apparaît pas).

<a name="activites"></a>
<h3>7.) Les Activités</h3>
Vous pouvez modifier la liste des activités dans le menu Administration/Les activités.<br/>
Vous pouvez modifier les noms, ajouter des activités, en supprimer (si elles ne sont pas attribuées).

<a name='statistiques'></a>
<h3>8.) Statistiques</h3>
<a name='stat_temps'></a>
<h4>8.1) Feuille de temps</h4>
La feuille de temps est un tableau qui vous permet de voir le nombre d'heures effectuées par jour et par agent 
entre deux dates. Par défaut, le tableau affiche les heures de la semaine courante.<br/>
Les dernières colonnes affichent le total d'heures par agent sur la période, la moyenne hebdomadaire et les quotas d'heures 
sur la période et par semaine. 
Si vous avez plusieurs sites, le total et la moyenne par site sont également affichés dans les dernières colonnes.<br/>
Les deux dernières lignes affichent le total d'heures et le nombre d'agents par jour.

<a name='stat_agent'></a>
<h4>8.2) Statistiques par agent</h4>
Les statistiques par agent affichent un tableau contenant par agent :
<ul>
<li>Le nombre d'heures faites entre les dates choisies</li>
<li>La moyenne d'heures par semaine</li>
<li>Le nombre d'heures et la moyenne hebdomadaire par site (si votre bibliothèque a plusieurs sites)</li>
<li>Les postes occupés</li>
<li>Les heures effectuées par poste</li>
<li>Le nombre de samedis, dimanches et jours feriés travaillés</li>
<li>Les dates des samedis, dimanches et jours feriés travaillés</li>
<li>Le nombre d'heures d'absences ainsi que les jours correspondant.</li>
</ul>
Choisissez la date de début, la date de fin et les agents dans le menu déroulant.<br/>
Vous pouvez sélectionner plusieurs agents à l'aide des touches du clavier CTRL ou MAJ ou en cliquant sur "Tous".<br/>
Cliquez ensuite sur le bouton "OK".

<a name='stat_service'></a>
<h4>8.3) Statistiques par service</h4>
Affiche les mêmes informations que les statistiques par agents en les groupant par service.

<a name='stat_statut'></a>
<h4>8.4) Statistiques par statut</h4>
Affiche les mêmes informations que les statistiques par agents en les groupant par statut.

<a name='stat_poste'></a>
<h4>8.5) Statistiques par poste</h4>
Les statistiques par poste affichent un tableau contenant par poste :
<ul>
<li>Le nombre d'heures faites entre les dates choisies</li>
<li>La moyenne d'heures par semaine</li>
<li>La moyenne d'heures par jour</li>
<li>Le nom des agents affectés</li>
<li>Le nombre d'heures effectuées par agent</li>
</ul>
Choisissez la date de début et la date de fin.<br/>
Choisissez le tri désiré (nom du poste, étage, obligatoire/renfort, nombre d'heure croissant/ décroissant).<br/>
Choisissez les postes dans le menu déroulant. Vous pouvez sélectionner plusieurs postes à l'aide des touches du clavier CTRL ou MAJ ou en cliquant sur "Tous".<br/>
Cliquez ensuite sur le bouton "OK".

<a name='stat_renfort'></a>
<h4>8.6) Statistiques par poste de renfort</h4>
Les statistiques par poste de renfort affichent un tableau contenant par poste de renfort uniquement :
<ul>
<li>Le nombre d'heures faites entre les dates choisies</li>
<li>La moyenne d'heures par semaine</li>
<li>La moyenne d'heures par jour</li>
<li>Les dates et horaires d'ouvertures </li>
<li>Le nombre d'heures faites par jour et par tranche horaire</li>
</ul>
Choisissez la date de début et la date de fin.<br/>
Choisissez le tri désiré (nom du poste, étage, nombre d'heure croissant/ décroissant).<br/>
Choisissez les postes dans le menu déroulant. Vous pouvez sélectionner plusieurs postes à l'aide des touches du clavier CTRL ou MAJ ou en cliquant sur "Tous".<br/>
Cliquez ensuite sur le bouton "OK".

<a name='stat_synthese'></a>
<h4>8.7) Statistiques par poste (Synthèse)</h4>
Les statistiques par poste (Synthèse) affichent un tableau contenant par poste :
<ul>
<li>Le nombre d'heures faites entre les dates choisies</li>
<li>La moyenne d'heures par semaine</li>
<li>La moyenne d'heures par jour</li>
<li>La somme des heures tous postes confondus ainsi que la moyenne par semaine et par jour</li>
</ul>
Choisissez la date de début et la date de fin.<br/>
Choisissez le tri désiré (nom du poste, étage, obligatoire/renfort, nombre d'heure croissant/ décroissant).<br/>
Choisissez les postes dans le menu déroulant. Vous pouvez sélectionner plusieurs postes à l'aide des touches du clavier CTRL ou MAJ ou en cliquant sur "Tous".<br/>
Cliquez ensuite sur le bouton "OK".

<a name='stat_samedi'></a>
<h4>8.8) Statistiques par samedi</h4>
Les statistiques par samedi affichent pour chaque agent, le nombre de samedis travaillés, le nombre d'heures de service public correspondant, 
ainsi que les dates et le nombre d'heures de service pour chacune des dates.<br/>
Une colonne affiche pour chaque agent son choix de recevoir une prime ou de récupérer ses heures (temps). Cette information est renseignée 
dans la fiche de l'agent.<br/>
Vous pouvez effectuer un tri sur chaque colonne et filtrer à l'aide du champ "Rechercher" (recherche dans toutes les colonnes).

<a name="informations"></a>
<h3>9) Les informations</h3>
Dans le menu "Administration / Informations", vous pouvez ajouter, modifier et supprimer des messages d'informations qui seront affichés aux dates voulues en haut des plannings. 

<a name="fermeture"></a>
<h3>10) Jours de fermeture</h3>
Dans le menu "Administration / Jours de fermeture", vous pouvez renseigner, pour chaque année universitaire, les jours fériés et les jours de fermeture de la bibliothèque.<br/>
Les jours fériés apparaissent automatiquement, vous pouvez y ajouter vos jours de fermeture en renseignant les dates, un nom et un commentaire. 
Cochez également si le jour est férié et/ou fermé.<br/>
Le nom des jours fériés apparaîtra en haut des plannings et dans les agendas. Vous aurez également les informations sur le nombre 
de jours fériés travaillés ainsi que le nombre d'heures correspondant pour chaque agent dans les statistiques par agent.
</div>