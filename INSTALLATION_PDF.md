# Installation du système de factures PDF

## 📦 Installation requise

Pour activer le téléchargement des factures en PDF, tu dois installer la bibliothèque dompdf:

```bash
composer require dompdf/dompdf
```

## ✅ Ce qui a été créé

1. **Service PDF** (`src/Service/PdfService.php`)
   - Génération de factures PDF
   - Téléchargement et visualisation

2. **Contrôleur** (`src/Controller/InvoiceController.php`)
   - Route `/invoice/download/{id}` - Télécharger la facture
   - Route `/invoice/view/{id}` - Voir la facture dans le navigateur

3. **Template PDF** (`templates/pdf/invoice.html.twig`)
   - Design professionnel pour impression
   - Toutes les informations de commande

4. **Boutons ajoutés**
   - ✅ Page de succès du paiement
   - ✅ Email de facture
   - ✅ Admin - détails de commande

## 🚀 Utilisation

Après installation de dompdf:

1. **Client** - Après paiement:
   - Bouton "Télécharger la facture PDF" sur la page de succès
   - Lien dans l'email de confirmation

2. **Admin** - Dans les détails de commande:
   - Bouton "Télécharger PDF"
   - Bouton "Voir la facture"

## 🎨 Personnalisation

Pour personnaliser la facture, édite:
- `templates/pdf/invoice.html.twig` - Design et contenu
- Modifie les informations de l'entreprise (adresse, téléphone, etc.)

## ⚠️ Important

Lance la commande d'installation maintenant:
```bash
composer require dompdf/dompdf
```

Puis teste en passant une commande!
