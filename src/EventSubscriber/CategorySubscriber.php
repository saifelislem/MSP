<?php

namespace App\EventSubscriber;

use App\Repository\CategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class CategorySubscriber implements EventSubscriberInterface
{
    private CategoryRepository $categoryRepository;
    private Environment $twig;

    public function __construct(CategoryRepository $categoryRepository, Environment $twig)
    {
        $this->categoryRepository = $categoryRepository;
        $this->twig = $twig;
    }

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

        // Récupérer les catégories actives
        $categories = $this->categoryRepository->findActiveCategories();

        // Rendre les catégories disponibles globalement dans Twig
        $this->twig->addGlobal('global_categories', $categories);
    }
}
