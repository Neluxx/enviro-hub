<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\EnvironmentalData;
use App\Repository\EnvironmentalDataRepository;
use App\Service\DashboardService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test class for DashboardController.
 */
class DashboardControllerTest extends WebTestCase
{
    /**
     * Test the index action renders the dashboard page.
     */
    public function testIndexRendersSuccessfully(): void
    {
        $client = static::createClient();

        $environmentalData = new EnvironmentalData(
            nodeUuid: 'test-node-uuid',
            temperature: 22.5,
            humidity: 65.0,
            pressure: 1013.25,
            carbonDioxide: 450.0,
            measuredAt: new DateTime(),
        );

        $repository = $this->createMock(EnvironmentalDataRepository::class);
        $repository->expects($this->once())
            ->method('getLastEntry')
            ->willReturn($environmentalData);

        static::getContainer()->set(EnvironmentalDataRepository::class, $repository);

        $client->request('GET', '/');

        static::assertResponseIsSuccessful();
        static::assertSelectorExists('.container-fluid');
        static::assertSelectorExists('.row');
        static::assertSelectorExists('.card');
        static::assertSelectorExists('#temperatureChart');
        static::assertSelectorExists('#humidityChart');
        static::assertSelectorExists('#co2Chart');
    }

    /**
     * Test the index action displays correct environmental data values.
     */
    public function testIndexDisplaysCorrectValues(): void
    {
        $client = static::createClient();

        $environmentalData = new EnvironmentalData(
            nodeUuid: 'test-node-uuid',
            temperature: 23.7,
            humidity: 58.2,
            pressure: 1015.5,
            carbonDioxide: 425.3,
            measuredAt: new DateTime('2024-01-15 10:30:00'),
        );

        $repository = $this->createMock(EnvironmentalDataRepository::class);
        $repository->method('getLastEntry')->willReturn($environmentalData);

        static::getContainer()->set(EnvironmentalDataRepository::class, $repository);

        $crawler = $client->request('GET', '/');

        static::assertResponseIsSuccessful();

        $allCardText = '';
        $crawler->filter('.card-body')->each(function ($node) use (&$allCardText) {
            $allCardText .= ' '.$node->text();
        });

        static::assertStringContainsString('Temperature 23.7 °C', $allCardText);
        static::assertStringContainsString('Humidity 58.2 %', $allCardText);
        static::assertStringContainsString('CO₂ 425.3 ppm', $allCardText);
    }

    /**
     * Test the index action handles null CO2 values.
     */
    public function testIndexHandlesNullCo2Value(): void
    {
        $client = static::createClient();

        $environmentalData = new EnvironmentalData(
            nodeUuid: 'test-node-uuid',
            temperature: 21.0,
            humidity: 60.0,
            pressure: 1012.0,
            carbonDioxide: null,
            measuredAt: new DateTime(),
        );

        $repository = $this->createMock(EnvironmentalDataRepository::class);
        $repository->method('getLastEntry')->willReturn($environmentalData);

        static::getContainer()->set(EnvironmentalDataRepository::class, $repository);

        $crawler = $client->request('GET', '/');

        static::assertResponseIsSuccessful();

        $allCardText = '';
        $crawler->filter('.card-body')->each(function ($node) use (&$allCardText) {
            $allCardText .= ' '.$node->text();
        });

        static::assertStringContainsString('CO₂ N/A ppm', $allCardText);
    }

    /**
     * Test chart data API endpoint returns valid JSON for today.
     */
    public function testGetChartDataTodayReturnsValidJson(): void
    {
        $client = static::createClient();

        $chartData = [
            'labels' => ['10:00', '11:00', '12:00'],
            'temperature' => [22.5, 23.0, 23.5],
            'humidity' => [65.0, 64.5, 64.0],
            'co2' => [450.0, 455.0, 460.0],
        ];

        $service = $this->createMock(DashboardService::class);
        $service->expects($this->once())
            ->method('getChartData')
            ->with('today')
            ->willReturn($chartData);

        static::getContainer()->set(DashboardService::class, $service);

        $client->request('GET', '/api/environmental-data/chart/today');

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        static::assertIsArray($responseData);
        static::assertArrayHasKey('labels', $responseData);
        static::assertArrayHasKey('temperature', $responseData);
        static::assertArrayHasKey('humidity', $responseData);
        static::assertArrayHasKey('co2', $responseData);
        static::assertCount(3, $responseData['labels']);
        static::assertEquals([22.5, 23.0, 23.5], $responseData['temperature']);
    }

    /**
     * Test chart data API endpoint for week range.
     */
    public function testGetChartDataWeekReturnsData(): void
    {
        $client = static::createClient();

        $chartData = [
            'labels' => ['Mon 10:00', 'Tue 10:00', 'Wed 10:00'],
            'temperature' => [20.0, 21.5, 22.0],
            'humidity' => [70.0, 68.0, 66.0],
            'co2' => [400.0, 420.0, 440.0],
        ];

        $service = $this->createMock(DashboardService::class);
        $service->expects($this->once())
            ->method('getChartData')
            ->with('week')
            ->willReturn($chartData);

        static::getContainer()->set(DashboardService::class, $service);

        $client->request('GET', '/api/environmental-data/chart/week');

        static::assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        static::assertCount(3, $responseData['labels']);
        static::assertEquals([20.0, 21.5, 22.0], $responseData['temperature']);
    }

    /**
     * Test chart data API endpoint for month range.
     */
    public function testGetChartDataMonthReturnsData(): void
    {
        $client = static::createClient();

        $chartData = [
            'labels' => ['Jan 01', 'Jan 15', 'Jan 30'],
            'temperature' => [18.0, 20.0, 22.0],
            'humidity' => [75.0, 70.0, 65.0],
            'co2' => [380.0, 400.0, 420.0],
        ];

        $service = $this->createMock(DashboardService::class);
        $service->expects($this->once())
            ->method('getChartData')
            ->with('month')
            ->willReturn($chartData);

        static::getContainer()->set(DashboardService::class, $service);

        $client->request('GET', '/api/environmental-data/chart/month');

        static::assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        static::assertCount(3, $responseData['labels']);
    }

    /**
     * Test chart data API endpoint for year range.
     */
    public function testGetChartDataYearReturnsData(): void
    {
        $client = static::createClient();

        $chartData = [
            'labels' => ['Jan 2024', 'Jun 2024', 'Dec 2024'],
            'temperature' => [15.0, 25.0, 18.0],
            'humidity' => [80.0, 60.0, 75.0],
            'co2' => [350.0, 450.0, 380.0],
        ];

        $service = $this->createMock(DashboardService::class);
        $service->expects($this->once())
            ->method('getChartData')
            ->with('year')
            ->willReturn($chartData);

        static::getContainer()->set(DashboardService::class, $service);

        $client->request('GET', '/api/environmental-data/chart/year');

        static::assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        static::assertCount(3, $responseData['labels']);
    }

    /**
     * Test chart data API endpoint returns empty arrays when no data.
     */
    public function testGetChartDataReturnsEmptyArraysWhenNoData(): void
    {
        $client = static::createClient();

        $emptyChartData = [
            'labels' => [],
            'temperature' => [],
            'humidity' => [],
            'co2' => [],
        ];

        $service = $this->createMock(DashboardService::class);
        $service->expects($this->once())
            ->method('getChartData')
            ->with('today')
            ->willReturn($emptyChartData);

        static::getContainer()->set(DashboardService::class, $service);

        $client->request('GET', '/api/environmental-data/chart/today');

        static::assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        static::assertEmpty($responseData['labels']);
        static::assertEmpty($responseData['temperature']);
        static::assertEmpty($responseData['humidity']);
        static::assertEmpty($responseData['co2']);
    }

    /**
     * Test chart data API only accepts GET requests.
     */
    public function testGetChartDataOnlyAcceptsGetMethod(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/environmental-data/chart/today');
        static::assertResponseStatusCodeSame(405);

        $client->request('PUT', '/api/environmental-data/chart/today');
        static::assertResponseStatusCodeSame(405);

        $client->request('DELETE', '/api/environmental-data/chart/today');
        static::assertResponseStatusCodeSame(405);
    }
}
