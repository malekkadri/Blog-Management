<?php

namespace App\Repository;

use App\Entity\PostTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostTranslation>
 *
 * @method PostTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostTranslation[]    findAll()
 * @method PostTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostTranslation::class);
    }

    public function add(PostTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PostTranslation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
