<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Api\SensorData\Entity\SensorData;
use App\Node\Entity\Node;
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

            // Fixed base values per node
            $baseTemp = 18.0 + ($nodeIndex * 2.0);
            $temperature = $baseTemp + ($day % 5) * 0.5;

            $baseHumidity = 45.0 + ($nodeIndex * 5.0);
            $humidity = $baseHumidity + ($day % 7) * 1.0;

            $basePressure = 1013.0 + ($nodeIndex * 2.0);
            $pressure = $basePressure + ($day % 10) * 0.5;

            // Only some nodes have CO2 sensors (even indices)
            $carbonDioxide = ($nodeIndex % 2 === 0)
                ? 400.0 + ($day % 15) * 10.0
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

            // Fixed values based on hour and node index
            $temperature = 20.0 + ($hour % 12) * 0.5;
            $humidity = 50.0 + ($hour % 10) * 1.5;
            $pressure = 1013.0 + ($hour % 8) * 0.75;

            // Only some nodes have CO2 sensors (even indices)
            $carbonDioxide = ($nodeIndex % 2 === 0)
                ? 400.0 + ($hour % 20) * 15.0
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
