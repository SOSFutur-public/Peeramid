# **Peeramid back documentation**

## Local installation

This project requires PHP 7.  
You need to modify two settings in your php.ini file: upload_max_filesize and post_max_size. Set these to the maximum 
size allowed for uploaded files (example: 200M). This value should be higher than the UPLOAD_MAX_SIZE setting defined in 
the database.

### Project setup

To setup the project, run:
````
php composer.phar install
````

If you encounter an error involving a memory limitation, run:

````
php -d memory_limit=-1 composer.phar install
````

>These commands can take some time

<br>

Fulfill the asked settings:

  1. ````
     database_host (127.0.0.1):
     ````
     >If you wish to change the database host you must type the host and validate by pressing "enter". Otherwise, if you
     are using a database located on the same server as the application, simply validate by pressing "enter". 
      
<br>

  2. ````
     database_port (3306):
     ````
     >If your database is located on another port, inform it, otherwise validate.

<br>

  3. ````
     database_name (peeramid):
     ````
     >This setting allows you to change the database name to be created.

<br>

  4. ````
     database_user (root):
     ````
     >This setting allows you to specify the user used to connect to the database. Root is the default user.

<br>

  5. ````
     database_password (null):
     ````
     >If you are using another user than root, specify is password. Otherwise press "enter" to use the default *null* password.
     You are advised to use a user with a password.
  
<br>

  6. ````
     database_driver (pdo_mysql):
     ````
     >Leave this setting to default.

<br>

  7. ````
     database_version (5.7.14):
     ````
     >Leave this setting to default.

<br>

  8. ````
     mailer_transport (smtp):
     ````
     >The protocol type used within the mailing application, you are advised to leave this setting to default.

<br>

  9. ````
     mailer_host (127.0.0.1):
     ````
     >This setting allows you to define the mailing server host, if wish to use gmail, yahoo or anything else, you must
     get the smtp server address. Otherwise, if you are using Postfix locally (meaning that the Postfix server is located on
     the same machine as the application) then leave it to default.

<br>

  10. ````
      mailer_user (null):
      ````
      >The user sending mails, meaning the mailing adress sending the messages. If you are using your Postfix server,
      you must set a user located on your machine, as an example ' toto@yourdomain.com ' (cf Postfix installation).
      Otherwise, if you are using a smtp server such as gmail or anything else, then you must set a valid mailing
      address.

<br>

  11. ````
      mailer_password (null):
      ````
      >The user or mailing address password.

<br>

  12. ````
      mailer_path ('/usr/sbin/sendmail -bs'):
      ````
      >If you are using Postfix and the default settings, then leave it to default. If you are not using sendmail
      but something else, then inform the path to the executable sending mails. Otherwise leave it to default, this
      setting will be ignored automatically if you are not using Postfix.

<br>

  13. ````
      mailer_alias (null):
      ````
      >If you are not using Postfix, it refers to the mailing address the mail recipient will see, meaning from you who
      the mail have been send. Otherwise leave it to default.

<br>

  14. ````
      front_address (null):
      ````
      >The url on which the front server is running.

<br>

  16. ````
      cors_allow_origin ('*'):
      ````
      >The url on which the front server is running. Leave it to default.

<br>

  17. ````
      secret (ThisTokenIsNotSoSecretChangeIt):
      ````
      >Change this value to a character string secret to the application.

<br>

  18. ````
      upload.directory (upload/):
      ````
      >Leave this setting to default.

<br>

  19. ````
      jwt_key_pass_phrase (__CaFpa_2017_jWt__cs!):
      ````
      >Change this value to a character string secret to the application and which will be used to generate
      authentication tokens.

### Database installation

To create the database, run:

````
php bin/console doctrine:database:create
````

To create the database schema, run:

````
php bin/console doctrine:schema:update --force
````

### Database peopling

Within a SQL requests manager, copy the peeramid_base.sql file content then execute the request.

Then you only need to log you on the platform as administrator to create users, lessons and groups.  
Use the following identifiers:

>**Login**: admin  
>**Password**: test

## Project specifications

This project is powered by **[PHP 7](http://php.net/)**
and **[Symfony 3](https://symfony.com/)**.

#

[![SOS Futur](../../../sosf_logo.png)](https://www.sos-futur.fr/)
###### powered by [SOS Futur](https://www.sos-futur.fr/)