---
lang: fr
permalink: overview
title: Connecteur Mailjet
description: Synchronisez vos clients avec vos listes de contacts Mailjet, avec leurs propriétés et leur état d'abonnement.
updated: 2026-09-24
---

### Présentation

Mailjet est une plateforme d'e-mail marketing et d'e-mails transactionnels.
Le connecteur Mailjet relie votre compte Mailjet à Splash via l'API Mailjet V3 : les clients de
vos boutiques et de votre ERP deviennent des **contacts d'une liste Mailjet**, et y restent à
jour.

Rien n'est à installer côté Mailjet : la connexion est entièrement gérée par Splash, à partir de
vos identifiants d'API.

### Objets synchronisés

| Objet | Rôle |
|---|---|
| **Client** | Un contact de votre liste Mailjet : e-mail, propriétés et état d'abonnement |

### Fonctionnement

- Un client écrit dans Splash est **créé ou mis à jour** dans votre liste Mailjet, identifié par
  son adresse e-mail.
- Les **propriétés de contact** de votre compte Mailjet sont exposées comme des champs : elles se
  mappent avec vos autres applications comme n'importe quel autre champ.
- L'**état d'abonnement** d'un contact à la liste est lisible et modifiable, tout comme son
  opt-in et son exclusion des campagnes.
- Mailjet **notifie Splash** dès qu'un contact change de son côté, via des webhooks que le
  connecteur configure pour vous.

### Bon à savoir

- Le connecteur travaille sur **une liste à la fois** : celle choisie dans les paramètres de la
  connexion est celle où les contacts sont écrits.
- Un contact **exclu des campagnes** dans Mailjet reste synchronisé : l'exclusion est un champ,
  pas une suppression.
- Splash ne supprime jamais un client de votre boutique parce qu'un contact a été retiré dans
  Mailjet.

### Pour démarrer

1. **Installez le connecteur** et vérifiez la connexion (section *Démarrer*).
2. Choisissez votre **liste** et ajustez les options (section *Configuration*).
3. Lisez comment les **contacts** sont synchronisés (section *Utilisation*).
