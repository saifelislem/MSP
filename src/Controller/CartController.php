<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('/', name: 'app_cart', methods: ['GET'])]
    public function index(): Response
    {
        $cart = $this->cartService->getCurrentCart();

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): JsonResponse
    {
        try {
            $quantity = $request->request->getInt('quantity', 1);
            $price = (float) $request->request->get('price', 10.0);

            // Validation
            if ($quantity <= 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'La quantité doit être supérieure à 0'
                ], 400);
            }

            if ($price < 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le prix ne peut pas être négatif'
                ], 400);
            }

            $cartItem = $this->cartService->addToCart($product, $quantity, $price);

            return $this->json([
                'success' => true,
                'message' => 'Produit ajouté au panier avec succès',
                'cartItemsCount' => $this->cartService->getCartItemsCount(),
                'cartTotal' => $this->cartService->getCartTotal(),
                'item' => [
                    'id' => $cartItem->getId(),
                    'productName' => $product->getText(),
                    'quantity' => $cartItem->getQuantity(),
                    'price' => $cartItem->getPrice(),
                    'subtotal' => $cartItem->getSubtotal()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['DELETE'])]
    public function remove(int $id): JsonResponse
    {
        $success = $this->cartService->removeFromCart($id);

        if ($success) {
            return $this->json([
                'success' => true,
                'message' => 'Produit supprimé du panier',
                'cartItemsCount' => $this->cartService->getCartItemsCount(),
                'cartTotal' => $this->cartService->getCartTotal()
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Produit non trouvé'
        ], 404);
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $quantity = $request->request->getInt('quantity');
        $success = $this->cartService->updateQuantity($id, $quantity);

        if ($success) {
            return $this->json([
                'success' => true,
                'message' => 'Quantité mise à jour',
                'cartItemsCount' => $this->cartService->getCartItemsCount(),
                'cartTotal' => $this->cartService->getCartTotal()
            ]);
        }

        return $this->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour'
        ], 400);
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['DELETE'])]
    public function clear(): JsonResponse
    {
        $this->cartService->clearCart();

        return $this->json([
            'success' => true,
            'message' => 'Panier vidé',
            'cartItemsCount' => 0,
            'cartTotal' => 0
        ]);
    }

    #[Route('/count', name: 'app_cart_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json([
            'count' => $this->cartService->getCartItemsCount(),
            'total' => $this->cartService->getCartTotal()
        ]);
    }

    #[Route('/checkout', name: 'app_cart_checkout', methods: ['GET'])]
    public function checkout(): Response
    {
        $cart = $this->cartService->getCurrentCart();

        if ($cart->getCartItems()->count() === 0) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('cart/checkout.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/create-order', name: 'app_cart_create_order', methods: ['POST'])]
    public function createOrder(Request $request, \App\Service\OrderService $orderService, LoggerInterface $logger): Response
    {
        $cart = $this->cartService->getCurrentCart();

        if ($cart->getCartItems()->count() === 0) {
            return $this->json([
                'success' => false,
                'message' => 'Votre panier est vide'
            ], 400);
        }

        $customerData = [
            'name' => $request->request->get('name'),
            'email' => $request->request->get('email'),
            'phone' => $request->request->get('phone'),
            'notes' => $request->request->get('notes'),
        ];

        try {
            $order = $orderService->createOrderFromCart($cart, $customerData);
            $orderService->clearCartAfterOrder($cart);

            $logger->info('Order created', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'orderNumber' => $order->getOrderNumber(),
                'orderId' => $order->getId()
            ]);
        } catch (\Exception $e) {
            $logger->error('Error creating order', ['exception' => $e]);

            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/order-success/{id}', name: 'app_cart_order_success', methods: ['GET'])]
    public function orderSuccess(\App\Entity\Order $order): Response
    {
        return $this->render('cart/order_success.html.twig', [
            'order' => $order,
        ]);
    }
}