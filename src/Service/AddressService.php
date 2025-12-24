<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Customer;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;

class AddressService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AddressRepository $addressRepository
    ) {}

    /**
     * Crée une nouvelle adresse pour un client
     */
    public function createAddress(Customer $customer, array $addressData): Address
    {
        $address = new Address();
        $address->setCustomer($customer);
        $address->setName($addressData['name']);
        $address->setStreet($addressData['street']);
        $address->setPostalCode($addressData['postalCode']);
        $address->setCity($addressData['city']);
        $address->setCountry($addressData['country'] ?? 'France');
        
        if (isset($addressData['company'])) {
            $address->setCompany($addressData['company']);
        }
        
        if (isset($addressData['additionalInfo'])) {
            $address->setAdditionalInfo($addressData['additionalInfo']);
        }

        // Si c'est la première adresse du client, la définir comme par défaut
        if ($customer->getAddresses()->count() === 0) {
            $address->setIsDefault(true);
        }

        $this->entityManager->persist($address);
        $this->entityManager->flush();

        return $address;
    }

    /**
     * Met à jour une adresse existante
     */
    public function updateAddress(Address $address, array $addressData): Address
    {
        $address->setName($addressData['name']);
        $address->setStreet($addressData['street']);
        $address->setPostalCode($addressData['postalCode']);
        $address->setCity($addressData['city']);
        $address->setCountry($addressData['country'] ?? 'France');
        
        if (isset($addressData['company'])) {
            $address->setCompany($addressData['company']);
        }
        
        if (isset($addressData['additionalInfo'])) {
            $address->setAdditionalInfo($addressData['additionalInfo']);
        }

        $this->entityManager->flush();

        return $address;
    }

    /**
     * Définit une adresse comme par défaut
     */
    public function setAsDefault(Address $address): void
    {
        $this->addressRepository->setAsDefault($address);
    }

    /**
     * Supprime une adresse
     */
    public function deleteAddress(Address $address): void
    {
        $customer = $address->getCustomer();
        $wasDefault = $address->isDefault();

        $this->entityManager->remove($address);
        $this->entityManager->flush();

        // Si l'adresse supprimée était par défaut, définir une autre comme par défaut
        if ($wasDefault && $customer && $customer->getAddresses()->count() > 0) {
            $firstAddress = $customer->getAddresses()->first();
            if ($firstAddress) {
                $this->setAsDefault($firstAddress);
            }
        }
    }

    /**
     * Crée une adresse temporaire à partir des données de commande
     */
    public function createTemporaryAddress(array $addressData): Address
    {
        $address = new Address();
        $address->setName($addressData['name']);
        $address->setStreet($addressData['street']);
        $address->setPostalCode($addressData['postalCode']);
        $address->setCity($addressData['city']);
        $address->setCountry($addressData['country'] ?? 'France');
        
        if (isset($addressData['company'])) {
            $address->setCompany($addressData['company']);
        }
        
        if (isset($addressData['additionalInfo'])) {
            $address->setAdditionalInfo($addressData['additionalInfo']);
        }

        $this->entityManager->persist($address);
        $this->entityManager->flush();

        return $address;
    }

    /**
     * Valide les données d'adresse
     */
    public function validateAddressData(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'] = 'Le nom est obligatoire';
        }

        if (empty($data['street'])) {
            $errors['street'] = 'L\'adresse est obligatoire';
        }

        if (empty($data['postalCode'])) {
            $errors['postalCode'] = 'Le code postal est obligatoire';
        } elseif (!preg_match('/^\d{5}$/', $data['postalCode'])) {
            $errors['postalCode'] = 'Le code postal doit contenir 5 chiffres';
        }

        if (empty($data['city'])) {
            $errors['city'] = 'La ville est obligatoire';
        }

        return $errors;
    }
}