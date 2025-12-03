<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\CartService;
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
        // Vérifier si le panier a des articles
        $cart = $cartService->getCurrentCart();
        $hasItems = $cart->getCartItems()->count() > 0;
        
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
        
        // Stocker l'ID du client en session
        $request->getSession()->set('customer_id', $customer->getId());
        
        // Rediriger vers le paiement si le panier a des articles, sinon vers le profil
        if ($hasItems) {
            return $this->redirectToRoute('payment_create_checkout');
        } else {
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

    #[Route('/logout', name: 'customer_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('customer_id');
        $this->addFlash('success', 'Vous avez été déconnecté.');
        return $this->redirectToRoute('app_home');
    }
}
