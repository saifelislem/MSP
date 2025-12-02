# ✅ Vérification Admin - Affichage des Commandes

## 📋 Checklist Complète

### 1. Vérifier les Commandes en Base de Données

```bash
php bin/console app:list-orders
```

**Résultat attendu:**
- Liste de toutes les commandes
- Avec ID, numéro, client, total, statut

**Actuellement:** ✅ 2 commandes trouvées

---

### 2. Se Connecter à l'Admin

**URL:** `http://localhost:8000/login`

**Identifiants:**
- Email: `admin@msp.com`
- Mot de passe: `admin123`

**Vérification:**
- [ ] Page de login s'affiche
- [ ] Connexion réussie
- [ ] Redirection vers `/admin`

---

### 3. Accéder à la Liste des Commandes

**URL:** `http://localhost:8000/admin/orders`

**Ce que vous devriez voir:**

```
📦 Gestion des Commandes
[Dernière mise à jour: il y a 0s] [🔄 Actualiser]

Filtres: [Toutes] [En attente] [En cours] [Terminées] [Annulées]

┌─────────────────────────────────────────────────────────┐
│ N° Commande         │ Date      │ Client      │ Total   │
├─────────────────────────────────────────────────────────┤
│ CMD-20251126-7DAC96 │ 26/11 ... │ Client Test │ 25.50€  │
│ CMD-20251126-E8B07F │ 26/11 ... │ Client Test │ 25.50€  │
└─────────────────────────────────────────────────────────┘
```

---

### 4. Si les Commandes ne S'affichent Pas

#### A. Vider le Cache
```bash
php bin/console cache:clear
```

#### B. Vérifier les Logs
```bash
tail -f var/log/dev.log
```

#### C. Tester la Route Directement
```bash
# Ouvrir dans le navigateur:
http://localhost:8000/admin/orders
```

#### D. Vérifier la Session
- Déconnectez-vous: `/logout`
- Reconnectez-vous: `/login`
- Retournez sur: `/admin/orders`

---

### 5. Créer une Nouvelle Commande de Test

```bash
php bin/console app:create-test-order
```

**Puis:**
1. Allez sur `/admin/orders`
2. Cliquez "🔄 Actualiser"
3. ✅ La nouvelle commande apparaît

---

### 6. Test Complet Client → Admin

**Étape 1 - Client:**
```
1. Aller sur http://localhost:8000/
2. Cliquer "Personnaliser" sur un modèle
3. Remplir et ajouter au panier
4. Confirmer dans le sidebar
5. Aller sur /cart
6. Cliquer "Passer commande"
7. Remplir le formulaire
8. Confirmer
```

**Étape 2 - Admin:**
```
1. Aller sur http://localhost:8000/admin/orders
2. Attendre 30 secondes (auto-refresh)
   OU cliquer "🔄 Actualiser"
3. ✅ La nouvelle commande apparaît!
```

---

## 🐛 Problèmes Courants

### Problème 1: "Access Denied"
**Solution:** Vérifier que vous êtes connecté avec le bon compte admin

### Problème 2: Page Blanche
**Solution:** 
```bash
php bin/console cache:clear
tail -f var/log/dev.log
```

### Problème 3: Commandes Vides
**Solution:**
```bash
# Vérifier la base de données
php bin/console app:list-orders

# Créer une commande de test
php bin/console app:create-test-order
```

### Problème 4: Auto-Refresh ne Fonctionne Pas
**Solution:** Cliquer manuellement sur "🔄 Actualiser"

---

## ✅ Résultat Attendu

Quand tout fonctionne:

1. **Dashboard** (`/admin`)
   - Total commandes: 2+
   - En attente: X
   - Revenu total: XX€

2. **Liste Commandes** (`/admin/orders`)
   - Toutes les commandes affichées
   - Filtres fonctionnels
   - Auto-refresh actif

3. **Détail Commande** (`/admin/orders/{id}`)
   - Informations complètes
   - Articles avec images
   - Possibilité de changer le statut

---

## 🎯 Actions Immédiates

1. **Connectez-vous:**
   ```
   http://localhost:8000/login
   admin@msp.com / admin123
   ```

2. **Allez sur:**
   ```
   http://localhost:8000/admin/orders
   ```

3. **Vous devriez voir:**
   - 2 commandes minimum
   - CMD-20251126-7DAC96
   - CMD-20251126-E8B07F

Si vous ne voyez rien, suivez les étapes de dépannage ci-dessus!
