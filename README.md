# osmose-dialyse

Osmose Dialyse est une application web permettant de mettre en relation les personnes sous dialyse avec des centres de dialyse.  
L’objectif est de faciliter la prise de rendez-vous ponctuelle (déplacements professionnels, vacances, etc.) via une plateforme unique.

---

## 🚀 Fonctionnalités principales

### Interface Patient (Symfony 7.3 + Twig)
- Création et gestion de compte patient
- Mise à jour des informations médicales liées à la dialyse
- Recherche de centres disponibles
- Demande de créneaux de dialyse sur des dates spécifiques

### Interface Centre de Dialyse (Symfony 7.3 + Angular)
- Gestion des demandes des patients
- Mise à jour des horaires et disponibilités
- Validation / refus des demandes
- Back-office moderne avec Angular

### API
- Communication sécurisée entre l’interface patient et l’interface centre
- Échanges JSON via des endpoints RESTful
- Gestion des authentifications et permissions par JWT

---

## 🛠️ Stack technique

- **Backend :** Symfony 7.3 (PHP 8.3)
- **Frontend patient :** Twig + jQuery + AssetMapper
- **Frontend centre :** Angular 17
- **Base de données :** MySQL
- **Authentification :** JWT
- **Serveur de dev local :** WAMP + Virtual Hosts
- **Serveur de prod :** VPS OVH (Ubuntu, Nginx, MySQL, PHP-FPM)

---

## ⚙️ Installation en local

### Prérequis
- PHP ≥ 8.3
- Composer
- Node.js ≥ 18 & npm/pnpm
- MySQL
- WAMP/MAMP/LAMP (ou Docker si souhaité)

### Étapes (interface patient)

git clone https://github.com/ton-compte/osmose-dialyse-patient.git
cd osmose-dialyse-patient
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start


### Les mails :

Pour la gestion des emails, le projet utilise un service SMTP. Vous devez configurer les paramètres SMTP dans le fichier
`.env` du backend.
le consumer est configuré pour envoyer les emails en arrière-plan via Symfony Messenger. Avec Supervisor, le consumer
sera lancé automatiquement.

- Pour lancer le supervisor, exécutez la commande suivante :
  `sudo supervisorctl restart symfony-messenger`
- Aprés une modification du code, il est recommandé de redémarrer le consumer pour prendre en compte les changements.
  `sudo supervisorctl reread`
  `sudo supervisorctl update`
- Vérification du statut du process :
  `sudo supervisorctl status symfony-messenger`
- Vérifie les logs si tu veux t’assurer que ça consomme bien :
  `tail -f /var/log/supervisor/symfony-messenger.log`
- ou
  `tail -f /var/log/symfony/messenger_error.log`

### Configuration de la base de données

Pour configurer la base de données, modifiez le fichier `.env` dans le dossier `backend/` avec vos paramètres de
connexion.
Commandes utiles :

- Export de la bdd sur VPS : `mysqldump --no-tablespace -u NOM_UTILISATEUR -p NOM_BDD > sauvegarde.sql`

PMA username : `admin_immo_user`
PMA password : `StreetwarriorCity!`

### Script de déploiement
deploy.sh : script bash pour déployer l’application sur le serveur de production OVH.
commande pour les log : tail -n 100 /var/log/osmose-deploy.log
commande suppression log : sudo rm -rf /var/log/osmose-deploy.log
commande lancer le script : /var/www/osmose/deploy.sh

### Verifier l'etat du serveur
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
sudo systemctl status mysql
### Redémarrer les services
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl restart mysql
sudo systemctl enable nginx

### Commandes utiles
Vérfier les autorisations du dossier : ls -la 

### Mode maintenance
Id : guilloux
Mot de passe : Dialyseprojet2025

## License
[Insérer votre licence ici]