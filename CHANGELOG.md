# Changelog

## Corrections en cours

- Noms des nouveaux équipements compatibles avec les colonnes UTF-8 Jeedom : caractères Unicode sur quatre octets représentés par leur code, sans modifier les notes Google.

- Synchronisation des notes sans liste : suppression du count(null) qui interrompait le traitement sous PHP 8.
- Aide détaillée pour récupérer oauth_token lorsque la page Google reste en chargement.

## 1.01

- Traductions françaises : restauration des chemins Jeedom complets et des barres obliques échappées.

- Connexion Google par échange du cookie oauth_token avec gpsoauth ; validation Keep avant enregistrement, secrets transmis via stdin, compatibilité authenticate/resume et erreur lisible du login par mot de passe.
