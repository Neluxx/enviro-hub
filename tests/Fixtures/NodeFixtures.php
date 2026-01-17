<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Home;
use App\Entity\Node;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NodeFixtures extends Fixture implements DependentFixtureInterface
{
    public const NODE_LIVING_ROOM = 'node-living-room';
    public const NODE_BEDROOM = 'node-bedroom';
    public const NODE_KITCHEN = 'node-kitchen';
    public const NODE_GUEST_ROOM = 'node-guest-room';
    public const NODE_GUEST_BATHROOM = 'node-guest-bathroom';
    public const NODE_SHED = 'node-shed';

    public function load(ObjectManager $manager): void
    {
        /** @var Home $mainHome */
        $mainHome = $this->getReference(HomeFixtures::HOME_MAIN, Home::class);
        /** @var Home $guestHome */
        $guestHome = $this->getReference(HomeFixtures::HOME_GUEST, Home::class);
        /** @var Home $gardenHome */
        $gardenHome = $this->getReference(HomeFixtures::HOME_GARDEN, Home::class);

        $nodeData = [
            // Main House nodes
            [
                'uuid' => '550e8400-e29b-41d4-a716-446655440001',
                'title' => 'Living Room Sensor',
                'home' => $mainHome,
                'reference' => self::NODE_LIVING_ROOM,
            ],
            [
                'uuid' => '550e8400-e29b-41d4-a716-446655440002',
                'title' => 'Bedroom Sensor',
                'home' => $mainHome,
                'reference' => self::NODE_BEDROOM,
            ],
            [
                'uuid' => '550e8400-e29b-41d4-a716-446655440003',
                'title' => 'Kitchen Sensor',
                'home' => $mainHome,
                'reference' => self::NODE_KITCHEN,
            ],

            // Guest House nodes
            [
                'uuid' => '550e8400-e29b-41d4-a716-446655440004',
                'title' => 'Guest Room Sensor',
                'home' => $guestHome,
                'reference' => self::NODE_GUEST_ROOM,
            ],
            [
                'uuid' => '550e8400-e29b-41d4-a716-446655440005',
                'title' => 'Guest Bathroom Sensor',
                'home' => $guestHome,
                'reference' => self::NODE_GUEST_BATHROOM,
            ],

            // Garden Shed node
            [
                'uuid' => '550e8400-e29b-41d4-a716-446655440006',
                'title' => 'Shed Sensor',
                'home' => $gardenHome,
                'reference' => self::NODE_SHED,
            ],
        ];

        foreach ($nodeData as $data) {
            $node = new Node($data['uuid'], $data['title'], $data['home']->getId());
            $manager->persist($node);

            // Store reference for use in SensorDataFixtures
            $this->addReference($data['reference'], $node);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            HomeFixtures::class,
        ];
    }
}
