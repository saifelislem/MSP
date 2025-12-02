# Point 2: CRUD + Panier Basique

## ✅ Ce qui est disponible

### 1. Gestion des Produits (CRUD)
**Route**: `/product`

**Fonctionnalités**:
- ✅ Liste des produits (tableau)
- ✅ Créer un produit (formulaire)
- ✅ Modifier un produit
- ✅ Voir les détails d'un produit
- ✅ Supprimer un produit
- ✅ **Nouveau**: Bouton "Ajouter au panier" sur la page détails

### 2. Système de Panier
**Route**: `/cart`

**Fonctionnalités**:
- ✅ Voir le panier
- ✅ Ajouter un produit au panier
- ✅ Modifier la quantité
- ✅ Supprimer un article
- ✅ Vider le panier
- ✅ Compteur du panier dans le header

**Routes API**:
- `POST /cart/add/{id}` - Ajouter un produit
- `PUT /cart/update/{id}` - Modifier la quantité
- `DELETE /cart/remove/{id}` - Supprimer un article
- `DELETE /cart/clear` - Vider le panier
- `GET /cart/count` - Obtenir le nombre d'articles

### 3. Entités
- **Product**: text, largeur, hauteur, typeEcriture
- **Cart**: sessionId, createdAt, updatedAt
- **CartItem**: product, quantity, price

### 4. Services
- **CartService**: Logique métier du panier
- **CartSubscriber**: Injection globale du panier dans les templates

## 🚫 Ce qui n'est PAS disponible

- ❌ Page d'accueil client
- ❌ Sidebar du panier
- ❌ Modal de création de produit
- ❌ Confirmation avant ajout
- ❌ Sélection multiple
- ❌ Aperçu en temps réel

## 🎯 Comment utiliser

### Créer un produit
1. Accédez à `/product`
2. Cliquez sur "Nouveau Produit"
3. Remplissez le formulaire
4. Cliquez sur "Enregistrer"

### Ajouter au panier
1. Accédez à `/product`
2. Cliquez sur "Voir" sur un produit
3. Cliquez sur "Ajouter au panier"
4. Vous êtes redirigé vers `/cart`

### Gérer le panier
1. Accédez à `/cart`
2. Modifiez les quantités avec +/-
3. Supprimez des articles avec l'icône ❌
4. Videz le panier avec "Vider le panier"

## 📁 Structure

```
/                           → Redirige vers /product
/product                    → Liste des produits (CRUD)
/product/new                → Créer un produit
/product/{id}               → Voir un produit + Ajouter au panier
/product/{id}/edit          → Modifier un produit
/cart                       → Page du panier
/cart/add/{id}              → Ajouter au panier (POST)
/cart/update/{id}           → Modifier quantité (PUT)
/cart/remove/{id}           → Supprimer article (DELETE)
/cart/clear                 → Vider panier (DELETE)
```

## 🎨 Interface

### Page Produits (/product)
```
┌─────────────────────────────────────────┐
│ Liste des Produits    [+ Nouveau]      │
├─────────────────────────────────────────┤
│ ID | Texte | L | H | Police | Actions  │
│ 1  | Test  | 10| 10| Arial  | 👁️ ✏️    │
└─────────────────────────────────────────┘
```

### Page Panier (/cart)
```
┌─────────────────────────────────────────┐
│ 🛒 Mon Panier                           │
├─────────────────────────────────────────┤
│ [Image] Produit 1                       │
│         10cm × 10cm                     │
│         Quantité: [1] [-] [+]           │
│         10.00€                      ❌  │
├─────────────────────────────────────────┤
│ Total: 10.00€                           │
│ [Voir le panier] [Commander]            │
└─────────────────────────────────────────┘
```

## 🔧 Fichiers Principaux

- `src/Controller/ProductController.php` - CRUD produits
- `src/Controller/CartController.php` - Gestion panier
- `src/Service/CartService.php` - Logique métier
- `src/Entity/Product.php` - Entité produit
- `src/Entity/Cart.php` - Entité panier
- `src/Entity/CartItem.php` - Entité article panier
- `templates/product/` - Templates CRUD
- `templates/cart/index.html.twig` - Page panier

## ✨ Avantages

✅ **Simple et direct** - Pas de complexité inutile
✅ **Fonctionnel** - Tout le nécessaire pour gérer produits et panier
✅ **Stable** - Code testé et fonctionnel
✅ **Base solide** - Prêt pour ajouter des fonctionnalités
