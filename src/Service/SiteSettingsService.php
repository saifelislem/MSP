<?php

namespace App\Service;

use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;

class SiteSettingsService
{
    public function __construct(
        private SiteSettingsRepository $settingsRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Récupère une valeur de paramètre
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settingsRepository->getSettingValue($key, $default);
    }

    /**
     * Définit une valeur de paramètre
     */
    public function set(string $key, mixed $value, string $category = 'general', string $description = '', string $type = 'text'): void
    {
        $this->settingsRepository->setSetting($key, $value, $category, $description, $type);
    }

    /**
     * Initialise les paramètres par défaut du site
     */
    public function initializeDefaultSettings(): void
    {
        $defaults = [
            // Couleurs principales
            'primary_color' => ['#2F4E9B', 'colors', 'Couleur principale du site', 'color'],
            'secondary_color' => ['#8A92AD', 'colors', 'Couleur secondaire', 'color'],
            'accent_color' => ['#DADDEB', 'colors', 'Couleur d\'accent', 'color'],
            'success_color' => ['#28a745', 'colors', 'Couleur de succès', 'color'],
            'warning_color' => ['#ffc107', 'colors', 'Couleur d\'avertissement', 'color'],
            'danger_color' => ['#dc3545', 'colors', 'Couleur de danger', 'color'],

            // Couleurs des boutons
            'btn_primary_bg' => ['#2F4E9B', 'buttons', 'Arrière-plan bouton principal', 'color'],
            'btn_primary_text' => ['#ffffff', 'buttons', 'Texte bouton principal', 'color'],
            'btn_secondary_bg' => ['#8A92AD', 'buttons', 'Arrière-plan bouton secondaire', 'color'],
            'btn_secondary_text' => ['#ffffff', 'buttons', 'Texte bouton secondaire', 'color'],

            // Textes du site
            'site_title' => ['MS Lettres', 'content', 'Titre du site', 'text'],
            'site_description' => ['Enseignes personnalisées de qualité', 'content', 'Description du site', 'textarea'],
            'footer_text' => ['© 2024 MS Lettres - Tous droits réservés', 'content', 'Texte du footer', 'text'],
            'contact_email' => ['contact@mslettres.com', 'content', 'Email de contact', 'text'],
            'contact_phone' => ['+33 1 23 45 67 89', 'content', 'Téléphone de contact', 'text'],

            // Paramètres de la boutique
            'currency_symbol' => ['€', 'shop', 'Symbole de la devise', 'text'],
            'default_product_price' => ['10.00', 'shop', 'Prix par défaut des produits', 'number'],
            'free_shipping_threshold' => ['0', 'shop', 'Seuil de livraison gratuite', 'number'],
            'tax_rate' => ['20', 'shop', 'Taux de TVA (%)', 'number'],

            // Paramètres d'affichage
            'products_per_page' => ['12', 'display', 'Produits par page', 'number'],
            'show_prices' => ['true', 'display', 'Afficher les prix', 'boolean'],
            'show_stock' => ['false', 'display', 'Afficher le stock', 'boolean'],
            'enable_reviews' => ['false', 'display', 'Activer les avis', 'boolean'],

            // Réseaux sociaux
            'facebook_url' => ['', 'social', 'URL Facebook', 'text'],
            'instagram_url' => ['', 'social', 'URL Instagram', 'text'],
            'twitter_url' => ['', 'social', 'URL Twitter', 'text'],
            'linkedin_url' => ['', 'social', 'URL LinkedIn', 'text'],

            // Images
            'logo_url' => ['/images/logo.png', 'images', 'URL du logo', 'text'],
            'favicon_url' => ['/images/icons/favicon.png', 'images', 'URL du favicon', 'text'],
            'banner_image' => ['/images/banner-01.jpg', 'images', 'Image de bannière', 'text'],

            // Maintenance
            'maintenance_mode' => ['false', 'maintenance', 'Mode maintenance', 'boolean'],
            'maintenance_message' => ['Site en maintenance, revenez bientôt !', 'maintenance', 'Message de maintenance', 'textarea'],
        ];

        foreach ($defaults as $key => [$value, $category, $description, $type]) {
            // Ne pas écraser les paramètres existants
            if ($this->get($key) === null) {
                $this->set($key, $value, $category, $description, $type);
            }
        }
    }

    /**
     * Récupère tous les paramètres par catégorie
     */
    public function getByCategory(string $category): array
    {
        return $this->settingsRepository->getSettingsByCategory($category);
    }

    /**
     * Récupère toutes les catégories
     */
    public function getAllCategories(): array
    {
        return $this->settingsRepository->getAllCategories();
    }

    /**
     * Génère le CSS personnalisé basé sur les paramètres
     */
    public function generateCustomCSS(): string
    {
        $css = "/* CSS généré automatiquement depuis les paramètres du site */\n\n";

        // Variables CSS
        $css .= ":root {\n";
        $css .= "  --primary-color: " . $this->get('primary_color', '#2F4E9B') . ";\n";
        $css .= "  --secondary-color: " . $this->get('secondary_color', '#8A92AD') . ";\n";
        $css .= "  --accent-color: " . $this->get('accent_color', '#DADDEB') . ";\n";
        $css .= "  --success-color: " . $this->get('success_color', '#28a745') . ";\n";
        $css .= "  --warning-color: " . $this->get('warning_color', '#ffc107') . ";\n";
        $css .= "  --danger-color: " . $this->get('danger_color', '#dc3545') . ";\n";
        $css .= "}\n\n";

        // Boutons
        $css .= "/* Boutons personnalisés */\n";
        $css .= ".btn-primary, .bg3 {\n";
        $css .= "  background-color: " . $this->get('btn_primary_bg', '#2F4E9B') . " !important;\n";
        $css .= "  color: " . $this->get('btn_primary_text', '#ffffff') . " !important;\n";
        $css .= "  border-color: " . $this->get('btn_primary_bg', '#2F4E9B') . " !important;\n";
        $css .= "}\n\n";

        $css .= ".btn-secondary {\n";
        $css .= "  background-color: " . $this->get('btn_secondary_bg', '#8A92AD') . " !important;\n";
        $css .= "  color: " . $this->get('btn_secondary_text', '#ffffff') . " !important;\n";
        $css .= "  border-color: " . $this->get('btn_secondary_bg', '#8A92AD') . " !important;\n";
        $css .= "}\n\n";

        // Couleurs du thème
        $css .= "/* Couleurs du thème */\n";
        $css .= ".cl2, .mtext-105.cl2, .ltext-109.cl2 {\n";
        $css .= "  color: " . $this->get('primary_color', '#2F4E9B') . " !important;\n";
        $css .= "}\n\n";

        $css .= ".bor13, .bor14 {\n";
        $css .= "  border-color: " . $this->get('accent_color', '#DADDEB') . " !important;\n";
        $css .= "}\n\n";

        return $css;
    }

    /**
     * Sauvegarde le CSS personnalisé dans un fichier
     */
    public function saveCustomCSS(): void
    {
        $css = $this->generateCustomCSS();
        $cssDir = 'public/css';
        $cssPath = $cssDir . '/custom-generated.css';
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($cssDir)) {
            mkdir($cssDir, 0755, true);
        }
        
        file_put_contents($cssPath, $css);
    }
}