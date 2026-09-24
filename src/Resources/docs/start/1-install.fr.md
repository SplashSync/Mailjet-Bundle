---
lang: fr
permalink: start/install
title: Installer le connecteur Mailjet
description: Clés d'API et secrète, création de la connexion dans Splash, choix de la liste, vérifications et activation des webhooks.
updated: 2026-09-24
---

### Prérequis

- Un compte **Mailjet**, avec au moins une liste de contacts.
- Vos **identifiants d'API Mailjet** : une clé d'API et une clé secrète, disponibles dans votre
  compte Mailjet sous *API Key Management*.
- Un compte **Splash Sync Premium** actif.

> [!CAUTION]
> Ces clés donnent un **accès complet** à votre compte Mailjet. Ne les partagez pas, et ne les
> envoyez jamais par e-mail.

### Étape 1 — Créer la connexion Mailjet

Depuis votre compte Splash, ajoutez une nouvelle connexion **Mailjet API V3**.

Le connecteur dialogue directement avec l'API Mailjet : il n'y a rien à installer côté Mailjet.

### Étape 2 — Saisir vos identifiants

Renseignez les champs de la connexion :

| Champ | Contenu |
|---|---|
| **API Key** | La clé publique de votre compte Mailjet |
| **Secret Key** | La clé secrète associée |

Enregistrez. Le connecteur appelle Mailjet, lit votre compte et **charge vos listes de contacts**.

> [!NOTE]
> Le sélecteur de liste n'apparaît qu'une fois des clés valides enregistrées : Splash doit
> d'abord lire vos listes dans Mailjet pour pouvoir vous les proposer.

### Étape 3 — Choisir la liste à synchroniser

Éditez à nouveau la connexion. Un sélecteur **Liste** affiche désormais vos listes Mailjet :
choisissez celle où vos clients doivent être écrits.

### Étape 4 — Vérifier la connexion

Lancez l'auto-test **Configuration du connecteur Mailjet**. Il vérifie que les deux clés sont
renseignées, et que Splash joint l'API Mailjet avec celles-ci.

Si le test échoue, vérifiez d'abord la clé secrète : c'est celle que l'on saisit le plus souvent
de travers.

### Étape 5 — Activer les webhooks

Les webhooks permettent à Mailjet de **notifier Splash en temps réel** dès qu'un contact change.
Sans eux, les modifications faites dans Mailjet ne remontent pas automatiquement.

Dans le bloc **Mise à jour des WebHooks** de votre connexion, lancez la mise à jour. Le
connecteur crée ou met à jour les webhooks nécessaires dans votre compte Mailjet.

Le bloc doit alors indiquer que la configuration est faite.

> [!TIP]
> Relancez la mise à jour si le bloc signale un échec, par exemple après un changement de clés.

### Et ensuite ?

Votre connexion est prête. Consultez les **Options du connecteur**, puis activez la
synchronisation de vos clients.
