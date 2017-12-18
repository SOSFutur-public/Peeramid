# **Documentation back de Peeramid**

## Installation locale

Ce projet nécessite PHP 7.  
Vous devez modifier deux paramètres dans votre fichier php.ini : upload_max_filesize et post_max_size. Saisissez la 
taille maximum autorisée pour les fichiers uploadés (exemple : 200M). Cette valeur doit être supérieure à celle définie 
pour le paramètre UPLOAD_MAX_SIZE dans la base de données.

### Mise en place du projet

Pour mettre en place le projet, lancez:

````
php composer.phar install
````

Si vous rencontrez une erreur impliquant une limitation de memoire, lancez:

````
php -d memory_limit=-1 composer.phar install
````

>Ces commandes peuvent prendre un certain temps

<br>

Remplissez les paramètres demandés:

  1. ````
     database_host (127.0.0.1):
     ````
     >Si vous souhaitez changer l'hôte pour votre base de donnée vous devez saisir l'hôte et valider en appuyant sur la
     touche “Entré”. Sinon si vous utilisez une base de donnée qui est sur le même serveur que l'application validez
     simplement avec la touche “Entrée”
      
<br>

  2. ````
     database_port (3306):
     ````
     >Si votre base de donnée est sur un port différent, renseignez le, sinon validez.

<br>

  3. ````
     database_name (peeramid):
     ````
     >Ce paramètre vous permet de changer le nom de la base de donnée qui sera créé.

<br>

  4. ````
     database_user (root):
     ````
     >Ce paramètre vous permet de spécifier l'utilisateur pour se connecter à votre base de données. Root par défaut.

<br>

  5. ````
     database_password (null):
     ````
     >Mettez le mot de passe de l'utilisateur que vous avez choisis juste avant. Pas de mot passe par défaut. Il est
     fortement conseillé d’avoir un utilisateur avec un mot de passe.

<br>

  6. ````
     database_driver (pdo_mysql):
     ````
     >Laissez ce paramètre par défaut.

<br>

  7. ````
     database_version (5.7.14):
     ````
     >Laissez ce paramètre par défaut.

<br>

  8. ````
     mailer_transport (smtp):
     ````
     >Le type de protocol utilisé dans l’application pour envoyer des mails, nous conseillons de laisser ce paramètre
     par défaut.

<br>

  9. ````
     mailer_host (127.0.0.1):
     ````
     >Ce paramètre vous permet de définir l’hôte de votre serveur de mail, si vous souhaitez utiliser gmail, yahoo ou
     autre, vous devez récupérer l’adresse du serveur smtp. Sinon si vous utilisez postfix en local (c’est à dire que
     le serveur Postfix est sur la même machine que l’application) alors laissez par défaut.

<br>

  10. ````
      mailer_user (null):
      ````
      >L’utilisateur qui envoie les mails, c’est à dire l’adresse mail qui enverra les messages. Si vous utilisez votre
      serveur Postfix alors vous devez mettre un utilisateur de votre machine par exemple ‘ toto@votredomaine.com ’
      (cf Installation de Postfix). Sinon si vous passez par un serveur smtp tel que gmail ou autre alors vous devez
      mettre un email valide.

<br>

  11. ````
      mailer_password (null):
      ````
      >Correspond au mot de passe de l'utilisateur ou de l’email associé.

<br>

  12. ````
      mailer_path ('/usr/sbin/sendmail -bs'):
      ````
      >Si vous utilisez Postfix et que vous utilisez les paramètres par défaut alors laisser tel quel. En revanche si
      vous n'utilisez pas sendmail mais autre chose alors renseignez le chemin de l'exécutable pour envoyer les mails.
      Sinon laissez par défaut, ce paramètre sera ignoré automatiquement si vous n'utilisez pas Postfix.

<br>

  13. ````
      mailer_alias (null):
      ````
      >Si vous utilisez Postfix, cela correspond à l’adresse mail que le destinataire du mail verra, c’est-à-dire de qui
      le mail vient. Sinon laissez par défaut.

<br>

  14. ````
      front_address (null):
      ````
      >L’url sur laquelle le front est lancé.

<br>

  16. ````
      cors_allow_origin ('*'):
      ````
      >L’url sur laquelle le front est lancé.

<br>

  17. ````
      secret (ThisTokenIsNotSoSecretChangeIt):
      ````
      >Changez cette valeur par une chaîne de caractère qui est secrète à l’application

<br>

  18. ````
      upload.directory (upload/):
      ````
      >Laissez ce paramètre par défaut.

<br>

  19. ````
      jwt_key_pass_phrase (__CaFpa_2017_jWt__cs!):
      ````
      >Changez cette valeur par une chaîne de caractère qui est secrète à l’application et qui servira à générer les
      token d’authentifications.

### Installation de la base de données

Pour créer la base de données, lancez:

````
php bin/console doctrine:database:create
````

Pour créer le schéma de base de données, lancez:

````
php bin/console doctrine:schema:update --force
````

### Peuplement la base de données

Dans un gestionnaire de requêtes SQL, copiez le contenu du fichier peeramid_base.sql puis executez la requête.

Il ne vous reste plus qu'a vous connecter sur la plateforme en tant qu'administrateur pour créer des utilisateurs, des
cours, et des groupes.  
Utiliser les identifiants suivants:

>**Login**: admin  
>**Mot de passe**: test

## Spécifications du projet

Ce projet fonctionne grâce à **[PHP 7](http://php.net/)**
et **[Symfony 3](https://symfony.com/)**.

#

[![SOS Futur](../sosf_logo.png)](https://www.sos-futur.fr/)
###### powered by [SOS Futur](https://www.sos-futur.fr/)