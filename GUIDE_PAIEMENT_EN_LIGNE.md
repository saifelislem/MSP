# 💳 Guide Complet - Paiement en Ligne MSP

## 🎯 Options de Paiement

### Option 1: Stripe (Recommandé) ⭐
- ✅ **Le plus populaire** et facile à intégrer
- ✅ **Frais:** 1.4% + 0.25€ par transaction (Europe)
- ✅ **Cartes:** Visa, Mastercard, Amex, etc.
- ✅ **Sécurisé:** PCI-DSS compliant
- ✅ **Test gratuit** avec cartes de test

### Option 2: PayPal
- ✅ **Très connu** des clients
- ✅ **Frais:** 2.9% + 0.35€ par transaction
- ✅ **Compte PayPal** ou carte bancaire
- ✅ **Protection acheteur**

### Option 3: Mollie (Europe)
- ✅ **Spécialisé Europe**
- ✅ **Frais:** 1.8% + 0.25€
- ✅ **Nombreux moyens:** CB, PayPal, Bancontact, etc.
- ✅ **Interface simple**

---

## 🚀 INTÉGRATION STRIPE (Recommandé)

### Étape 1: Créer un Compte Stripe

1. **Allez sur:** https://stripe.com
2. **Créez un compte** (gratuit)
3. **Activez le mode test** (pour développement)
4. **Récupérez vos clés API:**
   - Clé publique (pk_test_...)
   - Clé secrète (sk_test_...)

---

### Étape 2: Installation

```bash
composer require stripe/stripe-php
```

---

### Étape 3: Configuration

**Ajoutez dans `.env`:**
```env
STRIPE_PUBLIC_KEY=pk_test_votre_cle_publique
STRIPE_SECRET_KEY=sk_test_votre_cle_secrete
```

---

### Étape 4: Créer le Service de Paiement

**Fichier:** `src/Service/StripePaymentService.php`

```php
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

    public function createCheckoutSession(array $items, string $successUrl, string $cancelUrl): Session
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

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}
```

---

### Étape 5: Configurer le Service

**Fichier:** `config/services.yaml`

```yaml
services:
    App\Service\StripePaymentService:
        arguments:
            $stripeSecretKey: '%env(STRIPE_SECRET_KEY)%'
            $stripePublicKey: '%env(STRIPE_PUBLIC_KEY)%'
```

---

### Étape 6: Créer le Controller de Paiement

**Fichier:** `src/Controller/PaymentController.php`

```php
<?php

namespace App\Controller;

use App\Service\StripePaymentService;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/create-checkout-session', name: 'payment_create_checkout')]
    public function createCheckoutSession(
        StripePaymentService $stripeService,
        CartService $cartService
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
                    '%dx%dcm - %s',
                    $product->getLargeur(),
                    $product->getHauteur(),
                    $product->getTypeEcriture()
                ),
                'price' => $cartItem->getPrice(),
                'quantity' => $cartItem->getQuantity(),
            ];
        }

        // Créer la session Stripe
        $session = $stripeService->createCheckoutSession(
            $items,
            $this->generateUrl('payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('payment_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL)
        );

        // Rediriger vers Stripe Checkout
        return $this->redirect($session->url);
    }

    #[Route('/success', name: 'payment_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    #[Route('/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé');
        return $this->redirectToRoute('app_cart');
    }
}
```

---

### Étape 7: Modifier le Bouton de Paiement

**Dans:** `templates/cart/index.html.twig`

**Remplacez:**
```twig
<button onclick="openQuickCheckout()">
    Passer commande
</button>
```

**Par:**
```twig
<a href="{{ path('payment_create_checkout') }}" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer">
    <i class="zmdi zmdi-card m-r-5"></i> Payer maintenant
</a>
```

---

### Étape 8: Créer la Page de Succès

**Fichier:** `templates/payment/success.html.twig`

```twig
{% extends 'base.html.twig' %}

{% block body %}
<div class="container p-t-80 p-b-80">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <i class="zmdi zmdi-check-circle" style="font-size: 80px; color: #28a745;"></i>
            <h2 class="mtext-105 cl2 p-t-30">Paiement Réussi!</h2>
            <p class="stext-113 cl6 p-t-20">
                Votre paiement a été traité avec succès.<br>
                Vous allez recevoir un email de confirmation.
            </p>
            <a href="{{ path('app_home') }}" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer m-t-30">
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>
{% endblock %}
```

---

## 🧪 TEST EN MODE DÉVELOPPEMENT

### Cartes de Test Stripe

**Carte qui fonctionne:**
```
Numéro: 4242 4242 4242 4242
Date: N'importe quelle date future (ex: 12/25)
CVC: N'importe quel 3 chiffres (ex: 123)
```

**Carte refusée:**
```
Numéro: 4000 0000 0000 0002
```

**Carte nécessitant 3D Secure:**
```
Numéro: 4000 0027 6000 3184
```

---

## 📊 Flux Complet avec Paiement

```
1. Client ajoute produits au panier
   ↓
2. Client va sur /cart
   ↓
3. Client clique "Payer maintenant"
   ↓
4. Redirection vers Stripe Checkout
   ↓
5. Client entre ses infos de carte
   ↓
6. Paiement traité par Stripe
   ↓
7. Redirection vers /payment/success
   ↓
8. Commande créée automatiquement
   ↓
9. Email de confirmation envoyé
```

---

## 🔒 Sécurité

### Stripe gère automatiquement:
- ✅ **Cryptage** des données de carte
- ✅ **PCI-DSS** compliance
- ✅ **3D Secure** (authentification forte)
- ✅ **Détection de fraude**
- ✅ **Remboursements** faciles

### Vous ne stockez JAMAIS:
- ❌ Numéros de carte
- ❌ CVV
- ❌ Données bancaires

---

## 💰 Coûts Stripe

### Tarifs Europe
- **Par transaction:** 1.4% + 0.25€
- **Exemple:** Commande de 50€ = 0.95€ de frais
- **Pas d'abonnement** mensuel
- **Pas de frais** de mise en place

### Paiements
- **Délai:** 2-7 jours ouvrés
- **Vers:** Votre compte bancaire
- **Automatique:** Tous les jours

---

## 📱 Fonctionnalités Avancées

### 1. Webhooks (Notifications)
Recevoir des notifications automatiques:
- Paiement réussi
- Paiement échoué
- Remboursement

### 2. Abonnements
Pour des paiements récurrents

### 3. Apple Pay / Google Pay
Paiement en 1 clic

### 4. SEPA Direct Debit
Prélèvement bancaire

---

## 🎯 Prochaines Étapes

### Après l'intégration de base:

1. **Créer la commande** après paiement réussi
2. **Envoyer un email** de confirmation
3. **Gérer les webhooks** Stripe
4. **Ajouter des factures** PDF
5. **Gérer les remboursements**

---

## 📞 Support

### Documentation Stripe
- **Docs:** https://stripe.com/docs
- **API:** https://stripe.com/docs/api
- **Support:** support@stripe.com

### Test
- **Dashboard:** https://dashboard.stripe.com/test
- **Logs:** Voir toutes les transactions de test

---

## ✅ Checklist d'Intégration

- [ ] Compte Stripe créé
- [ ] Clés API récupérées
- [ ] Package Stripe installé
- [ ] Service créé
- [ ] Controller créé
- [ ] Bouton de paiement ajouté
- [ ] Page de succès créée
- [ ] Test avec carte de test
- [ ] Vérification dans dashboard Stripe

---

**Prêt à intégrer le paiement en ligne!** 💳✨

Voulez-vous que je commence l'intégration maintenant?
