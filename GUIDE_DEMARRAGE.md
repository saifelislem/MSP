# Guide de Démarrage - Site MS Lettres

## 🚀 Démarrer le Site

### Prérequis
- PHP 8.2 ou supérieur installé
- MySQL 8.0 en cours d'exécution
- Composer installé
- Base de données `ms_db` créée

### Commandes de Démarrage

**Option 1 - Serveur PHP intégré (Recommandé):**
```cmd
php -S localhost:8000 -t public
```

**Option 2 - Symfony CLI (si installé):**
```cmd
symfony server:start
```

### URLs d'Accès
- **Site Client:** http://localhost:8000
- **Dashboard Admin:** http://localhost:8000/admin
- **Panier:** http://localhost:8000/panier

### Identifiants Admin
- **Email:** admin@example.com
- **Mot de passe:** admin123

---

## 🔧 Installation Initiale

Si c'est la première fois que vous lancez le projet:

### 1. Installer les dépendances
```cmd
composer install
```

### 2. Configurer la base de données
Vérifiez le fichier `.env` et assurez-vous que la connexion MySQL est correcte:
```
DATABASE_URL="mysql://root:@127.0.0.1:3306/ms_db?serverVersion=8.0"
```

### 3. Créer la base de données
```cmd
php bin/console doctrine:database:create
```

### 4. Importer la structure et les données
```cmd
mysql -u root ms_db < database_complete.sql
```

OU mettre à jour le schéma:
```cmd
php bin/console doctrine:schema:update --force
```

### 5. Vider le cache
```cmd
php bin/console cache:clear
```

---

## ❌ Problèmes Courants et Solutions

### Problème 1: "Le panier ne fonctionne pas"

**Symptômes:**
- Impossible d'ajouter des produits au panier
- Erreurs lors de l'accès à `/panier`
- Le compteur du panier ne se met pas à jour

**Solutions:**

1. **Synchroniser la base de données:**
```cmd
php bin/console doctrine:schema:update --force
php bin/console cache:clear
```

2. **Vérifier que MySQL est démarré:**
- Ouvrez XAMPP/WAMP/MAMP
- Démarrez le service MySQL

3. **Vérifier la connexion à la base:**
```cmd
php bin/console doctrine:schema:validate
```

4. **Vérifier les sessions PHP:**
Assurez-vous que le dossier `var/sessions` existe et est accessible en écriture.

### Problème 2: "No route found for GET /panier"

**Solution:**
La route existe maintenant. Videz le cache:
```cmd
php bin/console cache:clear
```

### Problème 3: "The database schema is not in sync"

**Solution:**
```cmd
php bin/console doctrine:migrations:sync-metadata-storage
php bin/console doctrine:schema:update --force
```

### Problème 4: Erreurs de permissions sur Windows

**Solution:**
Exécutez les commandes en tant qu'administrateur ou vérifiez les permissions du dossier `var/`.

### Problème 5: Le serveur ne démarre pas

**Vérifications:**
1. Port 8000 déjà utilisé? Essayez un autre port:
```cmd
php -S localhost:8080 -t public
```

2. PHP n'est pas dans le PATH? Utilisez le chemin complet:
```cmd
C:\xampp\php\php.exe -S localhost:8000 -t public
```

### Problème 6: Images ne s'affichent pas

**Solution:**
Vérifiez que le dossier `public/uploads` existe et contient les images.

---

## 📊 Vérifications de Santé

### Vérifier la configuration
```cmd
php bin/console about
```

### Vérifier les routes
```cmd
php bin/console debug:router
```

### Vérifier la base de données
```cmd
php bin/console doctrine:schema:validate
```

### Lister les services
```cmd
php bin/console debug:container
```

---

## 🛠️ Commandes Utiles

### Créer un utilisateur admin
```cmd
php bin/console app:create-admin
```

### Vider le cache
```cmd
php bin/console cache:clear
```

### Voir les logs en temps réel
```cmd
tail -f var/log/dev.log
```

### Mettre à jour les assets
```cmd
php bin/console asset-map:compile
```

---

## 📝 Structure du Projet

```
MSP/
├── config/              # Configuration Symfony
├── public/              # Fichiers publics (CSS, JS, images)
├── src/
│   ├── Controller/      # Contrôleurs
│   ├── Entity/          # Entités Doctrine
│   ├── Repository/      # Repositories
│   └── Service/         # Services métier
├── templates/           # Templates Twig
├── var/                 # Cache et logs
├── .env                 # Configuration environnement
└── composer.json        # Dépendances PHP
```

---

## 🎯 Fonctionnalités Principales

### 1. Système de Calcul de Prix Personnalisé
- Formules mathématiques par modèle
- Variables: largeur, hauteur, prixBase, quantite
- Calcul automatique côté serveur

### 2. Gestionnaire de Fichiers
- Upload depuis PC
- Parcourir les fichiers existants
- Intégré dans tous les formulaires admin

### 3. Aperçu 3D Réaliste
- Three.js pour le rendu 3D
- Personnalisation des couleurs
- Rotation et zoom interactifs

### 4. Système de Panier
- Ajout de produits personnalisés
- Calcul automatique des prix
- Gestion des quantités
- Checkout avec adresses multiples

### 5. Dashboard Admin Complet
- Gestion des modèles et catégories
- Gestion des commandes
- Personnalisation du site
- Statistiques et rapports

---

## 🔐 Sécurité

- Authentification par email/mot de passe
- Protection CSRF activée
- Validation des formules de calcul
- Sanitization des uploads

---

## 📞 Support

En cas de problème persistant:
1. Vérifiez les logs dans `var/log/dev.log`
2. Consultez la documentation Symfony: https://symfony.com/doc
3. Vérifiez que toutes les dépendances sont installées: `composer install`

---

## ✅ Checklist de Démarrage Rapide

- [ ] MySQL démarré
- [ ] Base de données `ms_db` créée
- [ ] `composer install` exécuté
- [ ] `php bin/console doctrine:schema:update --force` exécuté
- [ ] `php bin/console cache:clear` exécuté
- [ ] Serveur démarré avec `php -S localhost:8000 -t public`
- [ ] Accès à http://localhost:8000 fonctionne
- [ ] Connexion admin avec admin@example.com / admin123

---

**Dernière mise à jour:** Mars 2026
**Version:** 1.0
