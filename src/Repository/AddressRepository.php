<?php

namespace App\Repository;

use App\Entity\Address;
use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Address>
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /**
     * Trouve toutes les adresses d'un client
     */
    public function findByCustomer(Customer $customer): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('a.isDefault', 'DESC')
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve l'adresse par défaut d'un client
     */
    public function findDefaultByCustomer(Customer $customer): ?Address
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.customer = :customer')
            ->andWhere('a.isDefault = true')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Définit une adresse comme par défaut et retire le statut des autres
     */
    public function setAsDefault(Address $address): void
    {
        $customer = $address->getCustomer();
        if (!$customer) {
            return;
        }

        // Retirer le statut par défaut des autres adresses
        $this->createQueryBuilder('a')
            ->update()
            ->set('a.isDefault', 'false')
            ->where('a.customer = :customer')
            ->andWhere('a.id != :addressId')
            ->setParameter('customer', $customer)
            ->setParameter('addressId', $address->getId())
            ->getQuery()
            ->execute();

        // Définir cette adresse comme par défaut
        $address->setIsDefault(true);
        $this->getEntityManager()->flush();
    }
}