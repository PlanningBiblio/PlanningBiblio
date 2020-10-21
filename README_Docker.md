# Dockerisons PLB

## Les services disponibles

 1. php.plb, qui contient la partie Web du projet
 2. nginx.plb, qui contient la partie serveur du projet
 3. mariadb.plb, qui contiendra une base de données suffisamment complète pour les tests. Il est possible de se passer de ce service lors de l'installation de PlanningBiblio.

## Prérequis à l'installation

- [ ] Avoir installé [Docker](https://doc.ubuntu-fr.org/docker)

## Installation

 Pour installer le projet PlanningBiblio sur votre machine, exécutez la commande suivante :

```bash
git clone https://github.com/PlanningBiblio/PlanningBiblio.git
```

### 1. Rendez-vous à la racine de votre projet depuis votre terminal

 ```bash
 cd PlanningBiblio/
 ```

### 2. Vérifiez que les fichiers et répertoires suivants sont présents à la racine de votre projet

- [ ] le répertoire docker/
- [ ] les fichiers
  - [ ] docker-compose.yml
  - [ ] Dockerfile-php
  - [ ] makefile

Pour vérifier leur présence, entrez la commande ls
 ```bash
 votresession@votremachine:~/PlanningBiblio$ ls
 ```

Si les fichiers ne sont pas présents dans votre répertoire, il suffira de les importer depuis ce git, puis de les déplacer avec la commande mv. On finira par la suppression du répertoire plb-docker une fois vidé.

```bash
votresession@votremachine:~/PlanningBiblio$ git clone https://github.com/sillydebs/plb-docker.git
votresession@votremachine:~/PlanningBiblio$ mv plb-docker/makefile ../makefile
votresession@votremachine:~/PlanningBiblio$ mv plb-docker/Dockerfile-php ../Dockerfile-php
votresession@votremachine:~/PlanningBiblio$ mv plb-docker/docker-compose.yml ../docker-compose.yml
votresession@votremachine:~/PlanningBiblio$ mv plb-docker/docker ./docker
votresession@votremachine:~/PlanningBiblio$ rm -rf plb-docker
```

### 3. Depuis la racine de votre projet, lancez make depuis votre terminal

```bash
votresession@votremachine:~/PlanningBiblio$ make
```

Le fichier makefile contient toutes les instructions nécessaires pour installer l'image Docker dont vous aurez besoin en local.

Il fera lui-même appel au fichier *docker-compose.yml*, pour installer les services requis et à *Dockerfile-php* pour créer l'image php nécessaire au chargement de PlanningBiblio.

### 4. Paramétrez votre projet

Au lancement de install.sh, vous devrez interagir depuis votre terminal pour paramétrer votre base de données.

Voici les informations à entrer* :
 **Si vous ne voyez pas un champ dans cette liste, appuyez sur Entrée, cela entrera la valeur par défaut.*

- DB HOST : le domaine de la base de données
  - si vous voulez utiliser votre propre base de données, entrez votre domaine
  - si vous n'avez pas de base de données, entrez mariadb.plb
- DB Admin User : root
- DB Admin Pass : biblibre *(si vous souhaitez utiliser mariadb.plb)*
- DB User : planningbadmin *(si vous souhaitez utiliser mariadb.plb)*
- DB Pass : DEVplb21 *(si vous souhaitez utiliser mariadb.plb)*
- DB Name : planningbiblio
- Planning Biblio admin's password : DEVplb21

Chargez la page localhost depuis votre navigateur préféré : la page d'accueil de PlanningBiblio s'ouvre !

Bonne continuation avec Docker et PlanningBiblio !
