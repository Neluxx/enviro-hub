<?php

declare(strict_types=1);

namespace App\Tests\Dashboard\Controller;

use App\Tests\Fixtures\HomeFixtures;
use App\Tests\Fixtures\NodeFixtures;
use App\Tests\Fixtures\SensorDataFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Integration test class for DashboardController.
 */
class DashboardControllerTest extends WebTestCase
{
    /**
     * Test the dashboard index page displays sensor data and charts.
     */
    public function testIndexPageDisplaysSensorDataAndCharts(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        // Using the living room node UUID from NodeFixtures (nodeIndex = 0)
        $nodeUuid = '550e8400-e29b-41d4-a716-446655440001';
        $crawler = $client->request('GET', '/main-house/'.$nodeUuid);

        static::assertResponseIsSuccessful();
        static::assertResponseStatusCodeSame(200);

        // Verify sensor data cards are present
        static::assertSelectorExists('.card');

        // Verify the page contains temperature, humidity and CO2 card titles
        // Check that the combined text of all .card-title elements contains each metric
        $cardTitlesText = $crawler->filter('.card-title')->each(static fn ($node) => $node->text());
        $allTitlesText = implode(' ', $cardTitlesText);

        static::assertStringContainsString('Temperature', $allTitlesText);
        static::assertStringContainsString('Humidity', $allTitlesText);
        static::assertStringContainsString('CO₂', $allTitlesText);

        // Verify the most recent sensor values are displayed
        // For node index 0, hour 0: temp=20.0, humidity=50.0, co2=400.0
        static::assertStringContainsString('20.0 °C', $crawler->filter('body')->text());
        static::assertStringContainsString('50.0 %', $crawler->filter('body')->text());
        static::assertStringContainsString('400 ppm', $crawler->filter('body')->text());

        // Verify charts are rendered
        static::assertSelectorExists('canvas');
    }

    /**
     * Test the dashboard page with bedroom node that has CO2 data.
     */
    public function testIndexPageWithBedroomNode(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        // Bedroom node (nodeIndex = 1, no CO2 sensor)
        $nodeUuid = '550e8400-e29b-41d4-a716-446655440002';
        $crawler = $client->request('GET', '/main-house/'.$nodeUuid);

        static::assertResponseIsSuccessful();

        // For node index 1, hour 0: temp=20.0, humidity=50.0, co2=null
        static::assertStringContainsString('20.0 °C', $crawler->filter('body')->text());
        static::assertStringContainsString('50.0 %', $crawler->filter('body')->text());
        static::assertStringContainsString('N/A ppm', $crawler->filter('body')->text());
    }

    /**
     * Test the dashboard page with kitchen node that has CO2 data.
     */
    public function testIndexPageWithKitchenNode(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        // Kitchen node (nodeIndex = 2, has CO2 sensor)
        $nodeUuid = '550e8400-e29b-41d4-a716-446655440003';
        $crawler = $client->request('GET', '/main-house/'.$nodeUuid);

        static::assertResponseIsSuccessful();

        // For node index 2, hour 0: temp=20.0, humidity=50.0, co2=400.0
        static::assertStringContainsString('20.0 °C', $crawler->filter('body')->text());
        static::assertStringContainsString('50.0 %', $crawler->filter('body')->text());
        static::assertStringContainsString('400 ppm', $crawler->filter('body')->text());
    }

    /**
     * Test the dashboard page with different home identifiers.
     */
    public function testIndexPageWithDifferentHomeIdentifiers(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        // Test guest house node (nodeIndex = 3, no CO2)
        $nodeUuid = '550e8400-e29b-41d4-a716-446655440004';
        $client->request('GET', '/guest-house/'.$nodeUuid);
        static::assertResponseIsSuccessful();

        // Test garden shed node (nodeIndex = 5, no CO2)
        $nodeUuid = '550e8400-e29b-41d4-a716-446655440006';
        $client->request('GET', '/garden-shed/'.$nodeUuid);
        static::assertResponseIsSuccessful();
    }

    /**
     * Test that API routes don't match when homeIdentifier is 'api'.
     */
    public function testApiRouteDoesNotMatchWhenHomeIdentifierIsApi(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $nodeUuid = '550e8400-e29b-41d4-a716-446655440001';

        // This should NOT match the dashboard route due to the regex requirement
        $client->request('GET', '/api/'.$nodeUuid);

        // Should get a 404, not the dashboard page
        static::assertResponseStatusCodeSame(404);
    }

    /**
     * Test dashboard displays time range buttons.
     */
    public function testDashboardDisplaysTimeRangeButtons(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $nodeUuid = '550e8400-e29b-41d4-a716-446655440001';
        $crawler = $client->request('GET', '/main-house/'.$nodeUuid);

        static::assertResponseIsSuccessful();

        // Verify time range buttons container exists
        static::assertSelectorExists('.time-range-buttons');

        // Get all button texts
        $buttonTexts = $crawler->filter('.time-range-buttons button')->each(static function ($node) {
            return $node->text();
        });

        // Verify all expected buttons are present
        static::assertContains('-12h', $buttonTexts);
        static::assertContains('-24h', $buttonTexts);
        static::assertContains('-7d', $buttonTexts);
        static::assertContains('-14d', $buttonTexts);
    }

    /**
     * Test dashboard displays measured at timestamp and version.
     */
    public function testDashboardDisplaysTimestampAndVersion(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $nodeUuid = '550e8400-e29b-41d4-a716-446655440001';
        $crawler = $client->request('GET', '/main-house/'.$nodeUuid);

        static::assertResponseIsSuccessful();

        $pageContent = $crawler->filter('body')->text();

        // Check for timestamp pattern (Y-m-d H:i:s)
        static::assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $pageContent,
            'Page should display timestamp in Y-m-d H:i:s format'
        );

        // Check that footer elements exist (timestamp and version)
        $footerElements = $crawler->filter('small.text-light');
        static::assertGreaterThanOrEqual(2, $footerElements->count(), 'Should have timestamp and version in footer');
    }

    /**
     * Test dashboard displays chart sections with correct IDs.
     */
    public function testDashboardDisplaysChartSections(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $nodeUuid = '550e8400-e29b-41d4-a716-446655440001';
        $client->request('GET', '/main-house/'.$nodeUuid);

        static::assertResponseIsSuccessful();

        // Verify all three chart sections exist with proper IDs
        static::assertSelectorExists('#temperature-chart');
        static::assertSelectorExists('#humidity-chart');
        static::assertSelectorExists('#co2-chart');

        // Verify chart wrappers exist
        static::assertSelectorExists('.chart-wrapper');
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
        $loader->addFixture(new SensorDataFixture());

        $purger = new ORMPurger($entityManager);
        $executor = new ORMExecutor($entityManager, $purger);
        $executor->execute($loader->getFixtures());

        // Ensure changes are committed and entity manager is cleared
        $entityManager->clear();
    }
}
