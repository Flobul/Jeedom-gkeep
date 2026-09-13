# Jeedom-gkeep

[![License](https://badgen.net/github/license/Flobul/Jeedom-gkeep?icon=github)](https://github.com/Flobul/Jeedom-gkeep)
[![Language](https://badgen.net/static/Language/PHP/blue?icon=github)](https://github.com/Flobul/Jeedom-gkeep)
[![Last commit](https://badgen.net/github/last-commit/Flobul/Jeedom-gkeep?icon=github)](https://github.com/Flobul/Jeedom-gkeep/commits)
[![Open issues](https://badgen.net/github/open-issues/Flobul/Jeedom-gkeep?icon=github)](https://github.com/Flobul/Jeedom-gkeep/issues)
[![Open pull requests](https://badgen.net/github/open-prs/Flobul/Jeedom-gkeep?icon=github)](https://github.com/Flobul/Jeedom-gkeep/pulls)

Synchronise ses notes depuis Google Keep.
Et crée un équipement par note.
Prend en compte l'ordre, les cases des items.

## Connexion Google avec navigateur

Si la connexion avec mot de passe d’application échoue (`UNKNOWN_ERR` notamment), utilisez la méthode avec navigateur :

1. Relancez les dépendances pour actualiser gpsoauth et gkeepapi.
2. Dans la configuration, renseignez l’adresse du compte Google puis sauvegardez.
3. Dans **Connexion Google avec navigateur**, cliquez sur **Ouvrir Google** et authentifiez-vous avec ce même compte, y compris la validation 2FA demandée.
4. Après avoir accepté la demande Google, ouvrez les outils de développement du navigateur, onglet **Application**, puis **Cookies** pour le domaine Google. Copiez la valeur du cookie `oauth_token`. Une page de chargement persistante peut être normale à cette étape.
5. Collez cette valeur dans le champ du plugin, conservez ou renseignez un Android ID de 16 caractères hexadécimaux, puis cliquez sur **Valider la connexion Google**.
6. Après confirmation, rechargez la configuration avant toute nouvelle sauvegarde et lancez la synchronisation.

Le cookie temporaire n’est pas enregistré dans la configuration et est transmis au processus Python par son entrée standard, pas dans ses arguments. Le master token n’est enregistré qu’après vérification de l’accès à Keep. Traitez ces deux secrets comme un mot de passe : ne les publiez pas et ne les joignez pas aux logs. Les comptes déjà connectés continuent à utiliser leur jeton existant.

Ce parcours est celui documenté par [gpsoauth](https://github.com/simon-weber/gpsoauth#alternative-flow) et [gkeepapi](https://gkeepapi.readthedocs.io/en/latest/#authenticating). Il utilise une API non officielle et peut être refusé selon les restrictions du compte Google.
