<?php

namespace App\EventListener;

use App\Service\SiteSettingsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class SiteSettingsListener implements EventSubscriberInterface
{
    public function __construct(
        private SiteSettingsService $settingsService,
        private Environment $twig
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Ne pas exécuter pour les sous-requêtes
        if (!$event->isMainRequest()) {
            return;
        }

        // Initialiser les paramètres par défaut si nécessaire
        $this->settingsService->initializeDefaultSettings();

        // Injecter les paramètres du site dans Twig
        $this->twig->addGlobal('site_settings', [
            'title' => $this->settingsService->get('site_title', 'MS Lettres'),
            'description' => $this->settingsService->get('site_description', 'Enseignes personnalisées'),
            'primary_color' => $this->settingsService->get('primary_color', '#2F4E9B'),
            'secondary_color' => $this->settingsService->get('secondary_color', '#8A92AD'),
            'accent_color' => $this->settingsService->get('accent_color', '#DADDEB'),
            'logo_url' => $this->settingsService->get('logo_url', '/images/logo.png'),
            'banner_image' => $this->settingsService->get('banner_image', '/images/banner-01.jpg'),
            'favicon_url' => $this->settingsService->get('favicon_url', '/images/icons/favicon.png'),
            'contact_email' => $this->settingsService->get('contact_email', 'contact@mslettres.com'),
            'contact_phone' => $this->settingsService->get('contact_phone', '+33 1 23 45 67 89'),
            'footer_text' => $this->settingsService->get('footer_text', '© 2024 MS Lettres'),
            'currency_symbol' => $this->settingsService->get('currency_symbol', '€'),
            'maintenance_mode' => $this->settingsService->get('maintenance_mode', false),
        ]);

        // Générer le CSS personnalisé si nécessaire
        $cssPath = 'public/css/custom-generated.css';
        if (!file_exists($cssPath)) {
            $this->settingsService->saveCustomCSS();
        }
    }
}