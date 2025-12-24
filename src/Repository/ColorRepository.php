<?php

namespace App\Repository;

use App\Entity\Color;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Color>
 */
class ColorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Color::class);
    }

    /**
     * Récupérer les couleurs disponibles pour la façade
     */
    public function findAvailableForFacade(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->andWhere('c.stock > 0')
            ->andWhere('c.type IN (:types)')
            ->setParameter('active', true)
            ->setParameter('types', ['facade', 'both'])
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les couleurs disponibles pour les côtés
     */
    public function findAvailableForSide(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->andWhere('c.stock > 0')
            ->andWhere('c.type IN (:types)')
            ->setParameter('active', true)
            ->setParameter('types', ['side', 'both'])
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer les couleurs avec stock faible
     */
    public function findLowStock(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->andWhere('c.stock <= c.minStock')
            ->andWhere('c.stock > 0')
            ->setParameter('active', true)
            ->orderBy('c.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupérer toutes les couleurs pour l'admin
     */
    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.isActive', 'DESC')
            ->addOrderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver une couleur par son code hex
     */
    public function findByHexCode(string $hexCode): ?Color
    {
        return $this->createQueryBuilder('c')
            ->where('c.hexCode = :hexCode')
            ->setParameter('hexCode', $hexCode)
            ->getQuery()
            ->getOneOrNullResult();
    }
}