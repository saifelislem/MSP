# 📁 Gestionnaire de Fichiers - Administration

## 🎯 Vue d'ensemble

Le gestionnaire de fichiers permet aux administrateurs de télécharger, organiser et sélectionner des images et autres fichiers pour les utiliser dans les modèles, logos, catégories et autres éléments du site.

## ✨ Fonctionnalités

### 🔧 Interface d'Administration
- **Gestionnaire principal** : `/admin/files/`
- **Upload par catégories** : Logos, Modèles, Général, Site
- **Drag & Drop** : Glisser-déposer des fichiers
- **Aperçu en temps réel** : Visualisation des images
- **Gestion des fichiers** : Supprimer, renommer, organiser

### 🎨 Intégration dans les Formulaires
- **Sélecteur de fichiers** : Bouton "Parcourir" dans tous les formulaires
- **Upload direct** : Bouton "Uploader" pour ajouter de nouveaux fichiers
- **Aperçu instantané** : Prévisualisation des images sélectionnées

## 🏗️ Architecture

### Contrôleurs
- **FileManagerController** : Gestion des fichiers (upload, suppression, listing)
- **API Routes** : `/admin/files/api/list`, `/admin/files/upload`, `/admin/files/delete`

### Frontend
- **file-picker.js** : Composant JavaScript réutilisable
- **Templates** : Interface d'administration et modals de sélection

### Structure des Dossiers
```
public/uploads/
├── logos/          # Logos du site
├── models/         # Images des modèles de lettres
├── general/        # Images des catégories et fichiers généraux
└── site/           # Images du site (bannières, favicon, etc.)
```

## 📋 Intégrations Disponibles

### 🏠 Paramètres du Site (`/admin/settings/appearance/`)
- **Images du Site** : Bannière, logo, favicon
- **Boutons** : Parcourir, Uploader, Aperçu
- **Catégorie** : `logos` pour les logos, `site` pour les autres

### 📦 Modèles de Lettres
- **Création** : `/admin/modeles/new`
- **Modification** : `/admin/modeles/{id}/edit`
- **Catégorie** : `models`
- **Aperçu** : Mise à jour automatique de l'aperçu principal

### 📂 Catégories
- **Création** : `/admin/categories/new`
- **Modification** : `/admin/categories/{id}/edit`
- **Catégorie** : `general`
- **Aperçu** : Prévisualisation instantanée

### 💰 Calcul de Prix
- **Configuration** : `/admin/pricing/`
- **Interface** : Gestion des formules de prix personnalisées

## 📋 Types de Fichiers Supportés

### Images
- **JPG/JPEG** : Photos et images compressées
- **PNG** : Images avec transparence
- **GIF** : Images animées
- **WebP** : Format moderne optimisé
- **SVG** : Images vectorielles

### Limitations
- **Taille maximale** : 5MB par fichier
- **Formats autorisés** : Images uniquement
- **Sécurité** : Validation du type MIME

## 🚀 Utilisation

### Interface d'Administration

1. **Accéder au gestionnaire** : `/admin/files/`
2. **Naviguer par catégories** : Logos, Modèles, Général, Site
3. **Uploader des fichiers** :
   - Cliquer sur "Télécharger des Fichiers"
   - Sélectionner la catégorie
   - Glisser-déposer ou parcourir
   - Cliquer "Télécharger"

### Dans les Formulaires

#### Paramètres du Site
1. **Aller à** : `/admin/settings/appearance/`
2. **Section Images du Site** :
   - **Parcourir** : Sélectionner depuis la galerie
   - **Uploader** : Ajouter un nouveau fichier
   - **Aperçu** : Voir l'image sélectionnée
   - **Supprimer** : Retirer l'image

#### Ajout de Modèle
1. **Aller à** : `/admin/modeles/new`
2. **Champ Image** :
   - **Parcourir** : Sélectionner depuis la galerie
   - **Uploader** : Ajouter un nouveau fichier
   - **Aperçu** : Voir l'image sélectionnée

#### Ajout de Catégorie
1. **Aller à** : `/admin/categories/new`
2. **Champ Image** :
   - **Parcourir** : Sélectionner depuis la galerie
   - **Uploader** : Ajouter un nouveau fichier
   - **Aperçu** : Voir l'image sélectionnée

## 🔧 Intégration Technique

### Utilisation du File Picker

```javascript
// Ouvrir le sélecteur de fichiers
filePicker.open(function(path) {
    // Callback avec le chemin du fichier sélectionné
    document.getElementById('image').value = path;
    showPreview(path);
}, 'models'); // Catégorie
```

### Upload Direct

```javascript
// Upload d'un fichier
const formData = new FormData();
formData.append('file', file);
formData.append('category', 'models');

fetch('/admin/files/upload', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Fichier uploadé:', data.path);
    }
});
```

### Intégration dans un Formulaire

```html
<!-- Champ avec sélecteur de fichiers -->
<div class="input-group">
    <input type="text" class="form-control" id="image" name="image" readonly>
    <button type="button" class="btn btn-outline-secondary" onclick="selectImage()">
        <i class="fa fa-folder-open"></i> Parcourir
    </button>
    <button type="button" class="btn btn-outline-success" onclick="uploadImage()">
        <i class="fa fa-upload"></i> Uploader
    </button>
</div>

<!-- Aperçu -->
<div id="imagePreview" style="display: none;">
    <img id="previewImg" class="img-thumbnail" style="max-width: 200px;">
    <button type="button" class="btn btn-sm btn-danger" onclick="removeImage()">
        <i class="fa fa-times"></i> Supprimer
    </button>
</div>
```

## 🛡️ Sécurité

### Validation des Fichiers
- **Type MIME** : Vérification du type réel du fichier
- **Extension** : Validation de l'extension
- **Taille** : Limite de 5MB par fichier
- **Nom de fichier** : Slugification et ajout d'un ID unique

### Protection
- **Dossier uploads** : Accessible en lecture seule via HTTP
- **Validation côté serveur** : Double vérification des fichiers
- **Noms sécurisés** : Éviter les conflits et injections

## 📊 API Endpoints

### Liste des Fichiers
```http
GET /admin/files/api/list?category=models
```

**Réponse :**
```json
{
  "files": [
    {
      "name": "modele-1.jpg",
      "path": "/uploads/models/modele-1-abc123.jpg",
      "size": 245760,
      "modified": 1640995200,
      "type": "image"
    }
  ]
}
```

### Upload de Fichier
```http
POST /admin/files/upload
Content-Type: multipart/form-data

file: [binary data]
category: models
```

**Réponse :**
```json
{
  "success": true,
  "filename": "modele-nouveau-def456.jpg",
  "path": "/uploads/models/modele-nouveau-def456.jpg",
  "url": "http://localhost:8000/uploads/models/modele-nouveau-def456.jpg"
}
```

### Suppression de Fichier
```http
POST /admin/files/delete

path: /uploads/models/modele-ancien.jpg
```

## 🎨 Interface Utilisateur

### Gestionnaire Principal
- **Onglets par catégorie** : Organisation claire
- **Grille d'images** : Aperçu visuel
- **Actions rapides** : Sélectionner, supprimer
- **Upload par drag & drop** : Interface intuitive

### Intégration Formulaires
- **Boutons intégrés** : Parcourir / Uploader dans tous les formulaires
- **Aperçu instantané** : Voir l'image sélectionnée
- **Suppression facile** : Bouton de suppression
- **Notifications** : Messages de succès/erreur

## 🔄 Workflow Complet

### Configuration des Images du Site

1. **Aller aux paramètres** : `/admin/settings/appearance/`
2. **Section Images du Site** : Voir les champs disponibles
3. **Sélectionner une image** :
   - **Option A** : Cliquer "Parcourir" → Sélectionner depuis la galerie
   - **Option B** : Cliquer "Uploader" → Ajouter une nouvelle image
4. **Vérifier l'aperçu** : S'assurer que l'image est correcte
5. **Sauvegarder** : Les modifications sont appliquées automatiquement

### Ajout d'un Nouveau Modèle

1. **Préparer l'image** : Avoir l'image du modèle prête
2. **Aller au formulaire** : `/admin/modeles/new`
3. **Remplir les informations** : Nom, description, prix
4. **Sélectionner l'image** :
   - **Option A** : Cliquer "Parcourir" → Sélectionner depuis la galerie
   - **Option B** : Cliquer "Uploader" → Ajouter une nouvelle image
5. **Vérifier l'aperçu** : S'assurer que l'image est correcte
6. **Sauvegarder** : Le modèle est créé avec l'image

### Gestion des Fichiers

1. **Accéder au gestionnaire** : `/admin/files/`
2. **Organiser par catégories** : Logos, Modèles, Général, Site
3. **Uploader en lot** : Plusieurs fichiers à la fois
4. **Nettoyer régulièrement** : Supprimer les fichiers inutilisés

## 📈 Évolutions Futures

- **Redimensionnement automatique** : Optimisation des images
- **Formats additionnels** : Support de plus de types de fichiers
- **Dossiers personnalisés** : Création de sous-catégories
- **Métadonnées** : Tags, descriptions, recherche
- **Historique** : Suivi des modifications

---

**✅ Le gestionnaire de fichiers est maintenant complètement intégré !**

### 🎯 Fonctionnalités Disponibles :

1. **Gestionnaire principal** : `/admin/files/`
2. **Paramètres du site** : Upload d'images pour bannière, logo, favicon
3. **Modèles de lettres** : Sélection/upload d'images pour les modèles
4. **Catégories** : Sélection/upload d'images pour les catégories
5. **Calcul de prix** : Configuration des formules de prix personnalisées

Pour tester :
1. Démarrer le serveur : `php -S localhost:8000 -t public`
2. Aller sur `/admin/settings/appearance/` pour tester les images du site
3. Tester l'ajout de modèle avec sélection d'image : `/admin/modeles/new`
4. Tester l'ajout de catégorie avec sélection d'image : `/admin/categories/new`