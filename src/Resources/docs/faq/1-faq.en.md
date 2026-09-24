---
lang: en
permalink: faq/questions
title: Frequently asked questions
description: Answers to common questions about the Mailjet connector.
updated: 2026-09-24
---

## Frequently asked questions {.faq}

### The list selector is empty, or does not appear

Splash reads your lists from Mailjet with your credentials. If the selector is missing, a key is
either absent or refused: save a valid **API Key** and **Secret Key** first, then edit the
connection again.

If the keys are valid but your account holds no list, create one in Mailjet.

### Changes made in Mailjet are not reported to Splash

Check the **Update of WebHooks** block of your connection, and run the update when it reports a
failure.

Without valid webhooks, Mailjet cannot notify Splash of its changes.

### A customer was not written to Mailjet

Check that the customer carries an **e-mail address**: it is the identifier of a Mailjet contact,
and a customer without one cannot be written.

Your Splash logs name the customers that were refused, and why.

### A contact is synchronized but receives nothing

Look at **Is Excluded from Campaigns**: an excluded contact keeps being updated by Splash, but
Mailjet never mails it. Check **Is Opt-In** as well.

### A property of my account is missing from the mappings

The connector reads the contact properties of your Mailjet account when the connection is loaded.
Reload your connection after adding a property in Mailjet.

### Does the connector delete contacts?

No. Deleting a customer in your shop never deletes the Mailjet contact. Deletions are done from
Mailjet, and are then reported to Splash.
