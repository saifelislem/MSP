# Gestion des Adresses Multiples et Validation Obligatoire

## Fonctionnalités Implémentées

### 1. Validation Obligatoire des Champs

Lors de la confirmation de commande, **tous les champs suivants sont maintenant obligatoires** :

#### Informations Client (Obligatoires)
- **Nom complet** : Nom et prénom du client
- **Email** : Adresse email valide (format vérifié)
- **Téléphone** : Numéro de téléphone valide (minimum 10 caractères)

#### Adresse de Facturation (Obligatoire)
- **Nom sur l'adresse** : Nom du destinataire
- **Adresse** : Rue et numéro
- **Code postal** : 5 chiffres obligatoires
- **Ville** : Nom de la ville
- **Pays** : Sélection dans la liste (France par défaut)

#### Adresse de Livraison
- **Par défaut** : Identique à l'adresse de facturation
- **Optionnel** : Possibilité de spécifier une adresse différente
- **Mêmes validations** que l'adresse de facturation si différente

### 2. Gestion des Adresses Multiples

#### Entité Address
- Nouvelle entité pour gérer les adresses séparément
- Champs : nom, société, rue, complément, code postal, ville, pays
- Relation avec Customer (un client peut avoir plusieurs adresses)
- Système d'adresse par défaut

#### Fonctionnalités Adresses
- **Création d'adresses** : Nouvelles adresses pour chaque client
- **Adresses multiples** : Un client peut avoir plusieurs adresses
- **Adresse par défaut** : Une adresse principale par client
- **Réutilisation** : Sélection d'adresses existantes lors de nouvelles commandes
- **Adresses temporaires** : Création d'adresses pour clients non enregistrés

### 3. Facturation Séparée par Adresse

#### Système de Commandes
- **Adresse de facturation** : Obligatoire pour chaque commande
- **Adresse de livraison** : Peut être différente de la facturation
- **Traçabilité** : Chaque commande conserve ses adresses spécifiques
- **Historique** : Toutes les adresses utilisées sont conservées

#### Gestion des Factures
- Chaque commande a sa propre adresse de facturation
- Possibilité d'avoir des factures avec des adresses différentes
- Conservation de l'historique des adresses par commande

## Structure Technique

### Nouvelles Entités

#### Address
```php
- id: int
- name: string (obligatoire)
- company: string (optionnel)
- street: string (obligatoire)
- additionalInfo: string (optionnel)
- postalCode: string (obligatoire, 5 chiffres)
- city: string (obligatoire)
- country: string (obligatoire, défaut: France)
- isDefault: boolean
- customer: Customer (relation)
```

#### Order (Modifiée)
```php
// Nouveaux champs obligatoires avec validation
- customerName: string (obligatoire)
- customerEmail: string (obligatoire, format email)
- customerPhone: string (obligatoire, format téléphone)
- billingAddress: Address (obligatoire)
- shippingAddress: Address (obligatoire)
```

### Nouveaux Services

#### AddressService
- `createAddress()` : Création d'une nouvelle adresse
- `updateAddress()` : Modification d'une adresse existante
- `setAsDefault()` : Définir une adresse comme par défaut
- `deleteAddress()` : Suppression d'une adresse
- `validateAddressData()` : Validation des données d'adresse

#### OrderService (Modifié)
- Validation obligatoire de tous les champs
- Gestion des adresses de facturation et livraison
- Création d'adresses temporaires si nécessaire
- Validation complète avant création de commande

### Nouveaux Contrôleurs

#### AddressController
- Gestion CRUD des adresses
- API REST pour les adresses client
- Validation des données d'adresse

#### CartController (Modifié)
- Nouvelle route `/checkout` pour le processus de commande
- Validation stricte des champs obligatoires
- Gestion des adresses multiples

## Interface Utilisateur

### Page de Checkout (`/cart/checkout`)
- **Formulaire complet** avec tous les champs obligatoires
- **Validation en temps réel** des champs
- **Gestion des adresses** : sélection ou création
- **Adresse de livraison** : option "identique à la facturation"
- **Messages d'erreur** clairs pour chaque champ

### Validation Frontend
- Vérification obligatoire de tous les champs
- Validation du format email et téléphone
- Validation du code postal (5 chiffres)
- Messages d'erreur contextuels
- Empêche la soumission si des champs sont manquants

### Page de Succès
- Affichage des adresses de facturation et livraison
- Confirmation des informations de commande
- Numéro de commande et détails complets

## Utilisation

### Pour le Client
1. **Remplir tous les champs obligatoires** lors de la commande
2. **Choisir ou créer une adresse** de facturation
3. **Optionnel** : Spécifier une adresse de livraison différente
4. **Validation automatique** avant confirmation
5. **Réutilisation** des adresses pour les prochaines commandes

### Pour l'Administrateur
1. **Visualisation complète** des adresses par commande
2. **Historique des adresses** utilisées
3. **Gestion des clients** et leurs adresses multiples
4. **Facturation précise** avec les bonnes adresses

## Avantages

### Sécurité et Fiabilité
- **Validation stricte** : Aucune commande sans informations complètes
- **Données cohérentes** : Format standardisé pour toutes les adresses
- **Traçabilité** : Conservation de toutes les informations

### Expérience Utilisateur
- **Processus clair** : Étapes bien définies
- **Réutilisation facile** : Adresses sauvegardées
- **Flexibilité** : Adresses différentes pour facturation/livraison
- **Validation immédiate** : Erreurs détectées en temps réel

### Gestion Business
- **Facturation précise** : Adresses correctes sur chaque facture
- **Livraisons fiables** : Adresses de livraison distinctes
- **Historique complet** : Toutes les adresses utilisées conservées
- **Conformité** : Respect des obligations légales de facturation

## Migration

La migration automatique a été effectuée pour :
- Créer la table `address`
- Ajouter les colonnes `billing_address_id` et `shipping_address_id` à la table `order`
- Créer une adresse par défaut pour les commandes existantes
- Maintenir la compatibilité avec les données existantes

## API Endpoints

### Adresses
- `GET /address/customer/{id}` : Liste des adresses d'un client
- `POST /address/create` : Créer une nouvelle adresse
- `PUT /address/update/{id}` : Modifier une adresse
- `POST /address/set-default/{id}` : Définir comme adresse par défaut
- `DELETE /address/delete/{id}` : Supprimer une adresse
- `POST /address/validate` : Valider des données d'adresse

### Commandes
- `GET /cart/checkout` : Page de finalisation de commande
- `POST /cart/create-order` : Créer une commande (avec validation obligatoire)
- `GET /cart/order-success/{id}` : Page de confirmation avec adresses

Cette implémentation garantit que tous les champs obligatoires sont remplis et permet une gestion flexible des adresses multiples avec facturation séparée pour chaque commande.