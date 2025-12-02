<?php

namespace App\EventSubscriber;

use App\Service\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class CartSubscriber implements EventSubscriberInterface
{
    private CartService $cartService;
    private Environment $twig;

    public function __construct(CartService $cartService, Environment $twig)
    {
        $this->cartService = $cartService;
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
        if (!$event->isMainRequest()) {
            return;
        }

        // Inject cart data into all Twig templates
        $cart = $this->cartService->getCurrentCart();
        $this->twig->addGlobal('cart', $cart);
        $this->twig->addGlobal('cartItemsCount', $this->cartService->getCartItemsCount());
        $this->twig->addGlobal('cartTotal', $this->cartService->getCartTotal());
    }
}
