<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private EntityManagerInterface $entityManager;
    private CartRepository $cartRepository;
    private CartItemRepository $cartItemRepository;
    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->requestStack = $requestStack;
    }

    public function getCurrentCart(): Cart
    {
        $session = $this->requestStack->getSession();
        
        // Démarrer la session si elle n'est pas déjà démarrée
        if (!$session->isStarted()) {
            $session->start();
        }
        
        $sessionId = $session->getId();

        $cart = $this->cartRepository->findBySessionId($sessionId);

        if (!$cart) {
            $cart = new Cart();
            $cart->setSessionId($sessionId);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        return $cart;
    }

    public function addToCart(Product $product, int $quantity = 1, float $price = 10.0): CartItem
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('La quantité doit être supérieure à 0');
        }

        if ($price < 0) {
            throw new \InvalidArgumentException('Le prix ne peut pas être négatif');
        }

        $cart = $this->getCurrentCart();

        // Vérifier si le produit est déjà dans le panier
        foreach ($cart->getCartItems() as $existingItem) {
            if ($existingItem->getProduct()->getId() === $product->getId()) {
                $existingItem->setQuantity($existingItem->getQuantity() + $quantity);
                $cart->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->flush();
                return $existingItem;
            }
        }

        // Créer un nouvel item
        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);
        $cartItem->setPrice($price);

        $cart->addCartItem($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();

        return $cartItem;
    }

    public function removeFromCart(int $cartItemId): bool
    {
        $cartItem = $this->cartItemRepository->find($cartItemId);
        
        if (!$cartItem) {
            return false;
        }

        $cart = $cartItem->getCart();
        $cart->removeCartItem($cartItem);
        $cart->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();

        return true;
    }

    public function updateQuantity(int $cartItemId, int $quantity): bool
    {
        $cartItem = $this->cartItemRepository->find($cartItemId);
        
        if (!$cartItem || $quantity <= 0) {
            return false;
        }

        $cartItem->setQuantity($quantity);
        $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();

        return true;
    }

    public function clearCart(): void
    {
        $cart = $this->getCurrentCart();
        
        foreach ($cart->getCartItems() as $item) {
            $this->entityManager->remove($item);
        }
        
        $this->entityManager->flush();
    }

    public function getCartItemsCount(): int
    {
        return $this->getCurrentCart()->getTotalItems();
    }

    public function getCartTotal(): float
    {
        return $this->getCurrentCart()->getTotal();
    }
}