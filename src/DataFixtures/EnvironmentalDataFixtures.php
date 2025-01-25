<?php

namespace App\DataFixtures;

use App\Entity\EnvironmentalData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EnvironmentalDataFixtures extends Fixture
{
    private const ENVIRONMENTAL_DATA_SAMPLES = [
        [
            'temperature' => 22.5,
            'humidity' => 55.3,
            'pressure' => 1012.5,
            'carbonDioxide' => 400.2,
            'measuredAt' => '2023-10-01 10:00:00',
        ],
        [
            'temperature' => 25.0,
            'humidity' => 60.1,
            'pressure' => 1009.8,
            'carbonDioxide' => 410.8,
            'measuredAt' => '2023-10-01 11:00:00',
        ],
        [
            'temperature' => 20.7,
            'humidity' => 50.0,
            'pressure' => 1015.3,
            'carbonDioxide' => 405.5,
            'measuredAt' => '2023-10-01 12:00:00',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ENVIRONMENTAL_DATA_SAMPLES as $dataSample) {
            $environmentalData = $this->createEnvironmentalData($dataSample);
            $manager->persist($environmentalData);
        }

        $manager->flush();
    }

    private function createEnvironmentalData(array $dataSample): EnvironmentalData
    {
        return new EnvironmentalData(
            $dataSample['temperature'],
            $dataSample['humidity'],
            $dataSample['pressure'],
            $dataSample['carbonDioxide'],
            new \DateTime($dataSample['measuredAt']),
            new \DateTime('now')
        );
    }
}
