---
lang: en
permalink: configure/options
title: Connector options
description: API credentials, contact list, and webhooks configuration.
updated: 2026-09-24
---

### API connection

| Option | Role |
|---|---|
| **API Key** | The public key Splash uses to reach your Mailjet account |
| **Secret Key** | The secret key that goes with it |

Changing the keys reloads your lists on the next save. Check the selected list afterwards: it may
no longer exist in the new account.

### List

| Option | Role |
|---|---|
| **List** | The Mailjet list contacts are written to |

The selector offers the lists read from your Mailjet account. It only appears once valid keys
have been saved.

> [!IMPORTANT]
> Changing the list does **not** move the contacts already synchronized. The previous ones stay
> in their list, and only what Splash writes afterwards goes to the new one.

### Webhooks

The **Update of WebHooks** block reports whether Mailjet can notify Splash. Run the update when
it reports a failure: the connector then creates or repairs the webhooks in your account.

Webhooks are attached to the account and to the synchronized list: run the update again after
changing either of them.

### Self-test

The **Mailjet Connector Configuration** self-test checks, in order, that both keys are filled in,
that Mailjet answers, and that a list is selected. Run it after every change to the connection.
