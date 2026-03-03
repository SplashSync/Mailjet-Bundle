<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace App\Command;

use App\Entity\Contact;
use App\Entity\ContactList;
use App\Entity\WebHook;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Seeds the sandbox database with default data for Mailjet API testing.
 *
 * This command populates the database with realistic test data that mimics
 * a typical Mailjet account structure, including:
 * - Contact lists (e.g., Newsletter, Clients, Prospects)
 * - Contact metadata (custom properties like name, SMS, etc.)
 * - Webhooks for event notifications
 * - Sample contacts with properties and list memberships
 *
 * The data is designed to work with the Mailjet connector's test suite
 * and provide a realistic API response structure.
 */
#[AsCommand(name: 'app:seed-data', description: 'Seed sandbox with default Mailjet data')]
class SeedDataCommand extends Command
{
    /**
     * EntityManager instance for database operations.
     */
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    /**
     * Execute the seed command.
     *
     * This method orchestrates the seeding process by calling
     * individual seed methods for each entity type.
     *
     * @param InputInterface $input Command input
     * @param OutputInterface $output Command output
     *
     * @return int Command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>Starting Mailjet sandbox data seeding...</comment>');
        $output->writeln('');

        $this->seedContactList($output);
        $this->seedContactMetadata($output);
        $this->seedWebHook($output);
        $this->seedContacts($output);

        $this->em->flush();
        $output->writeln('');
        $output->writeln('<info>✓ Seed data loaded successfully.</info>');
        $output->writeln('<info>The sandbox is now ready for testing at http://sandbox.mailjet.local</info>');

        return Command::SUCCESS;
    }

    /**
     * Seed contact lists with default data.
     *
     * Creates common contact list categories that would typically exist
     * in a Mailjet account. These lists can be used to organize contacts
     * and target specific groups in email campaigns.
     *
     * @param OutputInterface $output Command output
     */
    private function seedContactList(OutputInterface $output): void
    {
        if ($this->em->getRepository(ContactList::class)->count(array()) > 0) {
            $output->writeln('✓ ContactList already exists, skipping.');

            return;
        }

        // Define common contact list categories
        $lists = array(
            array('name' => 'Newsletter', 'totalSubscribers' => 0),
            array('name' => 'Clients', 'totalSubscribers' => 0),
            array('name' => 'Prospects', 'totalSubscribers' => 0),
            array('name' => 'VIP', 'totalSubscribers' => 0),
            array('name' => 'Partners', 'totalSubscribers' => 0),
        );

        foreach ($lists as $data) {
            $list = new ContactList();
            $list->name = $data['name'];
            $list->totalSubscribers = $data['totalSubscribers'];

            $this->em->persist($list);
        }

        $output->writeln(sprintf('✓ %d ContactLists seeded.', count($lists)));
    }

    private function seedContactMetadata(OutputInterface $output): void
    {
        if ($this->em->getRepository(\App\Entity\ContactMetadata::class)->count(array()) > 0) {
            $output->writeln('ContactMetadata already exists, skipping.');

            return;
        }

        $metadata = array(
            array('name' => 'NOM', 'dataType' => 'str'),
            array('name' => 'PRENOM', 'dataType' => 'str'),
            array('name' => 'SMS', 'dataType' => 'str'),
            array('name' => 'CIVILITE', 'dataType' => 'str'),
            array('name' => 'DATE_NAISSANCE', 'dataType' => 'datetime'),
        );

        foreach ($metadata as $data) {
            $meta = new \App\Entity\ContactMetadata();
            $meta->name = $data['name'];
            $meta->dataType = $data['dataType'];

            $this->em->persist($meta);
        }

        $output->writeln('ContactMetadata seeded.');
    }

    private function seedWebHook(OutputInterface $output): void
    {
        if ($this->em->getRepository(WebHook::class)->count(array()) > 0) {
            $output->writeln('WebHook already exists, skipping.');

            return;
        }

        $webhook = new WebHook();
        $webhook->url = 'https://example.com/webhook';
        $webhook->eventType = 'unsub';
        $webhook->isBackup = false;
        $webhook->status = 'alive';

        $this->em->persist($webhook);
        $output->writeln('WebHook seeded.');
    }

    private function seedContacts(OutputInterface $output): void
    {
        if ($this->em->getRepository(Contact::class)->count(array()) > 0) {
            $output->writeln('Contacts already exist, skipping.');

            return;
        }

        $contacts = array(
            array(
                'email' => 'test1@example.com',
                'isExcludedFromCampaigns' => false,
                'properties' => array('firstname' => 'John', 'lastname' => 'Doe'),
                'contactLists' => array(1, 2),
            ),
            array(
                'email' => 'test2@example.com',
                'isExcludedFromCampaigns' => true,
                'properties' => array('firstname' => 'Jane', 'lastname' => 'Smith'),
                'contactLists' => array(1, 3),
            ),
        );

        foreach ($contacts as $data) {
            $contact = new Contact();
            $contact->email = $data['email'];
            $contact->isExcludedFromCampaigns = $data['isExcludedFromCampaigns'];
            $contact->properties = $data['properties'];
            $contact->setContactLists($data['contactLists']);

            $this->em->persist($contact);
        }

        $output->writeln('Contacts seeded.');
    }
}
