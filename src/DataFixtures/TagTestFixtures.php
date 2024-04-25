<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TagTestFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $data = $this->getTagData();

            $tag = new Tag();
            $tag->setUser($this->getReference(UserFixtures::ADMIN_REFERENCE));

            foreach ($data['translations'] as $locale => $value) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->setLocale($locale);
                $tagTranslation->setName($value['name']);

                $tag->addTranslation($tagTranslation);
            }

            $manager->persist($tag);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    private function getTagData(): array
    {
        return [
            'translations' => [
                'en' => [
                    'name' => $this->faker->unique()->word(),
                ],
                'hr' => [
                    'name' => $this->faker->unique()->word(),
                ],
            ],
        ];
    }
}
