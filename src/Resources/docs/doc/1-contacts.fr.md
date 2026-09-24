---
lang: fr
permalink: doc/contacts
title: Contacts
description: Comment les clients deviennent des contacts Mailjet, quelles propriétés sont exposées, et comment l'abonnement est géré.
updated: 2026-09-24
---

### Identification

Un contact est identifié par son **adresse e-mail**. Écrire un client dont l'e-mail existe déjà
dans Mailjet met à jour ce contact, cela ne crée jamais de doublon.

Changer l'adresse e-mail d'un client **déplace** donc le contact : Mailjet y voit une nouvelle
identité.

### Champs exposés

| Champ | Sens | Remarques |
|---|---|---|
| **E-mail** | lecture & écriture | L'identifiant du contact |
| **Nom d'utilisateur** | lecture & écriture | Le nom affiché par Mailjet pour le contact |
| **Abonné à la liste** | lecture & écriture | Appartenance à la liste synchronisée |
| **Opt-in** | lecture & écriture | Consentement enregistré par Mailjet |
| **Exclu des campagnes** | lecture & écriture | Exclut le contact de toutes les campagnes |
| **Propriétés** | lecture & écriture | Chaque propriété de contact définie dans votre compte |

### Propriétés

Les propriétés de contact Mailjet ne sont pas une liste figée : ce sont celles que **vous avez
définies** dans votre compte. Le connecteur les lit et expose chacune comme un champ, sous son
nom Mailjet.

Elles apparaissent dans vos mappings comme n'importe quel autre champ, et leur type suit celui
déclaré dans Mailjet.

> [!TIP]
> Ajoutez une propriété dans Mailjet, puis rechargez votre connexion : le nouveau champ est
> immédiatement disponible pour le mapping.

### Abonnement et exclusion

Trois états qu'il ne faut pas confondre :

- **Abonné à la liste** dit si le contact appartient à la liste synchronisée.
- **Opt-in** enregistre le consentement, quelle que soit la liste.
- **Exclu des campagnes** bloque tout envoi, dans toutes les listes.

Un contact exclu des campagnes reste synchronisé : Splash continue d'écrire ses champs, Mailjet
ne lui envoie simplement jamais rien.

### Ce qui n'est jamais fait

- Splash ne **supprime jamais** un contact Mailjet quand un client est supprimé dans votre
  boutique.
- Les **statistiques** Mailjet — ouvertures, clics, rebonds — ne sont pas synchronisées.
- Les contacts ne sont pas importés en masse : ils arrivent dans Mailjet au fur et à mesure que
  vos autres applications les écrivent.
