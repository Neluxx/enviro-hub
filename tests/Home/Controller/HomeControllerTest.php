<?php

declare(strict_types=1);

namespace App\Tests\Home\Controller;

use App\Tests\Fixtures\HomeFixtures;
use App\Tests\Fixtures\NodeFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Integration test class for HomeController.
 */
class HomeControllerTest extends WebTestCase
{
    /**
     * Test the index page displays all homes from fixtures.
     */
    public function testIndexPageDisplaysAllHomes(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/');

        static::assertResponseIsSuccessful();
        static::assertResponseStatusCodeSame(200);

        // Should display 3 homes from fixtures
        static::assertCount(3, $crawler->filter('.card'));

        // Extract all home titles from the page
        $homeTitles = $crawler->filter('.card-title')->each(static function ($node) {
            return $node->text();
        });

        // Verify each home is displayed
        static::assertContains('Main House', $homeTitles);
        static::assertContains('Guest House', $homeTitles);
        static::assertContains('Garden Shed', $homeTitles);

        // Verify links exist
        static::assertSelectorExists('a[href="/main-house"]');
        static::assertSelectorExists('a[href="/guest-house"]');
        static::assertSelectorExists('a[href="/garden-shed"]');
    }

    /**
     * Test that node counts are displayed for each home.
     */
    public function testIndexPageDisplaysNodeCounts(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/');

        static::assertResponseIsSuccessful();

        $pageContent = $crawler->filter('body')->text();

        // Main House has 3 nodes (Living Room, Bedroom, Kitchen)
        static::assertStringContainsString('3 Nodes', $pageContent);

        // Guest House has 2 nodes (Guest Room, Guest Bathroom)
        static::assertStringContainsString('2 Nodes', $pageContent);

        // Garden Shed has 1 node (Shed)
        static::assertStringContainsString('1 Node', $pageContent);
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
