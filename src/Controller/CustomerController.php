<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Address;
use App\Repository\CustomerRepository;
use App\Service\CartService;
use App\Service\AddressService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/customer')]
class CustomerController extends AbstractController
{
    #[Route('/identify', name: 'customer_identify', methods: ['GET'])]
    public function identify(CartService $cartService): Response
    {
        $cart = $cartService->getCurrentCart();

        return $this->render('customer/identify.html.twig', [
            'cartItemsCount' => $cart->getCartItems()->count()
        ]);
    }

    #[Route('/identify/process', name: 'customer_identify_process', methods: ['POST'])]
    public function identifyProcess(
        Request $request,
        CustomerRepository $customerRepository,
        EntityManagerInterface $em,
        CartService $cartService
    ): Response {
        error_log('=== CUSTOMER IDENTIFY PROCESS CALLED ===');
        
        // Vérifier si le panier a des articles
        $cart = $cartService->getCurrentCart();
        $hasItems = $cart->getCartItems()->count() > 0;
        error_log('Cart has items: ' . ($hasItems ? 'YES' : 'NO') . ' (' . $cart->getCartItems()->count() . ')');
        
        $email = $request->request->get('email');
        
        // Chercher si le client existe déjà
        $customer = $customerRepository->findByEmail($email);
        
        if (!$customer) {
            // Créer un nouveau client
            $customer = new Customer();
            $customer->setEmail($email);
        }
        
        // Mettre à jour les informations
        $customer->setPrenom($request->request->get('prenom'));
        $customer->setNom($request->request->get('nom'));
        $customer->setTelephone($request->request->get('telephone'));
        $customer->setAdresse($request->request->get('adresse'));
        $customer->setVille($request->request->get('ville'));
        $customer->setCodePostal($request->request->get('code_postal'));
        
        $em->persist($customer);
        $em->flush();
        
        error_log('Customer saved with ID: ' . $customer->getId());
        
        // Stocker l'ID du client en session
        $request->getSession()->set('customer_id', $customer->getId());
        
        // Rediriger vers le paiement si le panier a des articles, sinon vers le profil
        if ($hasItems) {
            error_log('Redirecting to payment_create_checkout');
            return $this->redirectToRoute('payment_create_checkout');
        } else {
            error_log('No items in cart, redirecting to profile');
            $this->addFlash('success', 'Votre profil a été créé avec succès!');
            return $this->redirectToRoute('customer_profile');
        }
    }

    #[Route('/profile', name: 'customer_profile', methods: ['GET'])]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $customerId = $request->getSession()->get('customer_id');
        
        if (!$customerId) {
            // Pas de client en session, rediriger vers identification
            $this->addFlash('info', 'Veuillez vous identifier pour accéder à votre profil.');
            return $this->redirectToRoute('customer_identify');
        }
        
        $customer = $em->getRepository(Customer::class)->find($customerId);
        
        if (!$customer) {
            // Client introuvable, nettoyer la session
            $request->getSession()->remove('customer_id');
            return $this->redirectToRoute('customer_identify');
        }
        
        return $this->render('customer/profile.html.twig', [
            'customer' => $customer,
            'cartItemsCount' => 0 // Pour le header
        ]);
    }

    #[Route('/addresses', name: 'customer_addresses', methods: ['GET'])]
    public function addresses(Request $request, EntityManagerInterface $em): Response
    {
        $customerId = $request->getSession()->get('customer_id');
        
        if (!$customerId) {
            return $this->json(['success' => false, 'message' => 'Client non identifié'], 401);
        }
        
        $customer = $em->getRepository(Customer::class)->find($customerId);
        
        if (!$customer) {
            return $this->json(['success' => false, 'message' => 'Client non trouvé'], 404);
        }

        $addresses = [];
        foreach ($customer->getAddresses() as $address) {
            $addresses[] = [
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
            'addresses' => $addresses
        ]);
    }

    #[Route('/address/create', name: 'customer_address_create', methods: ['POST'])]
    public function createAddress(Request $request, EntityManagerInterface $em, AddressService $addressService): Response
    {
        $customerId = $request->getSession()->get('customer_id');
        
        if (!$customerId) {
            return $this->json(['success' => false, 'message' => 'Client non identifié'], 401);
        }
        
        $customer = $em->getRepository(Customer::class)->find($customerId);
        
        if (!$customer) {
            return $this->json(['success' => false, 'message' => 'Client non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['success' => false, 'message' => 'Données invalides'], 400);
        }

        try {
            $address = $addressService->createAddress($customer, $data);

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

    #[Route('/logout', name: 'customer_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('customer_id');
        $this->addFlash('success', 'Vous avez été déconnecté.');
        return $this->redirectToRoute('app_home');
    }
}
