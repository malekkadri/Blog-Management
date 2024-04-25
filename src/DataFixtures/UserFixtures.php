<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const ADMIN_REFERENCE = 'admin';
    public const USER_REFERENCE = 'user';
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createUser($manager, 'Admin', 'admin@test.com', 'admin12345', self::ADMIN_REFERENCE, [User::ROLE_ADMIN]);
        $this->createUser($manager, 'Test User', 'test@test.com', 'test12345', self::USER_REFERENCE, [User::ROLE_USER]);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['test', 'default'];
    }

    private function createUser(
        ObjectManager $manager,
        string        $name,
        string        $email,
        string        $password,
        string        $reference,
        array         $roles = []
    )
    {
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password)
        );

        if (empty($roles)) {
            $roles = [User::ROLE_USER];
        }

        $user->setRoles($roles);

        $manager->persist($user);

        $this->addReference($reference, $user);
    }
}
