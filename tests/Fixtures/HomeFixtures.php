<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Home\Entity\Home;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class HomeFixtures extends Fixture
{
    public const HOME_MAIN = 'home-main';
    public const HOME_GUEST = 'home-guest';
    public const HOME_GARDEN = 'home-garden';

    public function load(ObjectManager $manager): void
    {
        $homeData = [
            ['title' => 'Main House', 'identifier' => 'main-house', 'reference' => self::HOME_MAIN],
            ['title' => 'Guest House', 'identifier' => 'guest-house', 'reference' => self::HOME_GUEST],
            ['title' => 'Garden Shed', 'identifier' => 'garden-shed', 'reference' => self::HOME_GARDEN],
        ];

        foreach ($homeData as $data) {
            $home = new Home($data['title'], $data['identifier']);
            $manager->persist($home);

            // Store reference for use in other fixtures
            $this->addReference($data['reference'], $home);
        }

        $manager->flush();
    }
}
