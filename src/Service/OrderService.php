<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Address;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private AddressService $addressService
    ) {}

    public function createOrderFromCart(Cart $cart, array $orderData = []): Order
    {
        // Validation du panier
        if ($cart->getCartItems()->count() === 0) {
            throw new \InvalidArgumentException('Le panier est vide');
        }

        // Validation des données obligatoires
        $this->validateOrderData($orderData);

        $order = new Order();
        $order->setTotal($cart->getTotal());
        
        // Informations client obligatoires
        $order->setCustomerName(trim($orderData['customerName']));
        $order->setCustomerEmail(trim($orderData['customerEmail']));
        $order->setCustomerPhone(trim($orderData['customerPhone']));
        
        if (!empty($orderData['notes'])) {
            $order->setNotes(trim($orderData['notes']));
        }

        // Gestion des adresses
        $billingAddress = $this->handleBillingAddress($orderData);
        $shippingAddress = $this->handleShippingAddress($orderData, $billingAddress);

        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);

        // Associer le client si fourni
        if (isset($orderData['customer']) && $orderData['customer'] instanceof Customer) {
            $order->setCustomer($orderData['customer']);
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

        // Validation finale de l'entité Order
        $violations = $this->validator->validate($order);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException('Erreurs de validation: ' . implode(', ', $errors));
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    /**
     * Valide toutes les données obligatoires de la commande
     */
    private function validateOrderData(array $data): void
    {
        $requiredFields = [
            'customerName' => 'Le nom du client est obligatoire',
            'customerEmail' => 'L\'email du client est obligatoire',
            'customerPhone' => 'Le téléphone du client est obligatoire',
            'billingAddress' => 'L\'adresse de facturation est obligatoire'
        ];

        foreach ($requiredFields as $field => $message) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException($message);
            }
        }

        // Validation de l'email
        if (!filter_var($data['customerEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('L\'email du client n\'est pas valide');
        }

        // Validation du téléphone
        if (!preg_match('/^[0-9\s\-\+\(\)\.]{10,}$/', $data['customerPhone'])) {
            throw new \InvalidArgumentException('Le numéro de téléphone n\'est pas valide');
        }

        // Validation de l'adresse de facturation
        if (is_array($data['billingAddress'])) {
            $addressErrors = $this->addressService->validateAddressData($data['billingAddress']);
            if (!empty($addressErrors)) {
                throw new \InvalidArgumentException('Erreurs dans l\'adresse de facturation: ' . implode(', ', $addressErrors));
            }
        }

        // Validation de l'adresse de livraison si différente
        if (isset($data['shippingAddress']) && is_array($data['shippingAddress'])) {
            $addressErrors = $this->addressService->validateAddressData($data['shippingAddress']);
            if (!empty($addressErrors)) {
                throw new \InvalidArgumentException('Erreurs dans l\'adresse de livraison: ' . implode(', ', $addressErrors));
            }
        }
    }

    /**
     * Gère l'adresse de facturation
     */
    private function handleBillingAddress(array $orderData): Address
    {
        if ($orderData['billingAddress'] instanceof Address) {
            return $orderData['billingAddress'];
        }

        if (is_array($orderData['billingAddress'])) {
            return $this->addressService->createTemporaryAddress($orderData['billingAddress']);
        }

        throw new \InvalidArgumentException('Adresse de facturation invalide');
    }

    /**
     * Gère l'adresse de livraison
     */
    private function handleShippingAddress(array $orderData, Address $billingAddress): Address
    {
        // Si pas d'adresse de livraison spécifiée, utiliser l'adresse de facturation
        if (!isset($orderData['shippingAddress']) || empty($orderData['shippingAddress'])) {
            return $billingAddress;
        }

        if ($orderData['shippingAddress'] instanceof Address) {
            return $orderData['shippingAddress'];
        }

        if (is_array($orderData['shippingAddress'])) {
            return $this->addressService->createTemporaryAddress($orderData['shippingAddress']);
        }

        // Si c'est "same_as_billing", utiliser l'adresse de facturation
        if ($orderData['shippingAddress'] === 'same_as_billing') {
            return $billingAddress;
        }

        throw new \InvalidArgumentException('Adresse de livraison invalide');
    }

    public function clearCartAfterOrder(Cart $cart): void
    {
        foreach ($cart->getCartItems() as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();
    }
}
