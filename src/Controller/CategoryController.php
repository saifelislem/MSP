<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/{slug}', name: 'category_show', methods: ['GET'])]
    public function show(string $slug, CategoryRepository $categoryRepository, CartService $cartService): Response
    {
        $category = $categoryRepository->findBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        // Récupérer uniquement les modèles actifs de cette catégorie
        $modeles = [];
        foreach ($category->getModeles() as $modele) {
            if ($modele->isActif()) {
                $modeles[] = $modele;
            }
        }

        $cart = $cartService->getCurrentCart();

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'modeles' => $modeles,
            'cartItemsCount' => $cart->getCartItems()->count()
        ]);
    }
}
