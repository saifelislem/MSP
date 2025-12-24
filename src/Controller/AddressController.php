<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Customer;
use App\Service\AddressService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/address')]
class AddressController extends AbstractController
{
    public function __construct(
        private AddressService $addressService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/customer/{id}', name: 'app_address_list', methods: ['GET'])]
    public function list(Customer $customer): JsonResponse
    {
        $addresses = $customer->getAddresses();
        
        $addressesData = [];
        foreach ($addresses as $address) {
            $addressesData[] = [
                'id' => $address->getId(),
                'name' => $address->getName(),
                'street' => $address->getStreet(),
                'postalCode' => $address->getPostalCode(),
                'city' => $address->getCity(),
                'country' => $address->getCountry(),
                'company' => $address->getCompany(),
                'additionalInfo' => $address->getAdditionalInfo(),
                'isDefault' => $address->isDefault(),
                'fullAddress' => $address->getFullAddress()
            ];
        }

        return $this->json([
            'success' => true,
            'addresses' => $addressesData
        ]);
    }

    #[Route('/create', name: 'app_address_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides'
                ], 400);
            }

            // Validation des données
            $errors = $this->addressService->validateAddressData($data);
            if (!empty($errors)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $errors
                ], 400);
            }

            // Récupérer le client
            $customer = null;
            if (isset($data['customerId'])) {
                $customer = $this->entityManager->getRepository(Customer::class)->find($data['customerId']);
            }

            if (!$customer) {
                return $this->json([
                    'success' => false,
                    'message' => 'Client non trouvé'
                ], 404);
            }

            $address = $this->addressService->createAddress($customer, $data);

            return $this->json([
                'success' => true,
                'message' => 'Adresse créée avec succès',
                'address' => [
                    'id' => $address->getId(),
                    'name' => $address->getName(),
                    'fullAddress' => $address->getFullAddress(),
                    'isDefault' => $address->isDefault()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/update/{id}', name: 'app_address_update', methods: ['PUT'])]
    public function update(Address $address, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides'
                ], 400);
            }

            // Validation des données
            $errors = $this->addressService->validateAddressData($data);
            if (!empty($errors)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $errors
                ], 400);
            }

            $address = $this->addressService->updateAddress($address, $data);

            return $this->json([
                'success' => true,
                'message' => 'Adresse mise à jour avec succès',
                'address' => [
                    'id' => $address->getId(),
                    'name' => $address->getName(),
                    'fullAddress' => $address->getFullAddress(),
                    'isDefault' => $address->isDefault()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/set-default/{id}', name: 'app_address_set_default', methods: ['POST'])]
    public function setDefault(Address $address): JsonResponse
    {
        try {
            $this->addressService->setAsDefault($address);

            return $this->json([
                'success' => true,
                'message' => 'Adresse définie comme par défaut'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/delete/{id}', name: 'app_address_delete', methods: ['DELETE'])]
    public function delete(Address $address): JsonResponse
    {
        try {
            $this->addressService->deleteAddress($address);

            return $this->json([
                'success' => true,
                'message' => 'Adresse supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/validate', name: 'app_address_validate', methods: ['POST'])]
    public function validate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides'
            ], 400);
        }

        $errors = $this->addressService->validateAddressData($data);

        return $this->json([
            'success' => empty($errors),
            'errors' => $errors
        ]);
    }
}