# Dockerisons PLB

## Les sevices disponibles

 1. php.plb, qui contient la partie Web du projet
 2. nginx.plb, qui contient la partie serveur du projet
 3. mariadb.plb, qui contiendra une base de données suffisamment complète pour les tests. Il est possible de se passer de ce service lors de l'installation de PlanningBiblio.

## Prérequis à l'installation

- [ ] Avoir installé [Docker](https://doc.ubuntu-fr.org/docker)

## Installation

 *Si vous avez besoin d'installer le projet PlanningBiblio sur votre machine, exécutez la commande suivante :*

    git clone https://github.com/PlanningBiblio/PlanningBiblio.git 

### 1. Rendez-vous à la racine de votre projet depuis votre terminal

### 2. Vérifiez que les fichiers et répertoires suivants sont présents à la racine de votre projet 
- [ ] le répertoire docker/
- [ ] les fichiers
    - [ ] docker-compose.yml
    - [ ] Dockerfile-php
    - [ ] makefile

Si les fichiers ne sont pas présents dans votre répertoire, il suffira de les copier avec la commande cp : *cp chemin_d'origine_du_fichier/nom_du_fichier chemin_vers_le_projet/nom_du_fichier*
Exemple :

    cp ~/Downloads/makefile ~/PlanningBiblio/makefile

### 3. Depuis la racine de votre projet, lancez make

Le fichier makefile contient toutes les instructions nécessaires pour installer l'image Docker dont vous aurez besoin en local. 

Il fera lui-même appel au fichier *docker-compose.yml*, pour installer les services requis et à *Dockerfile-php* pour créer l'image php nécessaire au chargement de PlanningBiblio.

### 4. Paramétrez votre projet

Au lancement de install.sh, vous devrez interagir depuis votre terminal pour paramétrer votre base de données. 

Voici les informations à entrer* :
 **Si vous ne voyez pas un champ dans cette liste, appuyez sur Entrée, cela entrera la valeur par défaut.* 
    - DB HOST : le domaine de la base de données
        - si vous voulez votre base de données, entrez votre domaine
        - si vous n'avez pas de base de données, entrez mariadb.plb
    - DB Admin User : root
    - DB Admin Pass : biblibre *(si vous souhaitez utiliser mariadb.plb)*
    - DB User : planningbadmin *(si vous souhaitez utiliser mariadb.plb)*
    - DB Pass : DEVplb21 *(si vous souhaitez utiliser mariadb.plb)*
    - DB Name : planningbiblio
    - Planning Biblio admin's password : DEVplb21

Chargez la page localhost depuis votre navigateur préféré : la page d'accueil de PlanningBiblio s'ouvre !

Bonne continuation avec Docker et PlanningBiblio !
