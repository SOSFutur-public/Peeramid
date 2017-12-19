# **Documentation front de Peeramid**

## Serveur de developpement

#### Installation

  1. Allez dans le dossier "front":
  
     ````
     cd front
     ````

  2. Installez **Node.js** depuis le [site officiel](https://nodejs.org/fr/).
  
  3. Pour installer les paquets, lancez:
     
     ````
     npm install && install.sh
     ````

#### Utilisation

  1. Si de nouveaux paquets ont été installés ou mis à jour, lancez à nouveau:
     
     ````
     npm install && install.sh
     ````

  2. Editez le fichier d'environnement:

      Ouvrez le fichier "environment.ts" dans le dossier "src/environments/", puis remplacez 'api_url' et 'upload_url'.
      ````
      api_url: '/* votre chemin d'accès à l'api */',
        // ex: 'http://localhost/Peeramid/back/web/api/'
      upload_url: '/* votre chemin d'accès aux fichiers uploadés */'
        // ex: 'http://localhost/Peeramid/back/web/api/'
      ````

  3. Pour démarrer le serveur de développement, lancez:
  
      ````
      ng serve
      ````
      
      >Cette commande doit être lancée dans le dossier "front"

  4. Pour accéder à la plateforme, naviguez jusqu'à [http://localhost:4200/](http://localhost:4200/).

     *Le \"Hot reloading\" actualise la page à chaque changement dans le code source.*

## Serveur de production

Pour lancer la construction de la plateforme, lancez:

````
ng build
````

Pour lancer la construction en production, lancez:

````
ng build -prod
````

## Spécifications du projet

Ce projet fonctionne grâce à **[Angular 4](https://angular.io/)**.

Ce projet est implémenté grâce à **[Angular CLI](https://github.com/angular/angular-cli/blob/master/README.md)**.

Pour avoir de l'aide à propos du CLI, lancez:
````
ng help
````

#

[![SOS Futur](../sosf_logo.png)](https://www.sos-futur.fr/)
###### powered by [SOS Futur](https://www.sos-futur.fr/)