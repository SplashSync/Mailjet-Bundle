---
lang: fr
permalink: configure/options
title: Options du connecteur
description: Identifiants d'API, liste de contacts, et configuration des webhooks.
updated: 2026-09-24
---

### Connexion à l'API

| Option | Rôle |
|---|---|
| **API Key** | La clé publique avec laquelle Splash joint votre compte Mailjet |
| **Secret Key** | La clé secrète associée |

Changer les clés recharge vos listes au prochain enregistrement. Vérifiez ensuite la liste
sélectionnée : elle peut ne plus exister dans le nouveau compte.

### Liste

| Option | Rôle |
|---|---|
| **Liste** | La liste Mailjet où sont écrits les contacts |

Le sélecteur propose les listes lues dans votre compte Mailjet. Il n'apparaît qu'une fois des
clés valides enregistrées.

> [!IMPORTANT]
> Changer de liste ne **déplace pas** les contacts déjà synchronisés. Les précédents restent dans
> leur liste, et seul ce que Splash écrit ensuite part dans la nouvelle.

### Webhooks

Le bloc **Mise à jour des WebHooks** indique si Mailjet peut notifier Splash. Lancez la mise à
jour lorsqu'il signale un échec : le connecteur crée ou répare alors les webhooks dans votre
compte.

Les webhooks sont attachés au compte et à la liste synchronisée : relancez la mise à jour après
avoir changé l'un ou l'autre.

### Auto-test

L'auto-test **Configuration du connecteur Mailjet** vérifie, dans l'ordre, que les deux clés sont
renseignées, que Mailjet répond, et qu'une liste est sélectionnée. Lancez-le après chaque
modification de la connexion.
