# Structure du Projet - Boutique Produits Personnalisés

## 📁 Organisation

### FRONT-END (Client)
**Route**: `/` (app_home)
**Contrôleur**: `HomecontrollerController`
**Template**: `templates/front_home.html.twig`

**Fonctionnalités**:
- ✅ Affichage de tous les produits de la base de données
- ✅ Bouton "Créer un produit" (ouvre le modal)
- ✅ Bouton "Ajouter au panier" sur chaque produit
- ✅ Sidebar du panier avec confirmation en 2 étapes
- ✅ Header simplifié (Accueil + Mon Panier)
- ✅ Footer minimaliste

### BACK-END (Administration)
**Route**: `/product` (app_product_index)
**Contrôleur**: `ProductController`
**Templates**: `templates/product/*.html.twig`

**Fonctionnalités**:
- ✅ Liste des produits (index)
- ✅ Créer un produit (new)
- ✅ Modifier un produit (edit)
- ✅ Voir un produit (show)
- ✅ Supprimer un produit (delete)

## 🔄 Flux Utilisateur (Front)

### 1. Page d'accueil (/)
```
Utilisateur arrive sur /
↓
Voit tous les produits disponibles
↓
Deux options:
  A) Créer un nouveau produit
  B) Ajouter un produit existant au panier
```

### 2. Option A: Créer un nouveau produit
```
Clic sur "Créer un produit"
↓
Modal s'ouvre avec formulaire
↓
Remplir: texte, largeur, hauteur, police, quantité
↓
Aperçu en temps réel
↓
Clic "Ajouter au panier"
↓
Sidebar s'ouvre avec produit en attente (zone jaune)
↓
Clic "Confirmer"
↓
Produit créé en BDD + ajouté au panier
```

### 3. Option B: Ajouter produit existant
```
Clic sur "Ajouter au panier" (sur un produit)
↓
Sidebar s'ouvre avec produit en attente (zone jaune)
↓
Clic "Confirmer"
↓
Produit ajouté au panier (déjà en BDD)
```

## 📂 Fichiers Principaux

### Contrôleurs
- `src/Controller/HomecontrollerController.php` - Front (/)
- `src/Controller/ProductController.php` - Back (/product)
- `src/Controller/CartController.php` - Panier (/cart)

### Templates Front
- `templates/front_home.html.twig` - Page d'accueil
- `templates/cart/index.html.twig` - Page panier
- `templates/includes/cart_sidebar.html.twig` - Sidebar panier
- `templates/includes/product_modal.html.twig` - Modal création produit

### Templates Back
- `templates/product/index.html.twig` - Liste produits
- `templates/product/new.html.twig` - Créer produit
- `templates/product/edit.html.twig` - Modifier produit
- `templates/product/show.html.twig` - Voir produit

### Entités
- `src/Entity/Product.php` - Produit (text, largeur, hauteur, typeEcriture)
- `src/Entity/Cart.php` - Panier (sessionId, createdAt, updatedAt)
- `src/Entity/CartItem.php` - Article panier (product, quantity, price)

### Services
- `src/Service/CartService.php` - Logique métier du panier
- `src/EventSubscriber/CartSubscriber.php` - Injection globale du panier

### Assets
- `public/css/cart-sidebar.css` - Styles sidebar
- `public/js/cart-functions.js` - Fonctions JavaScript panier

## 🎯 Routes Principales

| Route | Nom | Contrôleur | Description |
|-------|-----|------------|-------------|
| `/` | app_home | HomecontrollerController | Page d'accueil (Front) |
| `/product` | app_product_index | ProductController | Liste produits (Back) |
| `/product/new` | app_product_new | ProductController | Créer produit (Back) |
| `/product/{id}/edit` | app_product_edit | ProductController | Modifier produit (Back) |
| `/product/create-and-add-to-cart` | app_product_create_and_add | ProductController | Créer produit (AJAX) |
| `/cart` | app_cart | CartController | Page panier |
| `/cart/add/{id}` | app_cart_add | CartController | Ajouter au panier |
| `/cart/remove/{id}` | app_cart_remove | CartController | Retirer du panier |
| `/cart/clear` | app_cart_clear | CartController | Vider le panier |

## 🗄️ Base de Données

### Table: product
- id (INT, PK, AUTO_INCREMENT)
- text (VARCHAR 255)
- largeur (INT)
- hauteur (INT)
- type_ecriture (VARCHAR 100)

### Table: cart
- id (INT, PK, AUTO_INCREMENT)
- session_id (VARCHAR 255)
- created_at (DATETIME)
- updated_at (DATETIME)

### Table: cart_item
- id (INT, PK, AUTO_INCREMENT)
- cart_id (INT, FK → cart.id)
- product_id (INT, FK → product.id)
- quantity (INT)
- price (DECIMAL 10,2)

## 🚀 Accès

- **Front (Client)**: http://localhost:8000/
- **Back (Admin)**: http://localhost:8000/product
- **Panier**: http://localhost:8000/cart
