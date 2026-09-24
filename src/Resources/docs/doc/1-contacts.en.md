---
lang: en
permalink: doc/contacts
title: Contacts
description: How customers become Mailjet contacts, which properties are exposed, and how subscription is handled.
updated: 2026-09-24
---

### Identification

A contact is identified by its **e-mail address**. Writing a customer whose e-mail already exists
in Mailjet updates that contact, it never creates a duplicate.

Changing the e-mail address of a customer therefore **moves** the contact: Mailjet sees a new
identity.

### Exposed fields

| Field | Direction | Notes |
|---|---|---|
| **E-mail** | read & write | The identifier of the contact |
| **Username** | read & write | The name Mailjet displays for the contact |
| **Is Subscribed in List** | read & write | Membership of the synchronized list |
| **Is Opt-In** | read & write | Consent recorded by Mailjet |
| **Is Excluded from Campaigns** | read & write | Excludes the contact from every campaign |
| **Properties** | read & write | Every contact property defined in your Mailjet account |

### Properties

Mailjet contact properties are not a fixed list: they are the ones **you defined** in your
account. The connector reads them and exposes each one as a field, under its Mailjet name.

They appear in your mappings like any other field, and their type follows the one declared in
Mailjet.

> [!TIP]
> Add a property in Mailjet, then reload your connection: the new field is available for mapping
> right away.

### Subscription and exclusion

Three states are not the same thing:

- **Is Subscribed in List** says whether the contact belongs to the synchronized list.
- **Is Opt-In** records the consent, whatever the list.
- **Is Excluded from Campaigns** blocks every send, in every list.

A contact excluded from campaigns stays synchronized: Splash keeps writing its fields, Mailjet
simply never mails it.

### What is never done

- Splash **never deletes** a Mailjet contact when a customer is deleted in your shop.
- Mailjet **statistics** — opens, clicks, bounces — are not synchronized.
- Contacts are not imported in bulk: they reach Mailjet as your other applications write them.
