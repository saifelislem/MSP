<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
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
    private EntityManagerInterface $entityManager;

    public function __construct(CartService $cartService, EntityManagerInterface $entityManager)
    {
        $this->cartService = $cartService;
        $this->entityManager = $entityManager;
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

    #[Route('/add-custom', name: 'app_cart_add_custom', methods: ['POST'])]
    public function addCustom(Request $request, \App\Service\FormulaCalculatorService $formulaCalculator): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json([
                    'success' => false,
                    'message' => 'Données invalides'
                ], 400);
            }

            // Récupérer le modèle si un ID est fourni
            $modele = null;
            if (isset($data['modeleId'])) {
                $modele = $this->entityManager->getRepository(\App\Entity\Modele::class)->find($data['modeleId']);
            }

            // Calculer le prix selon la formule du modèle
            $price = 10.0; // Prix par défaut
            if ($modele) {
                $parameters = [
                    'largeur' => (float) ($data['largeur'] ?? 0),
                    'hauteur' => (float) ($data['hauteur'] ?? 0),
                    'quantite' => (int) ($data['quantity'] ?? 1)
                ];
                $price = $formulaCalculator->calculatePrice($modele, $parameters);
            }

            // Créer un nouveau produit personnalisé
            $product = new Product();
            $product->setLargeur((int) $data['largeur']);
            $product->setHauteur((int) $data['hauteur']);
            $product->setImageUrl($data['imageUrl'] ?? null);
            $product->setModeleName($data['modeleName'] ?? 'Produit personnalisé');
            $product->setMode($data['mode'] ?? 'text');
            
            // Sauvegarder les couleurs façade et côtés
            $product->setFacadeColor($data['facadeColor'] ?? '#2A2A2A');
            $product->setSideColor($data['sideColor'] ?? '#E8E8E8');

            if ($data['mode'] === 'logo' && isset($data['logoData'])) {
                // Mode logo
                $product->setLogoData($data['logoData']);
                $product->setLogoFileName('logo_' . uniqid() . '.png'); // Générer un nom unique
                $product->setLogoRatio($data['logoRatio'] ?? 1.0);
                $product->setText('Logo personnalisé'); // Texte par défaut
                $product->setTypeEcriture('Arial'); // Police par défaut
            } else {
                // Mode texte
                $product->setText($data['text'] ?? '');
                $product->setTypeEcriture($data['typeEcriture'] ?? 'Arial');
            }

            // Persister le produit
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            // Ajouter au panier avec le prix calculé
            $quantity = (int) ($data['quantity'] ?? 1);
            
            $cartItem = $this->cartService->addToCart($product, $quantity, $price);

            return $this->json([
                'success' => true,
                'message' => 'Produit personnalisé ajouté au panier',
                'cartItemsCount' => $this->cartService->getCartItemsCount(),
                'cartTotal' => $this->cartService->getCartTotal(),
                'calculatedPrice' => $price,
                'item' => [
                    'id' => $cartItem->getId(),
                    'productName' => $product->isLogoMode() ? 'Logo personnalisé' : $product->getText(),
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

        // Récupération des données depuis JSON
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides'
            ], 400);
        }

        // Préparation des données de commande avec validation obligatoire
        $orderData = [
            'customerName' => $data['customerName'] ?? '',
            'customerEmail' => $data['customerEmail'] ?? '',
            'customerPhone' => $data['customerPhone'] ?? '',
            'notes' => $data['notes'] ?? '',
            'billingAddress' => $data['billingAddress'] ?? null,
            'shippingAddress' => $data['shippingAddress'] ?? null
        ];

        // Validation des champs obligatoires côté contrôleur
        $requiredFields = [
            'customerName' => 'Le nom du client est obligatoire',
            'customerEmail' => 'L\'email du client est obligatoire',
            'customerPhone' => 'Le téléphone du client est obligatoire'
        ];

        $errors = [];
        foreach ($requiredFields as $field => $message) {
            if (empty(trim($orderData[$field]))) {
                $errors[$field] = $message;
            }
        }

        // Validation de l'adresse de facturation
        if (empty($orderData['billingAddress'])) {
            $errors['billingAddress'] = 'L\'adresse de facturation est obligatoire';
        }

        if (!empty($errors)) {
            return $this->json([
                'success' => false,
                'message' => 'Tous les champs obligatoires doivent être remplis',
                'errors' => $errors
            ], 400);
        }

        try {
            $order = $orderService->createOrderFromCart($cart, $orderData);
            $orderService->clearCartAfterOrder($cart);

            $logger->info('Order created', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'total' => $order->getTotal(),
                'billing_address' => $order->getBillingAddress()->getFullAddress(),
                'shipping_address' => $order->getShippingAddress()->getFullAddress()
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

    #[Route('/checkout', name: 'app_cart_checkout', methods: ['GET'])]
    public function checkout(): Response
    {
        $cart = $this->cartService->getCurrentCart();

        if ($cart->getCartItems()->count() === 0) {
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('cart/checkout.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/order-success/{id}', name: 'app_cart_order_success', methods: ['GET'])]
    public function orderSuccess(\App\Entity\Order $order): Response
    {
        return $this->render('cart/order_success_simple.html.twig', [
            'order' => $order,
        ]);
    }
}