<?php

namespace App\Controller;

use App\Service\StripePaymentService;
use App\Service\CartService;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/create-checkout-session', name: 'payment_create_checkout')]
    public function createCheckoutSession(
        StripePaymentService $stripeService,
        CartService $cartService,
        Request $request
    ): Response {
        $cart = $cartService->getCurrentCart();
        
        if ($cart->getCartItems()->count() === 0) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        // Préparer les items pour Stripe
        $items = [];
        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $items[] = [
                'name' => $product->getText(),
                'description' => sprintf(
                    '%dx%dcm - %s%s',
                    $product->getLargeur(),
                    $product->getHauteur(),
                    $product->getTypeEcriture(),
                    $product->getModeleName() ? ' - ' . $product->getModeleName() : ''
                ),
                'price' => $cartItem->getPrice(),
                'quantity' => $cartItem->getQuantity(),
            ];
        }

        // Métadonnées pour retrouver le panier
        $metadata = [
            'cart_id' => $cart->getId(),
            'session_id' => $request->getSession()->getId(),
        ];

        try {
            // Créer la session Stripe
            $session = $stripeService->createCheckoutSession(
                $items,
                $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $metadata
            );

            // Rediriger vers Stripe Checkout
            return $this->redirect($session->url);
        } catch (\Exception $e) {
            // Log l'erreur complète
            error_log('STRIPE ERROR: ' . $e->getMessage());
            error_log('STRIPE TRACE: ' . $e->getTraceAsString());
            
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/success', name: 'payment_success')]
    public function success(
        Request $request,
        StripePaymentService $stripeService,
        CartService $cartService,
        OrderService $orderService
    ): Response {
        $sessionId = $request->query->get('session_id');
        
        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide');
            return $this->redirectToRoute('app_home');
        }

        try {
            // Récupérer la session Stripe
            $session = $stripeService->retrieveSession($sessionId);
            
            if ($session->payment_status === 'paid') {
                // Récupérer le panier
                $cart = $cartService->getCurrentCart();
                
                if ($cart->getCartItems()->count() > 0) {
                    // Créer la commande
                    $customerData = [
                        'name' => $session->customer_details->name ?? 'Client',
                        'email' => $session->customer_details->email ?? '',
                        'notes' => 'Paiement Stripe - Session: ' . $sessionId,
                    ];
                    
                    $order = $orderService->createOrderFromCart($cart, $customerData);
                    $orderService->clearCartAfterOrder($cart);
                    
                    return $this->render('payment/success.html.twig', [
                        'order' => $order,
                        'session' => $session,
                    ]);
                }
            }
            
            $this->addFlash('warning', 'Le paiement n\'a pas été complété');
            return $this->redirectToRoute('app_cart');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la vérification du paiement');
            return $this->redirectToRoute('app_cart');
        }
    }

    #[Route('/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé');
        return $this->redirectToRoute('app_cart');
    }
}
