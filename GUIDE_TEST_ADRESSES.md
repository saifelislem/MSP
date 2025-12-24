# Guide de Test - Gestion des Adresses Multiples

## Comment Tester les Nouvelles Fonctionnalités

### 1. Test de la Validation Obligatoire

#### Étapes de Test :
1. **Aller sur la page d'accueil** : `http://localhost/`
2. **Ajouter des produits au panier** (au moins un produit)
3. **Aller au panier** : Cliquer sur l'icône panier ou `/cart/`
4. **Cliquer sur "Finaliser la commande"** : Vous serez redirigé vers `/cart/checkout`

#### Tests de Validation :
1. **Essayer de soumettre le formulaire vide** :
   - Cliquer sur "Finaliser la commande" sans remplir les champs
   - ✅ **Résultat attendu** : Message d'erreur "Veuillez remplir tous les champs obligatoires"

2. **Tester chaque champ obligatoire** :
   - Laisser un champ vide à la fois
   - ✅ **Résultat attendu** : Erreur spécifique pour chaque champ manquant

3. **Tester les formats** :
   - Email invalide (ex: "test@")
   - Téléphone invalide (ex: "123")
   - Code postal invalide (ex: "ABC12")
   - ✅ **Résultat attendu** : Messages d'erreur de format

### 2. Test des Adresses Multiples

#### Scénario 1 : Première Commande (Nouvelle Adresse)
1. **Remplir tous les champs obligatoires** :
   ```
   Nom: Jean Dupont
   Email: jean.dupont@email.com
   Téléphone: 0123456789
   
   Adresse de facturation:
   Nom: Jean Dupont
   Adresse: 123 Rue de la Paix
   Code postal: 75001
   Ville: Paris
   Pays: France
   ```

2. **Soumettre la commande**
   - ✅ **Résultat attendu** : Commande créée avec succès
   - ✅ **Vérification** : Page de succès affiche l'adresse de facturation

#### Scénario 2 : Adresses Différentes (Facturation ≠ Livraison)
1. **Remplir l'adresse de facturation** (comme ci-dessus)
2. **Décocher "Identique à l'adresse de facturation"**
3. **Remplir une adresse de livraison différente** :
   ```
   Adresse de livraison:
   Nom: Marie Dupont
   Adresse: 456 Avenue des Champs
   Code postal: 75008
   Ville: Paris
   Pays: France
   ```

4. **Soumettre la commande**
   - ✅ **Résultat attendu** : Commande créée avec les deux adresses
   - ✅ **Vérification** : Page de succès affiche les deux adresses distinctes

### 3. Test de l'Interface Utilisateur

#### Validation en Temps Réel :
1. **Cliquer dans un champ obligatoire puis sortir sans remplir**
   - ✅ **Résultat attendu** : Bordure rouge + message d'erreur

2. **Remplir un champ correctement**
   - ✅ **Résultat attendu** : Bordure redevient normale

#### Basculement Adresse de Livraison :
1. **Cocher/décocher "Identique à l'adresse de facturation"**
   - ✅ **Résultat attendu** : Formulaire de livraison apparaît/disparaît

### 4. Test de la Base de Données

#### Vérifier les Données Créées :
1. **Après une commande réussie**, vérifier en base :
   ```sql
   -- Voir les adresses créées
   SELECT * FROM address ORDER BY id DESC LIMIT 5;
   
   -- Voir les commandes avec leurs adresses
   SELECT o.order_number, o.customer_name, 
          ba.name as billing_name, ba.street as billing_street,
          sa.name as shipping_name, sa.street as shipping_street
   FROM `order` o
   LEFT JOIN address ba ON o.billing_address_id = ba.id
   LEFT JOIN address sa ON o.shipping_address_id = sa.id
   ORDER BY o.id DESC LIMIT 5;
   ```

### 5. Test des Cas d'Erreur

#### Test 1 : Panier Vide
1. **Aller directement sur** `/cart/checkout` avec un panier vide
   - ✅ **Résultat attendu** : Redirection vers `/cart/` avec message d'erreur

#### Test 2 : Données Malformées
1. **Envoyer des données JSON invalides** (test API) :
   ```javascript
   fetch('/cart/create-order', {
     method: 'POST',
     headers: {'Content-Type': 'application/json'},
     body: 'invalid json'
   })
   ```
   - ✅ **Résultat attendu** : Erreur 400 "Données invalides"

### 6. Test de l'Historique des Commandes

#### Vérifier la Conservation des Adresses :
1. **Créer plusieurs commandes avec des adresses différentes**
2. **Aller dans l'admin** : `/admin/orders/`
3. **Voir les détails de chaque commande**
   - ✅ **Résultat attendu** : Chaque commande conserve ses adresses spécifiques

### 7. Test de Performance

#### Test de Charge :
1. **Créer plusieurs adresses pour un même client**
2. **Vérifier que les performances restent bonnes**
3. **Tester avec de nombreuses commandes**

## Résultats Attendus

### ✅ Validation Réussie Si :
- Impossible de créer une commande sans tous les champs obligatoires
- Messages d'erreur clairs et spécifiques
- Validation du format email et téléphone
- Code postal accepte uniquement 5 chiffres
- Interface réactive et intuitive

### ✅ Adresses Multiples Réussies Si :
- Possibilité d'avoir des adresses de facturation et livraison différentes
- Conservation de toutes les adresses dans l'historique
- Chaque commande garde ses adresses spécifiques
- Pas de perte de données lors des modifications

### ✅ Expérience Utilisateur Réussie Si :
- Processus de commande fluide et intuitif
- Validation en temps réel sans rechargement de page
- Messages d'erreur utiles et compréhensibles
- Interface responsive et accessible

## Commandes Utiles pour le Debug

```bash
# Voir les logs en temps réel
tail -f var/log/dev.log

# Vider le cache
php bin/console cache:clear

# Voir les routes
php bin/console debug:router | findstr cart

# Voir la structure de la base
php bin/console doctrine:schema:update --dump-sql

# Créer des données de test
php bin/console doctrine:fixtures:load
```

## Points de Contrôle Critiques

1. **Sécurité** : Aucune commande ne peut être créée sans validation complète
2. **Intégrité** : Toutes les adresses sont correctement liées aux commandes
3. **Traçabilité** : Historique complet des adresses utilisées
4. **Performance** : Temps de réponse acceptable même avec de nombreuses adresses
5. **Utilisabilité** : Interface intuitive et messages d'erreur clairs

Ce guide permet de valider que toutes les fonctionnalités de validation obligatoire et de gestion des adresses multiples fonctionnent correctement.