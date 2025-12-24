# 💰 Système de Calcul de Prix Personnalisé

## 🎯 Vue d'ensemble

Le système de calcul de prix personnalisé permet à l'administrateur de définir des formules de calcul spécifiques pour chaque produit, basées sur des facteurs comme les dimensions, la quantité, etc.

## ✨ Fonctionnalités

### 🔧 Configuration Admin
- **Interface d'administration** : `/admin/pricing/`
- **Configuration par produit** : Prix fixe ou personnalisé
- **Formules personnalisées** : Utilisation de variables et opérations mathématiques
- **Facteurs personnalisés** : Ajout de variables spécifiques au produit

### 🧮 Calcul en Temps Réel
- **Calcul automatique** : Prix mis à jour en temps réel dans le modal produit
- **API dédiée** : `/api/pricing/calculate/{id}`
- **Affichage détaillé** : Breakdown du calcul pour transparence

## 🏗️ Architecture

### Entités
```php
Product:
- useCustomPricing: boolean
- basePrice: decimal
- pricePerUnit: decimal
- pricingUnit: string (cm, m, linear_cm, linear_m, piece)
- pricingFormula: text (optionnel)
- pricingFactors: json (facteurs personnalisés)
```

### Services
- **PricingCalculatorService** : Logique de calcul
- **AdminPricingController** : Interface d'administration
- **Api/PricingController** : API pour calculs temps réel

### Frontend
- **pricing-calculator.js** : Calcul côté client
- **Modal produit** : Affichage prix en temps réel

## 📋 Types de Calcul

### 1. Prix Fixe
- Utilise le prix standard du produit
- Pas de calcul basé sur les dimensions

### 2. Prix Personnalisé Simple
```
Prix Total = Prix de Base + (Surface × Prix par Unité)
```

### 3. Prix avec Formule Personnalisée
```
Prix Total = {base_price} + ({length} * {width} * {price_per_unit})
```

## 🔢 Variables Disponibles

| Variable | Description |
|----------|-------------|
| `{base_price}` | Prix de base du produit |
| `{price_per_unit}` | Prix par unité configuré |
| `{length}` | Longueur en cm |
| `{width}` | Largeur en cm |
| `{height}` | Hauteur en cm |
| `{quantity}` | Quantité commandée |
| `{surface}` | Surface calculée (longueur × largeur) |
| `{perimeter}` | Périmètre calculé |

## 🎛️ Unités de Calcul

### Surface
- **cm** : Centimètres carrés (longueur × largeur)
- **m** : Mètres carrés (conversion automatique)

### Linéaire
- **linear_cm** : Centimètres linéaires (longueur uniquement)
- **linear_m** : Mètres linéaires (conversion automatique)

### Quantité
- **piece** : Prix par pièce (quantité × prix unitaire)

## 🚀 Utilisation

### Configuration Admin

1. **Accéder à l'interface** : `/admin/pricing/`
2. **Sélectionner un produit** : Cliquer sur "Configurer"
3. **Choisir le type** : Prix fixe ou personnalisé
4. **Configurer les paramètres** :
   - Prix de base
   - Prix par unité
   - Unité de calcul
   - Formule personnalisée (optionnel)
5. **Tester le calcul** : Bouton "Tester le Calcul"
6. **Sauvegarder**

### Côté Client

Le calcul se fait automatiquement quand l'utilisateur :
- Modifie les dimensions (largeur/hauteur)
- Change la quantité
- Ouvre le modal produit

## 🔧 Exemples de Configuration

### Enseigne Simple
```
Prix de base : 15.00€
Prix par unité : 2.50€/cm²
Unité : cm (centimètres carrés)

Calcul : 15.00 + (largeur × hauteur × 2.50)
```

### Enseigne avec Formule Complexe
```
Formule : {base_price} + ({surface} * {price_per_unit}) + ({perimeter} * 0.5)

Variables :
- base_price : 20.00€
- price_per_unit : 3.00€
- surface : largeur × hauteur
- perimeter : 2 × (largeur + hauteur)
```

### Enseigne Linéaire
```
Prix de base : 10.00€
Prix par unité : 1.50€/cm
Unité : linear_cm

Calcul : 10.00 + (largeur × 1.50)
```

## 🛡️ Sécurité

### Validation des Formules
- Caractères autorisés : `0-9+\-*\/\.\(\)\s\{\}a-zA-Z_`
- Évaluation sécurisée avec `eval()` contrôlé
- Fallback sur prix de base en cas d'erreur

### Validation des Données
- Dimensions minimales/maximales
- Prix positifs uniquement
- Formules syntaxiquement correctes

## 🔄 API

### Calcul de Prix
```http
POST /api/pricing/calculate/{productId}
Content-Type: application/json

{
  "dimensions": {
    "length": 50,
    "width": 20,
    "height": 0.5,
    "quantity": 1
  }
}
```

**Réponse :**
```json
{
  "price": 265.00,
  "formatted_price": "265,00 €",
  "base_price": 15.00,
  "use_custom_pricing": true,
  "pricing_unit": "cm",
  "dimensions": {
    "length": 50,
    "width": 20,
    "height": 0.5,
    "quantity": 1
  }
}
```

### Validation de Formule
```http
POST /api/pricing/validate-formula
Content-Type: application/json

{
  "formula": "{base_price} + ({length} * {width} * {price_per_unit})"
}
```

## 🎨 Interface Utilisateur

### Modal Produit
- Affichage du prix calculé en temps réel
- Détail du calcul (prix de base + surface)
- Indicateur de calcul pendant le traitement

### Interface Admin
- Liste des produits avec type de calcul
- Éditeur de formules avec variables
- Testeur de calcul intégré
- Aperçu en temps réel

## 🔍 Débogage

### Logs
Les erreurs de calcul sont loggées et un fallback sur le prix de base est appliqué.

### Test
Utilisez le bouton "Tester le Calcul" dans l'interface admin pour valider vos formules.

## 📈 Évolutions Futures

- Support de plus de variables (matériau, finition, etc.)
- Calculs conditionnels (if/then)
- Remises par quantité
- Intégration avec système de devis

---

**✅ Le système est maintenant opérationnel !**

Pour tester :
1. Démarrer le serveur : `php -S localhost:8000 -t public`
2. Aller sur `/admin/pricing/` pour configurer
3. Tester sur la page d'accueil avec le modal produit