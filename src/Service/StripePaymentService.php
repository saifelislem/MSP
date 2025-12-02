<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripePaymentService
{
    private string $secretKey;
    private string $publicKey;

    public function __construct(string $stripeSecretKey, string $stripePublicKey)
    {
        $this->secretKey = $stripeSecretKey;
        $this->publicKey = $stripePublicKey;
        Stripe::setApiKey($this->secretKey);
    }

    public function createCheckoutSession(array $items, string $successUrl, string $cancelUrl, array $metadata = []): Session
    {
        $lineItems = [];
        
        foreach ($items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                        'description' => $item['description'] ?? '',
                    ],
                    'unit_amount' => (int)($item['price'] * 100), // Prix en centimes
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $sessionData = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
        ];

        if (!empty($metadata)) {
            $sessionData['metadata'] = $metadata;
        }

        return Session::create($sessionData);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function retrieveSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }
}
