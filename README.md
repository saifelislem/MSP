# MS Lettres - Boutique de Lettres Personnalisées

Application e-commerce Symfony pour la vente de lettres personnalisées.

## 🎨 Design

- Palette de couleurs: Lavande (#DADDEB), Gris bleuté (#8A92AD), Bleu vif (#2F4E9B), Bleu marine (#0F1A3A)
- Interface responsive et moderne
- Thème personnalisé cohérent

## 🚀 Fonctionnalités

- ✅ Catalogue de modèles de lettres
- ✅ Personnalisation des produits (texte, dimensions, police)
- ✅ Panier d'achat avec gestion des quantités
- ✅ Paiement en ligne via Stripe
- ✅ Interface d'administration
- ✅ Gestion des commandes

## 📋 Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 8.0
- Symfony CLI (optionnel)

## 🔧 Installation

1. Cloner le repository
```bash
git clone <votre-repo>
cd MSP
```

2. Installer les dépendances
```bash
composer install
```

3. Configurer l'environnement
```bash
cp .env.example .env
```
Puis éditez `.env` et configurez:
- `DATABASE_URL` avec vos identifiants MySQL
- `STRIPE_PUBLIC_KEY` et `STRIPE_SECRET_KEY` avec vos clés Stripe

4. Créer la base de données
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Initialiser les données
```bash
php bin/console app:init-modeles
php bin/console app:create-admin
```

6. Lancer le serveur
```bash
symfony server:start
```
ou
```bash
php -S localhost:8000 -t public
```

## 🔑 Configuration Stripe

1. Créez un compte sur [Stripe](https://stripe.com)
2. Récupérez vos clés API (mode test)
3. Ajoutez-les dans votre fichier `.env`

### Carte de test Stripe
```
Numéro: 4242 4242 4242 4242
Date: 12/34
CVC: 123
Code postal: 12345
```

## 👤 Compte Admin

Après avoir exécuté `php bin/console app:create-admin`, connectez-vous avec:
- URL: `/login`
- Email: admin@example.com
- Mot de passe: admin123

## 📁 Structure

```
MSP/
├── public/
│   ├── css/
│   │   └── custom-theme.css    # Thème personnalisé
│   ├── images/                 # Images des produits
│   └── js/                     # Scripts JavaScript
├── src/
│   ├── Controller/             # Contrôleurs
│   ├── Entity/                 # Entités Doctrine
│   ├── Repository/             # Repositories
│   ├── Service/                # Services (Cart, Order, Stripe)
│   └── Command/                # Commandes CLI
├── templates/                  # Templates Twig
└── config/                     # Configuration Symfony
```

## 🛠️ Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Lister les commandes
php bin/console list

# Créer un admin
php bin/console app:create-admin

# Initialiser les modèles
php bin/console app:init-modeles

# Lister les commandes
php bin/console app:list-orders
```

## 📝 License

Propriétaire - MS Digital

## 👨‍💻 Auteur

MS Digital - Lettres Personnalisées
