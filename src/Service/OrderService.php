<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function createOrderFromCart(Cart $cart, array $customerData = []): Order
    {
        // Validation
        if ($cart->getCartItems()->count() === 0) {
            throw new \InvalidArgumentException('Le panier est vide');
        }

        if (empty($customerData['name'])) {
            throw new \InvalidArgumentException('Le nom du client est requis');
        }

        if (empty($customerData['email'])) {
            throw new \InvalidArgumentException('L\'email du client est requis');
        }

        if (!filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('L\'email du client n\'est pas valide');
        }

        $order = new Order();
        $order->setTotal($cart->getTotal());
        
        // Informations client
        $order->setCustomerName(trim($customerData['name']));
        $order->setCustomerEmail(trim($customerData['email']));
        
        if (!empty($customerData['phone'])) {
            $order->setCustomerPhone(trim($customerData['phone']));
        }
        if (!empty($customerData['notes'])) {
            $order->setNotes(trim($customerData['notes']));
        }

        // Créer les OrderItems depuis les CartItems
        foreach ($cart->getCartItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setPrice($cartItem->getPrice());
            $orderItem->setOrder($order);
            
            $order->addOrderItem($orderItem);
            $this->entityManager->persist($orderItem);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    public function clearCartAfterOrder(Cart $cart): void
    {
        foreach ($cart->getCartItems() as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();
    }
}
