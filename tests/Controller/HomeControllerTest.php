<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\Fixtures\HomeFixtures;
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
        $homeTitles = $crawler->filter('.card-title')->each(function ($node) {
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
     * Load fixtures for testing.
     */
    private function loadFixtures($client): void
    {
        $container = $client->getContainer();
        $doctrine = $container->get('doctrine');
        $entityManager = $doctrine->getManager();

        $loader = new Loader();
        $loader->addFixture(new HomeFixtures());

        $purger = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
