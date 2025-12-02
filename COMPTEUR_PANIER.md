# Compteur du Panier Dynamique

## 🎯 Fonctionnement

Le compteur du panier (badge avec le nombre) se met à jour automatiquement pour refléter:
- ✅ Les articles **confirmés** dans le panier (en BDD)
- ✅ Le produit **en attente** de confirmation (en sessionStorage)

## 📊 Calcul du Compteur

```
Compteur Total = Articles en BDD + Produit en attente
```

### Exemples

**Cas 1: Panier vide**
```
Articles en BDD: 0
Produit en attente: 0
Compteur affiché: 0
```

**Cas 2: Produit en attente**
```
Articles en BDD: 0
Produit en attente: 1
Compteur affiché: 1 ← Montre le produit en attente
```

**Cas 3: Articles + Produit en attente**
```
Articles en BDD: 2
Produit en attente: 1
Compteur affiché: 3 ← Total
```

**Cas 4: Après confirmation**
```
Articles en BDD: 3
Produit en attente: 0
Compteur affiché: 3
```

## 🔄 Mise à Jour Automatique

### Quand le compteur se met à jour:

1. **Au chargement de la page**
   - Vérifie les articles en BDD
   - Vérifie s'il y a un produit en attente
   - Affiche le total

2. **Après "Ajouter au panier"**
   - Ajoute +1 pour le produit en attente
   - Met à jour immédiatement le badge

3. **Après "Confirmer"**
   - Le produit passe de "en attente" à "en BDD"
   - Page se recharge
   - Compteur se met à jour

4. **Après "Annuler"**
   - Enlève le produit en attente
   - Met à jour le badge (-1)

## 🎨 Interface

### Badge du Panier

```
┌─────────────────────────────┐
│ Logo    Menu    🛒 (3)      │ ← Badge avec le nombre
└─────────────────────────────┘
```

### Évolution du Badge

```
Étape 1: Panier vide
🛒 (0)

Étape 2: Ajouter un produit (en attente)
🛒 (1) ← +1 immédiatement

Étape 3: Confirmer
🛒 (1) ← Reste à 1 (maintenant en BDD)

Étape 4: Ajouter un autre produit
🛒 (2) ← +1 pour le nouveau en attente

Étape 5: Annuler le dernier
🛒 (1) ← -1 pour l'annulation
```

## 💻 Code

### Fonction `updateCartBadge()`

```javascript
function updateCartBadge() {
    // 1. Récupérer le nombre d'articles en BDD
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const currentCount = data.count || 0;
            
            // 2. Vérifier s'il y a un produit en attente
            const pendingProduct = sessionStorage.getItem('pendingProduct');
            
            // 3. Calculer le total
            const totalCount = pendingProduct ? currentCount + 1 : currentCount;
            
            // 4. Mettre à jour tous les badges
            const badges = document.querySelectorAll('[data-notify]');
            badges.forEach(badge => {
                badge.setAttribute('data-notify', totalCount);
            });
        });
}
```

### Appels de la Fonction

**1. Au chargement de la page**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
});
```

**2. Après ajout au panier**
```javascript
function addToCartFromModal() {
    // ... code d'ajout ...
    updateCartBadge(); // ← Mise à jour
}
```

**3. Après annulation**
```javascript
function cancelPendingProduct() {
    sessionStorage.removeItem('pendingProduct');
    updateCartBadge(); // ← Mise à jour
}
```

## ✨ Avantages

✅ **Feedback immédiat** - L'utilisateur voit le changement instantanément
✅ **Précis** - Compte les articles confirmés ET en attente
✅ **Cohérent** - Se met à jour automatiquement partout
✅ **Intuitif** - L'utilisateur sait combien d'articles il a

## 🎯 Comportement Détaillé

### Scénario Complet

```
1. Page d'accueil
   Badge: 🛒 (0)

2. Quick View sur produit 1
   Badge: 🛒 (0)

3. Ajouter au panier
   Badge: 🛒 (1) ← +1 immédiatement

4. Continuer à naviguer
   Badge: 🛒 (1) ← Reste à 1

5. Ouvrir le panier
   Badge: 🛒 (1)
   Sidebar: "Produit à confirmer"

6. Confirmer
   Badge: 🛒 (1) ← Toujours 1 (maintenant en BDD)
   Page recharge

7. Quick View sur produit 2
   Badge: 🛒 (1)

8. Ajouter au panier
   Badge: 🛒 (2) ← +1 pour le nouveau

9. Ouvrir le panier
   Badge: 🛒 (2)
   Sidebar: 1 article confirmé + 1 en attente

10. Annuler le produit en attente
    Badge: 🛒 (1) ← -1 pour l'annulation
```

## 🔧 Fichiers Modifiés

- `templates/includes/product_modal.html.twig` - Appel après ajout
- `templates/includes/cart_sidebar.html.twig` - Appel après annulation
- `public/js/cart-functions.js` - Fonction updateCartBadge()

## 🎉 Résultat

Un compteur **intelligent et réactif** qui:
- 📊 Affiche le nombre total (confirmés + en attente)
- ⚡ Se met à jour instantanément
- 🔄 Reste synchronisé sur toutes les pages
- ✅ Donne un feedback clair à l'utilisateur
