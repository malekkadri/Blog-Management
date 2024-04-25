<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\TagTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $data = $this->getTagsData();

        foreach ($data as $item) {
            $tag = new Tag();
            $tag->setUser($this->getReference(UserFixtures::ADMIN_REFERENCE));

            foreach ($item as $locale => $name) {
                $tagTranslation = new TagTranslation();
                $tagTranslation->setLocale($locale);
                $tagTranslation->setName($name);

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
        return ['default'];
    }

    private function getTagsData(): array
    {
        return [
            [
                'en' => 'Technology',
                'hr' => 'Tehnologija',
            ],
            [
                'en' => 'Science',
                'hr' => 'Znanost',
            ],
            [
                'en' => 'Programming',
                'hr' => 'Programiranje',
            ],
            [
                'en' => 'Web Development',
                'hr' => 'Web Razvoj',
            ],
            [
                'en' => 'Mobile Development',
                'hr' => 'Razvoj Mobilnih Aplikacija',
            ],
            [
                'en' => 'Machine Learning',
                'hr' => 'Strojno Učenje',
            ],
            [
                'en' => 'Artificial Intelligence',
                'hr' => 'Umjetna Inteligencija',
            ],
            [
                'en' => 'Data Science',
                'hr' => 'Znanost o Podacima',
            ],
            [
                'en' => 'Big Data',
                'hr' => 'Veliki Podaci',
            ],
            [
                'en' => 'Blockchain',
                'hr' => 'Blockchain',
            ],
        ];
    }
}
