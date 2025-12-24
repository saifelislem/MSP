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
        Request $request,
        \Doctrine\ORM\EntityManagerInterface $em
    ): Response {
        error_log('=== PAYMENT CREATE CHECKOUT CALLED ===');
        
        // Récupérer l'ID de commande depuis les paramètres
        $orderId = $request->query->get('orderId');
        
        if ($orderId) {
            // Mode: Paiement d'une commande existante
            $order = $em->getRepository(\App\Entity\Order::class)->find($orderId);
            
            if (!$order) {
                $this->addFlash('error', 'Commande non trouvée');
                return $this->redirectToRoute('app_cart');
            }
            
            // Préparer les items depuis la commande
            $items = [];
            foreach ($order->getOrderItems() as $orderItem) {
                $product = $orderItem->getProduct();
                
                $productName = $product->getText() ?: ($product->getModeleName() ?: 'Enseigne personnalisée');
                $description = sprintf('%dx%dcm', $product->getLargeur() ?: 0, $product->getHauteur() ?: 0);
                
                if ($product->getTypeEcriture()) {
                    $description .= ' - ' . $product->getTypeEcriture();
                }
                
                $items[] = [
                    'name' => $productName,
                    'description' => $description,
                    'price' => $orderItem->getPrice(),
                    'quantity' => $orderItem->getQuantity(),
                ];
            }
            
            $metadata = [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
            ];
        } else {
            // Mode: Paiement depuis le panier (ancien système)
            $cart = $cartService->getCurrentCart();
            error_log('Cart items count: ' . $cart->getCartItems()->count());
            
            if ($cart->getCartItems()->count() === 0) {
                error_log('Cart is empty, redirecting to cart');
                $this->addFlash('error', 'Votre panier est vide');
                return $this->redirectToRoute('app_cart');
            }
            
            // Préparer les items depuis le panier
            $items = [];
            foreach ($cart->getCartItems() as $cartItem) {
                $product = $cartItem->getProduct();
                
                $productName = $product->getText() ?: ($product->getModeleName() ?: 'Enseigne personnalisée');
                $description = sprintf('%dx%dcm', $product->getLargeur() ?: 0, $product->getHauteur() ?: 0);
                
                if ($product->getTypeEcriture()) {
                    $description .= ' - ' . $product->getTypeEcriture();
                }
                
                $items[] = [
                    'name' => $productName,
                    'description' => $description,
                    'price' => $cartItem->getPrice(),
                    'quantity' => $cartItem->getQuantity(),
                ];
            }
            
            $metadata = [
                'cart_id' => $cart->getId(),
                'session_id' => $request->getSession()->getId(),
            ];
        }



        try {
            error_log('Creating Stripe session with ' . count($items) . ' items');
            
            // Créer la session Stripe
            $session = $stripeService->createCheckoutSession(
                $items,
                $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $metadata
            );

            error_log('Stripe session created: ' . $session->id);
            error_log('Redirecting to: ' . $session->url);
            
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
        OrderService $orderService,
        \Doctrine\ORM\EntityManagerInterface $em,
        \App\Service\EmailService $emailService
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
                // Vérifier si c'est une commande existante
                if (isset($session->metadata['order_id'])) {
                    // Paiement d'une commande existante
                    $order = $em->getRepository(\App\Entity\Order::class)->find($session->metadata['order_id']);
                    
                    if ($order) {
                        // Marquer la commande comme payée
                        $order->setStatus('paid');
                        $em->flush();
                        
                        // Envoyer l'email de facture au client
                        try {
                            $emailService->sendInvoiceEmail($order);
                            error_log('Invoice email sent for order: ' . $order->getOrderNumber());
                        } catch (\Exception $e) {
                            error_log('Failed to send invoice email: ' . $e->getMessage());
                        }
                        
                        return $this->redirectToRoute('app_cart_order_success', ['id' => $order->getId()]);
                    }
                } else {
                    // Ancien système avec panier
                    $cart = $cartService->getCurrentCart();
                    
                    if ($cart->getCartItems()->count() > 0) {
                        // Récupérer le customer de la session
                        $customerId = $request->getSession()->get('customer_id');
                        $customer = null;
                        
                        if ($customerId) {
                            $customer = $em->getRepository(\App\Entity\Customer::class)->find($customerId);
                        }
                        
                        // Créer la commande (ancien système)
                        $customerData = [
                            'name' => $customer ? $customer->getFullName() : ($session->customer_details->name ?? 'Client'),
                            'email' => $customer ? $customer->getEmail() : ($session->customer_details->email ?? ''),
                            'phone' => $customer ? $customer->getTelephone() : '',
                            'notes' => 'Paiement Stripe - Session: ' . $sessionId,
                        ];
                        
                        $order = $orderService->createOrderFromCart($cart, $customerData);
                        $order->setStatus('paid');
                        
                        // Lier le customer à la commande
                        if ($customer) {
                            $order->setCustomer($customer);
                        }
                        $em->flush();
                        
                        $orderService->clearCartAfterOrder($cart);
                        
                        // Envoyer l'email de facture au client
                        try {
                            $emailService->sendInvoiceEmail($order);
                            error_log('Invoice email sent for order: ' . $order->getOrderNumber());
                        } catch (\Exception $e) {
                            error_log('Failed to send invoice email: ' . $e->getMessage());
                        }
                        
                        // Nettoyer la session
                        $request->getSession()->remove('customer_id');
                        
                        return $this->redirectToRoute('app_cart_order_success', ['id' => $order->getId()]);
                    }
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
