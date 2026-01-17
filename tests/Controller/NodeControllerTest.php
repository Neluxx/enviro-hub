<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\Fixtures\HomeFixtures;
use App\Tests\Fixtures\NodeFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Integration test class for NodeController.
 */
class NodeControllerTest extends WebTestCase
{
    /**
     * Test the Main House page displays all nodes from fixtures.
     */
    public function testMainHousePageDisplaysAllNodes(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/main-house');

        static::assertResponseIsSuccessful();
        static::assertResponseStatusCodeSame(200);

        // Should display 3 nodes for Main House
        static::assertCount(3, $crawler->filter('.card'));

        // Extract all node titles from the page
        $nodeTitles = $crawler->filter('.card-title')->each(function ($node) {
            return $node->text();
        });

        // Verify each node is displayed
        static::assertContains('Living Room Sensor', $nodeTitles);
        static::assertContains('Bedroom Sensor', $nodeTitles);
        static::assertContains('Kitchen Sensor', $nodeTitles);

        // Check that node links contain the home identifier and UUID
        static::assertSelectorExists('a[href="/main-house/550e8400-e29b-41d4-a716-446655440001"]');
        static::assertSelectorExists('a[href="/main-house/550e8400-e29b-41d4-a716-446655440002"]');
        static::assertSelectorExists('a[href="/main-house/550e8400-e29b-41d4-a716-446655440003"]');
    }

    /**
     * Test the Guest House page displays all nodes from fixtures.
     */
    public function testGuestHousePageDisplaysAllNodes(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/guest-house');

        static::assertResponseIsSuccessful();
        static::assertResponseStatusCodeSame(200);

        // Should display 2 nodes for Guest House
        static::assertCount(2, $crawler->filter('.card'));

        // Extract all node titles from the page
        $nodeTitles = $crawler->filter('.card-title')->each(function ($node) {
            return $node->text();
        });

        // Verify each node is displayed
        static::assertContains('Guest Room Sensor', $nodeTitles);
        static::assertContains('Guest Bathroom Sensor', $nodeTitles);

        // Check that node links contain the home identifier and UUID
        static::assertSelectorExists('a[href="/guest-house/550e8400-e29b-41d4-a716-446655440004"]');
        static::assertSelectorExists('a[href="/guest-house/550e8400-e29b-41d4-a716-446655440005"]');
    }

    /**
     * Test the Garden Shed page displays all nodes from fixtures.
     */
    public function testGardenShedPageDisplaysAllNodes(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/garden-shed');

        static::assertResponseIsSuccessful();
        static::assertResponseStatusCodeSame(200);

        // Should display 1 node for Garden Shed
        static::assertCount(1, $crawler->filter('.card'));

        // Extract all node titles from the page
        $nodeTitles = $crawler->filter('.card-title')->each(function ($node) {
            return $node->text();
        });

        // Verify the node is displayed
        static::assertContains('Shed Sensor', $nodeTitles);

        // Check that node links contain the home identifier and UUID
        static::assertSelectorExists('a[href="/garden-shed/550e8400-e29b-41d4-a716-446655440006"]');
    }

    /**
     * Test that non-existent home returns 404.
     */
    public function testNonExistentHomeReturns404(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $client->request('GET', '/non-existent-home');

        static::assertResponseStatusCodeSame(404);
    }

    /**
     * Load fixtures for testing.
     */
    private function loadFixtures($client): void
    {
        $container = $client->getContainer();
        $doctrine = $container->get('doctrine');
        $entityManager = $doctrine->getManager();

        $loader = new Loader();
        $loader->addFixture(new HomeFixtures());
        $loader->addFixture(new NodeFixtures());

        $purger = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
