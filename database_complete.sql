-- =====================================================
-- Base de Données MSP - Système de Lettres Personnalisées
-- Date: 24 Décembre 2025
-- Version: 1.0
-- =====================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `ms_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ms_db`;

-- =====================================================
-- STRUCTURE DES TABLES
-- =====================================================

-- Table des clients
CREATE TABLE customer (
    id INT AUTO_INCREMENT NOT NULL, 
    prenom VARCHAR(100) NOT NULL, 
    nom VARCHAR(100) NOT NULL, 
    email VARCHAR(180) NOT NULL, 
    telephone VARCHAR(20) NOT NULL, 
    adresse VARCHAR(255) DEFAULT NULL, 
    ville VARCHAR(100) DEFAULT NULL, 
    code_postal VARCHAR(20) DEFAULT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    UNIQUE INDEX UNIQ_81398E09E7927C74 (email), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des adresses
CREATE TABLE address (
    id INT AUTO_INCREMENT NOT NULL, 
    customer_id INT DEFAULT NULL, 
    name VARCHAR(255) NOT NULL, 
    street VARCHAR(255) NOT NULL, 
    postal_code VARCHAR(10) NOT NULL, 
    city VARCHAR(100) NOT NULL, 
    country VARCHAR(100) NOT NULL, 
    company VARCHAR(255) DEFAULT NULL, 
    additional_info VARCHAR(255) DEFAULT NULL, 
    is_default TINYINT(1) NOT NULL, 
    INDEX IDX_D4E6F819395C3F3 (customer_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des catégories
CREATE TABLE category (
    id INT AUTO_INCREMENT NOT NULL, 
    nom VARCHAR(100) NOT NULL, 
    slug VARCHAR(100) NOT NULL, 
    description LONGTEXT DEFAULT NULL, 
    image VARCHAR(255) DEFAULT NULL, 
    ordre INT NOT NULL, 
    actif TINYINT(1) NOT NULL, 
    supports_logo TINYINT(1) NOT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    UNIQUE INDEX UNIQ_64C19C1989D9B62 (slug), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des modèles (avec formule de calcul)
CREATE TABLE modele (
    id INT AUTO_INCREMENT NOT NULL, 
    category_id INT DEFAULT NULL, 
    nom VARCHAR(255) NOT NULL, 
    description LONGTEXT DEFAULT NULL, 
    image VARCHAR(255) NOT NULL, 
    prix_base DOUBLE PRECISION NOT NULL, 
    actif TINYINT(1) NOT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    formule_calcul LONGTEXT DEFAULT NULL, 
    INDEX IDX_1002855812469DE2 (category_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des commandes
CREATE TABLE `order` (
    id INT AUTO_INCREMENT NOT NULL, 
    customer_id INT DEFAULT NULL, 
    billing_address_id INT DEFAULT NULL, 
    shipping_address_id INT DEFAULT NULL, 
    order_number VARCHAR(50) NOT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    status VARCHAR(50) NOT NULL, 
    total DOUBLE PRECISION NOT NULL, 
    customer_name VARCHAR(255) NOT NULL, 
    customer_email VARCHAR(255) NOT NULL, 
    customer_phone VARCHAR(50) NOT NULL, 
    notes LONGTEXT DEFAULT NULL, 
    UNIQUE INDEX UNIQ_F5299398551F0F81 (order_number), 
    INDEX IDX_F52993989395C3F3 (customer_id), 
    INDEX IDX_F529939879D0C0E4 (billing_address_id), 
    INDEX IDX_F52993984D4CFF2B (shipping_address_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des produits personnalisés
CREATE TABLE product (
    id INT AUTO_INCREMENT NOT NULL, 
    text VARCHAR(255) DEFAULT NULL, 
    largeur INT DEFAULT NULL, 
    hauteur INT DEFAULT NULL, 
    type_ecriture VARCHAR(255) DEFAULT NULL, 
    image_url VARCHAR(255) DEFAULT NULL, 
    modele_name VARCHAR(255) DEFAULT NULL, 
    mode VARCHAR(10) DEFAULT NULL, 
    logo_data LONGTEXT DEFAULT NULL, 
    logo_file_name VARCHAR(255) DEFAULT NULL, 
    logo_ratio DOUBLE PRECISION DEFAULT NULL, 
    facade_color VARCHAR(7) DEFAULT NULL, 
    side_color VARCHAR(7) DEFAULT NULL, 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des couleurs
CREATE TABLE color (
    id INT AUTO_INCREMENT NOT NULL, 
    name VARCHAR(100) NOT NULL, 
    hex_code VARCHAR(7) NOT NULL, 
    emoji VARCHAR(10) NOT NULL, 
    stock INT NOT NULL, 
    min_stock INT NOT NULL, 
    is_active TINYINT(1) NOT NULL, 
    type VARCHAR(20) NOT NULL, 
    sort_order INT NOT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des paniers
CREATE TABLE cart (
    id INT AUTO_INCREMENT NOT NULL, 
    session_id VARCHAR(255) NOT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des articles du panier
CREATE TABLE cart_item (
    id INT AUTO_INCREMENT NOT NULL, 
    cart_id INT NOT NULL, 
    product_id INT NOT NULL, 
    quantity INT NOT NULL, 
    price DOUBLE PRECISION NOT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    INDEX IDX_F0FE25271AD5CDBF (cart_id), 
    INDEX IDX_F0FE25274584665A (product_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des articles de commande
CREATE TABLE order_item (
    id INT AUTO_INCREMENT NOT NULL, 
    order_id INT NOT NULL, 
    product_id INT NOT NULL, 
    quantity INT NOT NULL, 
    price DOUBLE PRECISION NOT NULL, 
    INDEX IDX_52EA1F098D9F6D38 (order_id), 
    INDEX IDX_52EA1F094584665A (product_id), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des paramètres du site
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT NOT NULL, 
    setting_key VARCHAR(100) NOT NULL, 
    setting_value LONGTEXT DEFAULT NULL, 
    category VARCHAR(100) DEFAULT NULL, 
    description VARCHAR(255) DEFAULT NULL, 
    type VARCHAR(50) DEFAULT NULL, 
    updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    UNIQUE INDEX UNIQ_E9081F1F5FA1E697 (setting_key), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des utilisateurs admin
CREATE TABLE `user` (
    id INT AUTO_INCREMENT NOT NULL, 
    email VARCHAR(180) NOT NULL, 
    roles JSON NOT NULL, 
    password VARCHAR(255) NOT NULL, 
    name VARCHAR(255) NOT NULL, 
    UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Table des messages (Symfony Messenger)
CREATE TABLE messenger_messages (
    id BIGINT AUTO_INCREMENT NOT NULL, 
    body LONGTEXT NOT NULL, 
    headers LONGTEXT NOT NULL, 
    queue_name VARCHAR(190) NOT NULL, 
    created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', 
    delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', 
    INDEX IDX_75EA56E0FB7336F0 (queue_name), 
    INDEX IDX_75EA56E0E3BD61CE (available_at), 
    INDEX IDX_75EA56E016BA31DB (delivered_at), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- =====================================================
-- CONTRAINTES DE CLÉS ÉTRANGÈRES
-- =====================================================

ALTER TABLE address ADD CONSTRAINT FK_D4E6F819395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id);
ALTER TABLE modele ADD CONSTRAINT FK_1002855812469DE2 FOREIGN KEY (category_id) REFERENCES category (id);
ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id);
ALTER TABLE `order` ADD CONSTRAINT FK_F529939879D0C0E4 FOREIGN KEY (billing_address_id) REFERENCES address (id);
ALTER TABLE `order` ADD CONSTRAINT FK_F52993984D4CFF2B FOREIGN KEY (shipping_address_id) REFERENCES address (id);
ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id);
ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product (id);
ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id);
ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id);

-- =====================================================
-- DONNÉES D'EXEMPLE
-- =====================================================

-- Utilisateur admin par défaut
INSERT INTO `user` (email, roles, password, name) VALUES 
('admin@msp.com', '["ROLE_ADMIN"]', '$2y$13$example_hashed_password', 'Administrateur MSP');

-- Catégories d'exemple
INSERT INTO category (nom, slug, description, ordre, actif, supports_logo, created_at) VALUES 
('Lettres 3D', 'lettres-3d', 'Lettres en relief avec effet 3D', 1, 1, 1, NOW()),
('Enseignes Lumineuses', 'enseignes-lumineuses', 'Enseignes avec éclairage LED', 2, 1, 1, NOW()),
('Plaques Professionnelles', 'plaques-professionnelles', 'Plaques pour bureaux et entreprises', 3, 1, 0, NOW());

-- Modèles d'exemple avec formules de calcul
INSERT INTO modele (category_id, nom, description, image, prix_base, actif, formule_calcul, created_at) VALUES 
(1, 'Lettres Acier Inoxydable', 'Lettres 3D en acier inoxydable brossé', '/images/models/acier-inox.jpg', 15.00, 1, '(largeur * hauteur * 0.75) + prixBase', NOW()),
(1, 'Lettres Aluminium', 'Lettres 3D en aluminium anodisé', '/images/models/aluminium.jpg', 12.00, 1, '(largeur * hauteur * 0.60) + (prixBase * quantite)', NOW()),
(2, 'Enseigne LED Standard', 'Enseigne lumineuse avec LED blanches', '/images/models/led-standard.jpg', 25.00, 1, '((largeur * hauteur) / 100) * 3.5 + prixBase', NOW()),
(3, 'Plaque Professionnelle', 'Plaque gravée pour professions libérales', '/images/models/plaque-pro.jpg', 8.00, 1, 'prixBase + (largeur * 0.5)', NOW());

-- Couleurs disponibles
INSERT INTO color (name, hex_code, emoji, stock, min_stock, is_active, type, sort_order, created_at) VALUES 
('Noir', '#000000', '⚫', 100, 10, 1, 'facade', 1, NOW()),
('Blanc', '#FFFFFF', '⚪', 100, 10, 1, 'facade', 2, NOW()),
('Rouge', '#E74C3C', '🔴', 50, 5, 1, 'facade', 3, NOW()),
('Bleu', '#3498DB', '🔵', 50, 5, 1, 'facade', 4, NOW()),
('Or', '#FFD700', '🟡', 30, 3, 1, 'facade', 5, NOW()),
('Argent', '#C0C0C0', '⚪', 40, 4, 1, 'facade', 6, NOW());

-- Paramètres du site
INSERT INTO site_settings (setting_key, setting_value, category, description, type, updated_at) VALUES 
('site_title', 'MSP Lettres', 'content', 'Titre du site', 'text', NOW()),
('site_description', 'Spécialiste en lettres personnalisées et enseignes', 'content', 'Description du site', 'textarea', NOW()),
('primary_color', '#2F4E9B', 'colors', 'Couleur primaire', 'color', NOW()),
('secondary_color', '#8A92AD', 'colors', 'Couleur secondaire', 'color', NOW()),
('currency_symbol', '€', 'shop', 'Symbole de la devise', 'text', NOW()),
('default_product_price', '10.00', 'shop', 'Prix par défaut', 'number', NOW()),
('contact_email', 'contact@msp.com', 'content', 'Email de contact', 'email', NOW()),
('contact_phone', '+33 1 23 45 67 89', 'content', 'Téléphone de contact', 'text', NOW());

-- =====================================================
-- NOTES D'UTILISATION
-- =====================================================

/*
FONCTIONNALITÉS PRINCIPALES:

1. SYSTÈME DE CALCUL DE PRIX PERSONNALISÉ
   - Chaque modèle peut avoir sa propre formule de calcul
   - Variables disponibles: largeur, hauteur, prixBase, quantite
   - Exemples de formules:
     * Simple: prixBase * quantite
     * Avec dimensions: (largeur * hauteur * 0.75) + prixBase
     * Complexe: ((largeur * hauteur) / 100) * 2.5 + (prixBase * quantite)

2. GESTION DES CATÉGORIES
   - Support logo configurable par catégorie
   - Ordre d'affichage personnalisable
   - Slug automatique pour URLs

3. SYSTÈME DE COMMANDES COMPLET
   - Gestion des adresses multiples
   - Statuts de commande
   - Numéros de commande uniques

4. PERSONNALISATION DU SITE
   - Paramètres configurables via interface admin
   - Couleurs personnalisables
   - Contenu modifiable

5. GESTION DES COULEURS
   - Stock et seuils d'alerte
   - Types de couleurs (façade, côtés)
   - Emojis pour interface utilisateur

UTILISATION:
1. Importer ce fichier SQL dans votre base de données MySQL
2. Configurer les paramètres de connexion dans .env
3. Lancer l'application Symfony
4. Se connecter en admin avec admin@msp.com
5. Personnaliser les paramètres via l'interface admin

SÉCURITÉ:
- Changer le mot de passe admin par défaut
- Configurer les clés Stripe pour les paiements
- Vérifier les paramètres SMTP pour les emails
*/