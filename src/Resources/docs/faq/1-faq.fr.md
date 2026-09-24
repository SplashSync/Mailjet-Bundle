---
lang: fr
permalink: faq/questions
title: Questions fréquentes
description: Réponses aux questions courantes sur le connecteur Mailjet.
updated: 2026-09-24
---

## Questions fréquentes {.faq}

### Le sélecteur de liste est vide, ou n'apparaît pas

Splash lit vos listes dans Mailjet avec vos identifiants. Si le sélecteur manque, une clé est
absente ou refusée : enregistrez d'abord une **API Key** et une **Secret Key** valides, puis
éditez à nouveau la connexion.

Si les clés sont valides mais que votre compte ne contient aucune liste, créez-en une dans
Mailjet.

### Les modifications faites dans Mailjet ne remontent pas dans Splash

Vérifiez le bloc **Mise à jour des WebHooks** de votre connexion, et lancez la mise à jour
lorsqu'il signale un échec.

Sans webhooks valides, Mailjet ne peut pas notifier Splash de ses changements.

### Un client n'a pas été écrit dans Mailjet

Vérifiez que le client porte une **adresse e-mail** : c'est l'identifiant d'un contact Mailjet,
et un client qui n'en a pas ne peut pas être écrit.

Vos journaux Splash nomment les clients refusés, et pourquoi.

### Un contact est synchronisé mais ne reçoit rien

Regardez **Exclu des campagnes** : un contact exclu continue d'être mis à jour par Splash, mais
Mailjet ne lui envoie rien. Vérifiez également **Opt-in**.

### Une propriété de mon compte manque dans les mappings

Le connecteur lit les propriétés de contact de votre compte Mailjet au chargement de la
connexion. Rechargez votre connexion après avoir ajouté une propriété dans Mailjet.

### Le connecteur supprime-t-il des contacts ?

Non. Supprimer un client dans votre boutique ne supprime jamais le contact Mailjet. Les
suppressions se font depuis Mailjet, et sont ensuite signalées à Splash.
