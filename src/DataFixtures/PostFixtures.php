<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Repository\TagRepository;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PostFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private $enFaker;
    private $hrFaker;
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->enFaker = Factory::create();
        $this->hrFaker = Factory::create('hr_HR');

        $this->tagRepository = $tagRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $this->tagRepository->findAll();

        for ($i = 0; $i < 20; $i++) {
            $data = $this->getPostData();

            $post = new Post();
            $post->setImage($data['image']);
            $post->setPublishedAt($data['publishedAt']);
            $post->setAuthor($this->getReference(UserFixtures::ADMIN_REFERENCE));

            $numberOfTags = random_int(1, 4);

            for ($j = 0; $j < $numberOfTags; $j++) {
                $tag = $tags[array_rand($tags)];
                $post->addTag($tag);
            }

            foreach ($data['translations'] as $locale => $value) {
                $postTranslation = new PostTranslation();
                $postTranslation->setLocale($locale);
                $postTranslation->setTitle($value['title']);
                $postTranslation->setContent($value['content']);

                $post->addTranslation($postTranslation);
            }

            $manager->persist($post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            TagFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['default'];
    }

    private function getPostData(): array
    {
        $image = file_get_contents('https://picsum.photos/1280/768');

        $imagePath = 'public/uploads/post-images/' . uniqid() . '.jpg';

        file_put_contents($imagePath, $image);

        return [
            'image' => str_replace('public/uploads/', '', $imagePath),
            'publishedAt' => DateTimeImmutable::createFromMutable($this->enFaker->dateTimeBetween('-1 year', '-1 day')),
            'translations' => [
                'en' => [
                    'title' => $this->enFaker->unique()->sentence(6),
                    'content' => $this->enFaker->realText(3000),
                ],
                'hr' => [
                    'title' => $this->hrFaker->unique()->sentence(6),
                    'content' => $this->hrFaker->realText(3000),
                ],
            ],
        ];
    }
}
