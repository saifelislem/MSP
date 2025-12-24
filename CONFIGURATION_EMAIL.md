# Configuration de l'envoi d'emails

## 📧 Système d'emails automatiques

Le système envoie automatiquement des emails dans les cas suivants:
- ✅ **Facture au client** après paiement confirmé
- 🔔 **Notification admin** pour nouvelle commande (optionnel)

## 🔧 Configuration

### Option 1: Gmail (Recommandé pour test)

1. Créer un mot de passe d'application Gmail:
   - Aller sur https://myaccount.google.com/security
   - Activer la validation en 2 étapes
   - Créer un mot de passe d'application

2. Modifier `.env`:
```env
MAILER_DSN=gmail+smtp://votre-email@gmail.com:votre-mot-de-passe-app@default
```

### Option 2: Mailtrap (Test sans envoi réel)

1. Créer un compte sur https://mailtrap.io
2. Copier les identifiants SMTP
3. Modifier `.env`:
```env
MAILER_DSN=smtp://username:password@smtp.mailtrap.io:2525
```

### Option 3: SMTP personnalisé

```env
MAILER_DSN=smtp://user:password@smtp.example.com:587
```

### Option 4: Mode développement (pas d'envoi)

```env
MAILER_DSN=null://null
```
Les emails seront loggés dans `var/log/dev.log` mais pas envoyés.

## 📝 Personnalisation

### Modifier l'expéditeur

Éditer `config/services.yaml`:
```yaml
parameters:
    app.email.from: 'contact@votre-domaine.com'
    app.email.from_name: 'Votre Entreprise'
    app.email.admin: 'admin@votre-domaine.com'
```

### Activer la notification admin

Dans `src/Controller/PaymentController.php`, décommenter la ligne:
```php
$emailService->sendNewOrderNotification($order, 'admin@enseignes.com');
```

## 📄 Templates d'emails

Les templates sont dans `templates/emails/`:
- `invoice.html.twig` - Facture client
- `new_order_admin.html.twig` - Notification admin

Vous pouvez les personnaliser selon vos besoins.

## 🧪 Test

Pour tester l'envoi d'email:

1. Configurer le MAILER_DSN
2. Passer une commande test
3. Vérifier les logs: `var/log/dev.log`
4. Vérifier la réception de l'email

## ⚠️ Important

- En production, utilisez un service SMTP professionnel (SendGrid, Mailgun, etc.)
- Ne commitez jamais vos identifiants SMTP dans Git
- Utilisez `.env.local` pour les configurations locales
