# 🎯 Guide Complet - Back-Office Admin MSP

## 📦 Fichiers Créés

### Entités
- ✅ `src/Entity/Modele.php` - Type de lettre (nom, image, prix, actif)
- ✅ `src/Entity/Order.php` - Commande (numéro, date, statut, total, client)
- ✅ `src/Entity/OrderItem.php` - Article de commande
- ✅ `src/Entity/User.php` - Utilisateur admin

### Repositories
- ✅ `src/Repository/ModeleRepository.php`
- ✅ `src/Repository/OrderRepository.php`
- ✅ `src/Repository/OrderItemRepository.php`
- ✅ `src/Repository/UserRepository.php`

### Controllers
- ✅ `src/Controller/SecurityController.php` - Login/Logout
- ✅ `src/Controller/Admin/AdminDashboardController.php` - Tableau de bord
- ✅ `src/Controller/Admin/AdminOrderController.php` - Gestion commandes
- ✅ `src/Controller/Admin/AdminModeleController.php` - Gestion modèles

### Templates Admin
- ✅ `templates/admin/base.html.twig` - Layout admin
- ✅ `templates/security/login.html.twig` - Page de connexion
- ✅ `templates/admin/dashboard/index.html.twig` - Tableau de bord
- ✅ `templates/admin/orders/index.html.twig` - Liste commandes
- ✅ `templates/admin/orders/show.html.twig` - Détail commande
- ✅ `templates/admin/modeles/index.html.twig` - Liste modèles
- ✅ `templates/admin/modeles/new.html.twig` - Créer modèle
- ✅ `templates/admin/modeles/edit.html.twig` - Modifier modèle

### Commands
- ✅ `src/Command/CreateAdminCommand.php` - Créer un admin
- ✅ `src/Command/InitModelesCommand.php` - Initialiser les modèles

### Configuration
- ✅ `config/packages/security.yaml` - Configuration sécurité

---

## 🚀 Installation et Configuration

### 1. Créer un administrateur
```bash
php bin/console app:create-admin
```
**Informations demandées:**
- Email: `admin@msp.com` (ou votre email)
- Nom: `Admin MSP`
- Mot de passe: (votre mot de passe sécurisé)

### 2. Initialiser les modèles de lettres
```bash
php bin/console app:init-modeles
```
Cela créera 10 modèles de lettres par défaut.

---

## 🔐 Accès Admin

### URL de connexion
```
http://localhost:8000/login
```

### Identifiants par défaut
- **Email:** admin@msp.com
- **Mot de passe:** (celui que vous avez défini)

---

## 📊 Fonctionnalités Admin

### 1. Tableau de Bord (`/admin`)
- **Statistiques:**
  - Total des commandes
  - Commandes en attente
  - Commandes terminées
  - Revenu total
  - Modèles actifs
  - Produits créés
- **Commandes récentes** (5 dernières)

### 2. Gestion des Commandes (`/admin/orders`)
- **Liste complète** avec filtres par statut:
  - Toutes
  - En attente
  - En cours
  - Terminées
  - Annulées
- **Détail d'une commande:**
  - Informations client
  - Liste des articles avec images
  - Changer le statut
  - Supprimer la commande

### 3. Gestion des Modèles (`/admin/modeles`)
- **Liste des modèles** avec:
  - Image
  - Nom
  - Description
  - Prix de base
  - Statut (Actif/Inactif)
- **Actions:**
  - Créer un nouveau modèle
  - Modifier un modèle
  - Activer/Désactiver
  - Supprimer

---

## 🎨 Ajouter un Nouveau Modèle

### Via l'interface admin:
1. Aller sur `/admin/modeles`
2. Cliquer sur "Nouveau modèle"
3. Remplir le formulaire:
   - **Nom:** Ex: "Lettres LED RGB"
   - **Description:** Description du modèle
   - **Image:** Chemin relatif (ex: `lettres/mon-modele.jpg`)
   - **Prix de base:** Prix en euros
   - **Actif:** Cocher pour afficher sur le site
4. Cliquer sur "Créer le modèle"

### Important:
- Placez l'image dans `public/lettres/`
- Format recommandé: JPG ou PNG
- Taille recommandée: 800x600px

---

## 📦 Statuts des Commandes

| Statut | Description | Badge |
|--------|-------------|-------|
| `pending` | En attente | 🟡 Jaune |
| `processing` | En cours de traitement | 🔵 Bleu |
| `completed` | Terminée | 🟢 Vert |
| `cancelled` | Annulée | 🔴 Rouge |

---

## 🔄 Workflow Commande

1. **Client crée un produit** → Produit en attente dans le panier
2. **Client confirme** → Produit ajouté au panier
3. **Client valide le panier** → Commande créée (statut: pending)
4. **Admin traite** → Change statut à "processing"
5. **Admin termine** → Change statut à "completed"

---

## 🛠️ Routes Admin

| Route | URL | Description |
|-------|-----|-------------|
| Login | `/login` | Page de connexion |
| Dashboard | `/admin` | Tableau de bord |
| Commandes | `/admin/orders` | Liste des commandes |
| Détail commande | `/admin/orders/{id}` | Voir une commande |
| Modèles | `/admin/modeles` | Liste des modèles |
| Nouveau modèle | `/admin/modeles/new` | Créer un modèle |
| Modifier modèle | `/admin/modeles/{id}/edit` | Modifier un modèle |
| Logout | `/logout` | Déconnexion |

---

## 🎯 Prochaines Étapes

### Pour créer une commande depuis le panier:
Il faudra ajouter:
1. Un bouton "Valider la commande" dans le panier
2. Un formulaire pour les infos client (nom, email, téléphone)
3. Une route pour créer la commande depuis le panier

### Pour gérer les images:
1. Installer VichUploaderBundle pour l'upload d'images
2. Ou utiliser un système d'upload manuel

---

## 📞 Support

Pour toute question ou problème:
1. Vérifier les logs: `var/log/dev.log`
2. Vérifier la console du navigateur (F12)
3. Vérifier que la base de données est à jour

---

## ✅ Checklist de Vérification

- [ ] Base de données mise à jour
- [ ] Admin créé avec `app:create-admin`
- [ ] Modèles initialisés avec `app:init-modeles`
- [ ] Connexion admin fonctionnelle
- [ ] Dashboard affiche les statistiques
- [ ] Modèles visibles sur le site front
- [ ] Création/modification de modèles fonctionne

---

**Système créé le:** {{ "now"|date("d/m/Y") }}
**Version:** 1.0
