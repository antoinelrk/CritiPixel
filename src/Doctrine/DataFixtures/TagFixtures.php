<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TagFixtures extends Fixture
{
    /**
     * Load tag fixtures into the database.
     *
     * @param ObjectManager $manager The object manager to persist entities.
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $tag = new Tag();
            $tag->setName(sprintf('Tag %d', $i));
            $manager->persist($tag);
        }

        $manager->flush();
    }
}
