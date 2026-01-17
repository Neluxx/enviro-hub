<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Node;
use App\Entity\SensorData;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SensorDataFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $nodes = [
            $this->getReference(NodeFixtures::NODE_LIVING_ROOM, Node::class),
            $this->getReference(NodeFixtures::NODE_BEDROOM, Node::class),
            $this->getReference(NodeFixtures::NODE_KITCHEN, Node::class),
            $this->getReference(NodeFixtures::NODE_GUEST_ROOM, Node::class),
            $this->getReference(NodeFixtures::NODE_GUEST_BATHROOM, Node::class),
            $this->getReference(NodeFixtures::NODE_SHED, Node::class),
        ];

        foreach ($nodes as $nodeIndex => $node) {
            /* @var Node $node */
            $this->createDailySensorData($manager, $node, $nodeIndex);
            $this->createHourlySensorData($manager, $node, $nodeIndex);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            NodeFixtures::class,
        ];
    }

    private function createDailySensorData(ObjectManager $manager, Node $node, int $nodeIndex): void
    {
        $now = new DateTime();

        // Create 30 days of historical data (one reading per day)
        for ($day = 30; $day >= 0; --$day) {
            $measuredAt = (clone $now)->modify("-{$day} days");

            // Create varied but realistic sensor data
            $baseTemp = 18 + ($nodeIndex * 2);
            $temperature = $baseTemp + sin($day / 5) * 3 + random_int(-10, 10) / 10;

            $baseHumidity = 45 + ($nodeIndex * 5);
            $humidity = $baseHumidity + cos($day / 7) * 10 + random_int(-50, 50) / 10;
            $humidity = max(20, min(80, $humidity));

            $basePressure = 1013 + ($nodeIndex * 2);
            $pressure = $basePressure + sin($day / 10) * 5 + random_int(-20, 20) / 10;

            // Only some nodes have CO2 sensors
            $carbonDioxide = ($nodeIndex % 2 === 0)
                ? 400 + random_int(0, 200) + sin($day / 3) * 50
                : null;

            $sensorData = new SensorData(
                $node->getUuid(),
                round($temperature, 2),
                round($humidity, 2),
                round($pressure, 2),
                $carbonDioxide ? round($carbonDioxide, 2) : null,
                $measuredAt
            );

            $manager->persist($sensorData);
        }
    }

    private function createHourlySensorData(ObjectManager $manager, Node $node, int $nodeIndex): void
    {
        $now = new DateTime();

        // Add hourly data for the last 24 hours
        for ($hour = 24; $hour >= 0; --$hour) {
            $measuredAt = (clone $now)->modify("-{$hour} hours");

            $temperature = 20 + random_int(-20, 30) / 10;
            $humidity = 50 + random_int(-100, 100) / 10;
            $pressure = 1013 + random_int(-30, 30) / 10;
            $carbonDioxide = ($nodeIndex % 2 === 0)
                ? 400 + random_int(0, 300)
                : null;

            $sensorData = new SensorData(
                $node->getUuid(),
                round($temperature, 2),
                round($humidity, 2),
                round($pressure, 2),
                $carbonDioxide ? round($carbonDioxide, 2) : null,
                $measuredAt
            );

            $manager->persist($sensorData);
        }
    }
}
