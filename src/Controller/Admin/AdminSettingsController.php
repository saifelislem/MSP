<?php

namespace App\Controller\Admin;

use App\Service\SiteSettingsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/settings')]
#[IsGranted('ROLE_ADMIN')]
class AdminSettingsController extends AbstractController
{
    public function __construct(
        private SiteSettingsService $settingsService
    ) {}

    #[Route('/', name: 'admin_settings_index')]
    public function index(): Response
    {
        // Initialiser les paramètres par défaut si nécessaire
        $this->settingsService->initializeDefaultSettings();

        $categories = $this->settingsService->getAllCategories();
        $settingsByCategory = [];

        foreach ($categories as $category) {
            $settingsByCategory[$category] = $this->settingsService->getByCategory($category);
        }

        return $this->render('admin/settings/index.html.twig', [
            'categories' => $categories,
            'settingsByCategory' => $settingsByCategory,
        ]);
    }

    #[Route('/update', name: 'admin_settings_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $settings = $request->request->all();

        foreach ($settings as $key => $value) {
            if ($key !== '_token') {
                // Récupérer le paramètre existant pour conserver le type et la catégorie
                $existingSetting = $this->settingsService->getByCategory('colors')[0] ?? null;
                
                // Déterminer la catégorie et le type basés sur la clé
                [$category, $type] = $this->getCategoryAndType($key);
                
                $this->settingsService->set($key, $value, $category, '', $type);
            }
        }

        // Régénérer le CSS personnalisé
        $this->settingsService->saveCustomCSS();

        $this->addFlash('success', 'Paramètres mis à jour avec succès !');

        return $this->redirectToRoute('admin_settings_index');
    }

    #[Route('/colors', name: 'admin_settings_colors')]
    public function colors(): Response
    {
        $colorSettings = $this->settingsService->getByCategory('colors');
        $buttonSettings = $this->settingsService->getByCategory('buttons');

        return $this->render('admin/settings/colors.html.twig', [
            'colorSettings' => $colorSettings,
            'buttonSettings' => $buttonSettings,
        ]);
    }

    #[Route('/content', name: 'admin_settings_content')]
    public function content(): Response
    {
        $contentSettings = $this->settingsService->getByCategory('content');

        return $this->render('admin/settings/content.html.twig', [
            'contentSettings' => $contentSettings,
        ]);
    }

    #[Route('/shop', name: 'admin_settings_shop')]
    public function shop(): Response
    {
        $shopSettings = $this->settingsService->getByCategory('shop');
        $displaySettings = $this->settingsService->getByCategory('display');

        return $this->render('admin/settings/shop.html.twig', [
            'shopSettings' => $shopSettings,
            'displaySettings' => $displaySettings,
        ]);
    }

    #[Route('/appearance', name: 'admin_settings_appearance')]
    public function appearance(): Response
    {
        $colorSettings = $this->settingsService->getByCategory('colors');
        $buttonSettings = $this->settingsService->getByCategory('buttons');
        $imageSettings = $this->settingsService->getByCategory('images');

        return $this->render('admin/settings/appearance.html.twig', [
            'colorSettings' => $colorSettings,
            'buttonSettings' => $buttonSettings,
            'imageSettings' => $imageSettings,
        ]);
    }

    #[Route('/general', name: 'admin_settings_general')]
    public function general(): Response
    {
        $contentSettings = $this->settingsService->getByCategory('content');
        $shopSettings = $this->settingsService->getByCategory('shop');
        $displaySettings = $this->settingsService->getByCategory('display');

        return $this->render('admin/settings/general.html.twig', [
            'contentSettings' => $contentSettings,
            'shopSettings' => $shopSettings,
            'displaySettings' => $displaySettings,
        ]);
    }

    #[Route('/preview-css', name: 'admin_settings_preview_css')]
    public function previewCSS(): Response
    {
        $css = $this->settingsService->generateCustomCSS();

        return new Response($css, 200, [
            'Content-Type' => 'text/css',
        ]);
    }

    #[Route('/reset-defaults', name: 'admin_settings_reset_defaults', methods: ['POST'])]
    public function resetDefaults(): Response
    {
        // Réinitialiser tous les paramètres aux valeurs par défaut
        $this->settingsService->initializeDefaultSettings();
        $this->settingsService->saveCustomCSS();

        $this->addFlash('success', 'Paramètres réinitialisés aux valeurs par défaut !');

        return $this->redirectToRoute('admin_settings_index');
    }

    /**
     * Détermine la catégorie et le type d'un paramètre basé sur sa clé
     */
    private function getCategoryAndType(string $key): array
    {
        // Couleurs
        if (str_contains($key, 'color')) {
            return ['colors', 'color'];
        }

        // Boutons
        if (str_contains($key, 'btn_')) {
            return ['buttons', 'color'];
        }

        // Contenu
        if (in_array($key, ['site_title', 'site_description', 'footer_text', 'contact_email', 'contact_phone'])) {
            return ['content', str_contains($key, 'description') ? 'textarea' : 'text'];
        }

        // Boutique
        if (in_array($key, ['currency_symbol', 'default_product_price', 'free_shipping_threshold', 'tax_rate'])) {
            return ['shop', str_contains($key, 'price') || str_contains($key, 'threshold') || str_contains($key, 'rate') ? 'number' : 'text'];
        }

        // Affichage
        if (in_array($key, ['products_per_page', 'show_prices', 'show_stock', 'enable_reviews'])) {
            return ['display', str_contains($key, 'show_') || str_contains($key, 'enable_') ? 'boolean' : 'number'];
        }

        // Réseaux sociaux
        if (str_contains($key, '_url') && !str_contains($key, 'logo') && !str_contains($key, 'favicon')) {
            return ['social', 'text'];
        }

        // Images
        if (str_contains($key, 'logo') || str_contains($key, 'favicon') || str_contains($key, 'banner') || str_contains($key, 'image')) {
            return ['images', 'text'];
        }

        // Maintenance
        if (str_contains($key, 'maintenance')) {
            return ['maintenance', str_contains($key, 'mode') ? 'boolean' : 'textarea'];
        }

        return ['general', 'text'];
    }
}