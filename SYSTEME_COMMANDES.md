# 📦 Système de Commandes - MSP

## ✅ Fichiers Créés

### Services
- `src/Service/OrderService.php` - Gestion des commandes

### Routes ajoutées dans CartController
- `/cart/checkout` - Page de finalisation
- `/cart/create-order` - Création de la commande (POST)
- `/cart/order-success/{id}` - Page de confirmation

### Templates
- `templates/cart/checkout.html.twig` - Formulaire client
- `templates/cart/order_success.html.twig` - Confirmation

---

## 🔄 Flux Complet de Commande

### 1. Client crée des produits personnalisés
- Sélectionne un modèle de lettre
- Remplit le formulaire (texte, dimensions, police)
- Clique "Ajouter au panier"
- Produit en attente dans le sidebar

### 2. Client confirme les produits
- Ouvre le sidebar du panier
- Voit les produits en attente
- Clique "Tout Confirmer"
- Produits ajoutés au panier

### 3. Client consulte son panier
- Va sur `/cart`
- Voit tous les articles avec images et détails
- Peut modifier les quantités
- Peut supprimer des articles

### 4. Client passe commande
- Clique "Passer commande"
- Redirigé vers `/cart/checkout`
- Remplit le formulaire:
  - Nom complet *
  - Email *
  - Téléphone
  - Notes (optionnel)

### 5. Commande créée
- Système crée la commande avec statut "pending"
- Génère un numéro unique (ex: CMD-20241113-A1B2C3)
- Copie tous les articles du panier
- Vide le panier
- Redirige vers la page de confirmation

### 6. Admin voit la commande
- Se connecte sur `/login`
- Va sur `/admin/orders`
- Voit la nouvelle commande
- Peut changer le statut:
  - En attente → En cours → Terminée
  - Ou Annulée

---

## 📊 Statuts des Commandes

| Statut | Description | Action Admin |
|--------|-------------|--------------|
| `pending` | En attente de traitement | Commande reçue |
| `processing` | En cours de fabrication | Admin commence |
| `completed` | Terminée et livrée | Admin termine |
| `cancelled` | Annulée | Admin annule |

---

## 🎯 Tester le Système

### Test Complet:

1. **Créer un produit:**
   - Aller sur `http://localhost:8000/`
   - Cliquer "Personnaliser" sur un modèle
   - Remplir: "TEST", 10cm, 10cm, Arial
   - Ajouter au panier

2. **Confirmer:**
   - Cliquer sur l'icône panier
   - Cliquer "Tout Confirmer"
   - Produit ajouté au panier

3. **Passer commande:**
   - Aller sur le panier
   - Cliquer "Passer commande"
   - Remplir:
     - Nom: Test Client
     - Email: test@test.com
     - Téléphone: 0612345678
   - Confirmer

4. **Vérifier dans l'admin:**
   - Se connecter: `/login`
   - Aller sur `/admin/orders`
   - Voir la commande créée
   - Cliquer "Voir" pour les détails

---

## 📋 Données de la Commande

### Order (Commande)
```php
- orderNumber: "CMD-20241113-A1B2C3"
- createdAt: Date de création
- status: "pending"
- total: 10.00€
- customerName: "Test Client"
- customerEmail: "test@test.com"
- customerPhone: "0612345678"
- notes: "Notes optionnelles"
```

### OrderItem (Article)
```php
- product: Référence au produit
- quantity: 1
- price: 10.00€
- subtotal: 10.00€ (calculé)
```

---

## 🔐 Sécurité

- ✅ Vérification panier non vide
- ✅ Validation des données client
- ✅ Génération numéro unique
- ✅ Transaction base de données
- ✅ Gestion des erreurs

---

## 📱 Pages Client

| Page | URL | Description |
|------|-----|-------------|
| Accueil | `/` | Sélection modèles |
| Panier | `/cart` | Voir le panier |
| Checkout | `/cart/checkout` | Finaliser |
| Confirmation | `/cart/order-success/{id}` | Commande créée |

---

## 🎨 Améliorations Futures

### À ajouter:
1. **Email de confirmation** automatique
2. **Suivi de commande** pour le client
3. **Paiement en ligne** (Stripe, PayPal)
4. **Facture PDF** téléchargeable
5. **Historique des commandes** client
6. **Notifications** admin (nouvelle commande)
7. **Export Excel** des commandes
8. **Statistiques** avancées

---

## ✅ Checklist de Test

### Client
- [ ] Créer un produit personnalisé
- [ ] Confirmer dans le sidebar
- [ ] Voir le panier complet
- [ ] Modifier une quantité
- [ ] Supprimer un article
- [ ] Passer commande
- [ ] Remplir le formulaire
- [ ] Voir la confirmation

### Admin
- [ ] Se connecter
- [ ] Voir la commande dans la liste
- [ ] Filtrer par statut
- [ ] Voir le détail
- [ ] Changer le statut
- [ ] Voir les articles avec images
- [ ] Voir les infos client

---

**Système de commandes opérationnel!** 🚀

Maintenant les commandes s'afficheront dans l'admin dès qu'un client finalise son panier.
