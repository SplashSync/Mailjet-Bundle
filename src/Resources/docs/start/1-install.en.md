---
lang: en
permalink: start/install
title: Install the Mailjet connector
description: API and secret keys, connection setup in Splash, list selection, checks and webhooks activation.
updated: 2026-09-24
---

### Requirements

- A **Mailjet** account, with at least one contact list.
- Your **Mailjet API credentials**: an API key and a secret key, found in your Mailjet account
  under *API Key Management*.
- An active **Splash Sync Premium** account.

> [!CAUTION]
> These keys grant **full access** to your Mailjet account. Do not share them, and never send
> them by e-mail.

### Step 1 — Create the Mailjet connection

From your Splash account, add a new **Mailjet API V3** connection.

The connector talks directly to the Mailjet API: there is nothing to install on the Mailjet side.

### Step 2 — Enter your credentials

Fill in the connection fields:

| Field | Content |
|---|---|
| **API Key** | The public key of your Mailjet account |
| **Secret Key** | The secret key that goes with it |

Save. The connector calls Mailjet, reads your account and **loads your contact lists**.

> [!NOTE]
> The list selector only appears once valid keys have been saved: Splash has to read your lists
> from Mailjet before it can offer them.

### Step 3 — Choose the list to synchronize

Edit the connection again. A **List** selector now shows your Mailjet lists: pick the one your
customers must be written to.

### Step 4 — Check the connection

Run the **Mailjet Connector Configuration** self-test. It checks that both keys are filled in,
and that Splash can reach the Mailjet API with them.

If the test fails, check the secret key first: it is the one most often mistyped.

### Step 5 — Enable webhooks

Webhooks let Mailjet **notify Splash in real time** whenever a contact changes. Without them,
changes made in Mailjet are not automatically reported.

In the **Update of WebHooks** block of your connection, run the update. The connector creates or
updates the required webhooks in your Mailjet account.

The block should then display that the configuration is done.

> [!TIP]
> Run the update again if the block reports a failure, for example after changing your keys.

### What's next?

Your connection is ready. Read the **Connector options**, then enable synchronization for your
customers.
