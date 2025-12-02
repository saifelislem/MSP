# 🚀 Étapes d'Installation - Back-Office Admin MSP

## ✅ Ce qui a été créé

### 📦 Entités (4)
1. `Modele` - Types de lettres
2. `Order` - Commandes
3. `OrderItem` - Articles de commande
4. `User` - Administrateurs

### 🎮 Controllers (4)
1. `SecurityController` - Login/Logout
2. `AdminDashboardController` - Tableau de bord
3. `AdminOrderController` - Gestion commandes
4. `AdminModeleController` - Gestion modèles

### 🎨 Templates (9)
1. Layout admin
2. Page login
3. Dashboard
4. Liste commandes
5. Détail commande
6. Liste modèles
7. Créer modèle
8. Modifier modèle
9. Front mis à jour

### ⚙️ Commands (2)
1. Créer un admin
2. Initialiser les modèles

---

## 📋 ÉTAPES À SUIVRE MAINTENANT

### ÉTAPE 1: Créer un administrateur
```bash
php bin/console app:create-admin
```
**Répondez aux questions:**
- Email: `admin@msp.com`
- Nom: `Admin MSP`
- Mot de passe: `votre_mot_de_passe_sécurisé`

### ÉTAPE 2: Initialiser les modèles de lettres
```bash
php bin/console app:init-modeles
```
✅ Cela créera 10 modèles de lettres automatiquement

### ÉTAPE 3: Tester la connexion admin
1. Ouvrir: `http://localhost:8000/login`
2. Se connecter avec vos identifiants
3. Vous serez redirigé vers `/admin`

### ÉTAPE 4: Vérifier le front
1. Ouvrir: `http://localhost:8000/`
2. Les modèles doivent s'afficher depuis la base de données
3. Tester la personnalisation d'un modèle

---

## 🎯 URLs Importantes

| Page | URL | Description |
|------|-----|-------------|
| **Site Front** | `http://localhost:8000/` | Page d'accueil client |
| **Login Admin** | `http://localhost:8000/login` | Connexion admin |
| **Dashboard** | `http://localhost:8000/admin` | Tableau de bord |
| **Commandes** | `http://localhost:8000/admin/orders` | Gestion commandes |
| **Modèles** | `http://localhost:8000/admin/modeles` | Gestion modèles |

---

## 📊 Structure Admin

```
/admin
├── Dashboard (statistiques)
├── /orders
│   ├── Liste des commandes
│   ├── Filtres par statut
│   └── Détail commande
└── /modeles
    ├── Liste des modèles
    ├── Créer un modèle
    ├── Modifier un modèle
    └── Activer/Désactiver
```

---

## 🔐 Sécurité

- ✅ Routes `/admin/*` protégées par `ROLE_ADMIN`
- ✅ Login avec CSRF protection
- ✅ Mots de passe hashés
- ✅ Logout sécurisé

---

## 📝 Commandes Utiles

```bash
# Créer un nouvel admin
php bin/console app:create-admin

# Réinitialiser les modèles
php bin/console app:init-modeles

# Voir les routes
php bin/console debug:router

# Vider le cache
php bin/console cache:clear
```

---

## 🎨 Personnalisation

### Ajouter un modèle manuellement:
1. Aller sur `/admin/modeles`
2. Cliquer "Nouveau modèle"
3. Remplir:
   - Nom: "Mon Modèle"
   - Image: `lettres/mon-image.jpg`
   - Prix: `15.00`
   - Description: "Description..."
4. Cocher "Actif"
5. Sauvegarder

### Modifier un modèle:
1. Liste des modèles
2. Cliquer sur l'icône crayon
3. Modifier les champs
4. Sauvegarder

---

## ✅ Checklist de Test

### Front (Client)
- [ ] Page d'accueil affiche les modèles
- [ ] Cliquer "Personnaliser" ouvre le modal
- [ ] Remplir le formulaire et ajouter au panier
- [ ] Confirmer dans le sidebar
- [ ] Voir le panier complet

### Admin
- [ ] Se connecter sur `/login`
- [ ] Dashboard affiche les stats
- [ ] Liste des commandes fonctionne
- [ ] Filtres par statut fonctionnent
- [ ] Détail d'une commande s'affiche
- [ ] Liste des modèles s'affiche
- [ ] Créer un modèle fonctionne
- [ ] Modifier un modèle fonctionne
- [ ] Activer/Désactiver fonctionne

---

## 🐛 Dépannage

### Erreur "Access Denied"
→ Vérifier que l'utilisateur a le rôle `ROLE_ADMIN`

### Modèles ne s'affichent pas
→ Vérifier qu'ils sont "Actifs" dans l'admin

### Images ne s'affichent pas
→ Vérifier que les images sont dans `public/lettres/`

### Erreur de connexion
→ Vider le cache: `php bin/console cache:clear`

---

## 📞 Prochaines Fonctionnalités

### À ajouter:
1. **Validation de commande** depuis le panier
2. **Upload d'images** pour les modèles
3. **Gestion des clients**
4. **Statistiques avancées**
5. **Export des commandes** (PDF, Excel)
6. **Notifications email**

---

**Tout est prêt! Suivez les 4 étapes ci-dessus pour démarrer.** 🚀
