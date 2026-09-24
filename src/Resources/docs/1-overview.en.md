---
lang: en
permalink: overview
title: Mailjet Connector
description: Synchronize your customers with your Mailjet contact lists, with their properties and their subscription state.
updated: 2026-09-24
---

### Overview

Mailjet is an e-mail marketing and transactional e-mail platform.
The Mailjet connector links your Mailjet account to Splash through the Mailjet API V3: the
customers of your shops and your ERP become **contacts of a Mailjet list**, and stay up to date
there.

Nothing needs to be installed on the Mailjet side: the connection is fully managed by Splash,
using your API credentials.

### Synchronized objects

| Object | Role |
|---|---|
| **Customer** | A contact of your Mailjet list: e-mail, properties and subscription state |

### How it works

- A customer written in Splash is **created or updated** in your Mailjet list, identified by its
  e-mail address.
- The **contact properties** of your Mailjet account are exposed as fields: they map to your
  other applications like any other field.
- The **subscription state** of a contact in the list is readable and writable, as well as its
  opt-in and its exclusion from campaigns.
- Mailjet **notifies Splash** whenever a contact changes on its side, through webhooks the
  connector configures for you.

### Good to know

- The connector works on **one list at a time**: the list chosen in the connection settings is
  the one contacts are written to.
- A contact **excluded from campaigns** in Mailjet stays synchronized: the exclusion is a field,
  not a deletion.
- Splash never deletes a customer of your shop because a contact was removed in Mailjet.

### Getting started

1. **Install the connector** and check the connection (*Getting started* section).
2. Choose your **list** and adjust the options (*Configuration* section).
3. Read how **contacts** are synchronized (*Usage* section).
