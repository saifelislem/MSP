# Flux d'ajout au panier avec confirmation

## Étapes du processus

### 1. Création du produit personnalisé
- L'utilisateur clique sur "Créer un produit personnalisé" ou "Quick View"
- Un modal s'ouvre avec le formulaire de personnalisation
- L'utilisateur remplit:
  - Texte à imprimer
  - Largeur (cm)
  - Hauteur (cm)
  - Police d'écriture
  - Quantité
- Un aperçu en temps réel s'affiche

### 2. Ajout au panier (mode preview)
- L'utilisateur clique sur "Ajouter au panier"
- Le modal se ferme
- Le sidebar du panier s'ouvre automatiquement
- Le produit s'affiche dans une zone spéciale "Produit à confirmer" (fond jaune)
- Le produit n'est PAS encore enregistré en base de données
- Les données sont stockées temporairement dans sessionStorage

### 3. Confirmation dans le sidebar
L'utilisateur a deux options:

#### Option A: Confirmer
- Clic sur le bouton "Confirmer"
- Le produit est créé en base de données
- Le produit est ajouté au panier
- Message de succès affiché
- La page se recharge
- Le produit apparaît maintenant dans la liste normale du panier

#### Option B: Annuler
- Clic sur le bouton "Annuler"
- Le produit en attente disparaît
- Aucune action en base de données
- Message d'information affiché

## Avantages de ce système

1. **Validation visuelle**: L'utilisateur voit exactement ce qu'il va ajouter
2. **Pas d'erreur**: Possibilité d'annuler avant l'enregistrement
3. **Expérience fluide**: Tout se passe dans le sidebar sans rechargement
4. **Feedback clair**: Zone distincte pour le produit en attente

## Fichiers modifiés

- `templates/includes/product_modal.html.twig`: Modal de création
- `templates/includes/cart_sidebar.html.twig`: Sidebar avec zone de confirmation
- `public/css/cart-sidebar.css`: Styles pour la zone de confirmation
- `src/Controller/ProductController.php`: Route de création
- `templates/home.html.twig`: Intégration du modal

## Technologies utilisées

- **SessionStorage**: Stockage temporaire des données du produit
- **AJAX/Fetch**: Communication avec le serveur
- **SweetAlert**: Notifications utilisateur
- **CSS Animations**: Effets visuels
