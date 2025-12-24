<?php

namespace App\Controller\Admin;

use App\Entity\Color;
use App\Repository\ColorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/colors')]
class ColorController extends AbstractController
{
    #[Route('/', name: 'admin_colors_index')]
    public function index(ColorRepository $colorRepository): Response
    {
        $colors = $colorRepository->findAllForAdmin();
        $lowStockColors = $colorRepository->findLowStock();

        return $this->render('admin/colors/index.html.twig', [
            'colors' => $colors,
            'lowStockColors' => $lowStockColors,
        ]);
    }

    #[Route('/new', name: 'admin_colors_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $color = new Color();
            $color->setName($request->request->get('name'));
            $color->setHexCode($request->request->get('hexCode'));
            $color->setEmoji($request->request->get('emoji'));
            $color->setStock((int) $request->request->get('stock'));
            $color->setMinStock((int) $request->request->get('minStock', 5));
            $color->setType($request->request->get('type', 'both'));
            $color->setSortOrder((int) $request->request->get('sortOrder', 0));
            $color->setIsActive($request->request->getBoolean('isActive', true));

            $em->persist($color);
            $em->flush();

            $this->addFlash('success', 'Couleur ajoutée avec succès!');
            return $this->redirectToRoute('admin_colors_index');
        }

        return $this->render('admin/colors/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_colors_edit', methods: ['GET', 'POST'])]
    public function edit(Color $color, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $color->setName($request->request->get('name'));
            $color->setHexCode($request->request->get('hexCode'));
            $color->setEmoji($request->request->get('emoji'));
            $color->setStock((int) $request->request->get('stock'));
            $color->setMinStock((int) $request->request->get('minStock'));
            $color->setType($request->request->get('type'));
            $color->setSortOrder((int) $request->request->get('sortOrder'));
            $color->setIsActive($request->request->getBoolean('isActive'));

            $em->flush();

            $this->addFlash('success', 'Couleur modifiée avec succès!');
            return $this->redirectToRoute('admin_colors_index');
        }

        return $this->render('admin/colors/edit.html.twig', [
            'color' => $color,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_colors_toggle', methods: ['POST'])]
    public function toggle(Color $color, EntityManagerInterface $em): Response
    {
        $color->setIsActive(!$color->isActive());
        $em->flush();

        $status = $color->isActive() ? 'activée' : 'désactivée';
        $this->addFlash('success', "Couleur {$status} avec succès!");

        return $this->redirectToRoute('admin_colors_index');
    }

    #[Route('/{id}/stock', name: 'admin_colors_update_stock', methods: ['POST'])]
    public function updateStock(Color $color, Request $request, EntityManagerInterface $em): Response
    {
        $action = $request->request->get('action');
        $quantity = (int) $request->request->get('quantity', 1);

        if ($action === 'add') {
            $color->increaseStock($quantity);
            $message = "Stock augmenté de {$quantity}";
        } elseif ($action === 'remove') {
            $color->decreaseStock($quantity);
            $message = "Stock diminué de {$quantity}";
        } else {
            $color->setStock($quantity);
            $message = "Stock mis à jour à {$quantity}";
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => $message,
            'newStock' => $color->getStock(),
            'isLowStock' => $color->isLowStock(),
        ]);
    }

    #[Route('/api/available', name: 'admin_colors_api_available', methods: ['GET'])]
    public function getAvailableColors(ColorRepository $colorRepository): Response
    {
        $facadeColors = $colorRepository->findAvailableForFacade();
        $sideColors = $colorRepository->findAvailableForSide();

        return $this->json([
            'facade' => array_map(fn($color) => [
                'value' => $color->getHexCode(),
                'label' => $color->getDisplayName(),
                'stock' => $color->getStock(),
            ], $facadeColors),
            'side' => array_map(fn($color) => [
                'value' => $color->getHexCode(),
                'label' => $color->getDisplayName(),
                'stock' => $color->getStock(),
            ], $sideColors),
        ]);
    }

    #[Route('/init-default', name: 'admin_colors_init_default')]
    public function initDefaultColors(EntityManagerInterface $em, ColorRepository $colorRepository): Response
    {
        // Vérifier si des couleurs existent déjà
        if (count($colorRepository->findAll()) > 0) {
            $this->addFlash('warning', 'Des couleurs existent déjà!');
            return $this->redirectToRoute('admin_colors_index');
        }

        // Couleurs par défaut
        $defaultColors = [
            ['name' => 'Noir', 'hex' => '#2A2A2A', 'emoji' => '⚫', 'stock' => 50, 'type' => 'both', 'order' => 1],
            ['name' => 'Blanc', 'hex' => '#FFFFFF', 'emoji' => '⚪', 'stock' => 50, 'type' => 'both', 'order' => 2],
            ['name' => 'Rouge', 'hex' => '#E74C3C', 'emoji' => '🔴', 'stock' => 30, 'type' => 'both', 'order' => 3],
            ['name' => 'Bleu', 'hex' => '#3498DB', 'emoji' => '🔵', 'stock' => 30, 'type' => 'both', 'order' => 4],
            ['name' => 'Vert', 'hex' => '#2ECC71', 'emoji' => '🟢', 'stock' => 25, 'type' => 'both', 'order' => 5],
            ['name' => 'Orange', 'hex' => '#F39C12', 'emoji' => '🟠', 'stock' => 20, 'type' => 'both', 'order' => 6],
            ['name' => 'Violet', 'hex' => '#9B59B6', 'emoji' => '🟣', 'stock' => 15, 'type' => 'both', 'order' => 7],
            ['name' => 'Rose', 'hex' => '#E91E63', 'emoji' => '🌸', 'stock' => 15, 'type' => 'both', 'order' => 8],
            ['name' => 'Or', 'hex' => '#FFD700', 'emoji' => '🟡', 'stock' => 10, 'type' => 'facade', 'order' => 9],
            ['name' => 'Argent', 'hex' => '#C0C0C0', 'emoji' => '⚪', 'stock' => 10, 'type' => 'facade', 'order' => 10],
            ['name' => 'Marron', 'hex' => '#8B4513', 'emoji' => '🟤', 'stock' => 20, 'type' => 'both', 'order' => 11],
            ['name' => 'Cyan', 'hex' => '#00BCD4', 'emoji' => '🔷', 'stock' => 15, 'type' => 'both', 'order' => 12],
            ['name' => 'Gris clair', 'hex' => '#E8E8E8', 'emoji' => '⚪', 'stock' => 40, 'type' => 'side', 'order' => 13],
        ];

        foreach ($defaultColors as $colorData) {
            $color = new Color();
            $color->setName($colorData['name']);
            $color->setHexCode($colorData['hex']);
            $color->setEmoji($colorData['emoji']);
            $color->setStock($colorData['stock']);
            $color->setMinStock(5);
            $color->setType($colorData['type']);
            $color->setSortOrder($colorData['order']);
            $color->setIsActive(true);

            $em->persist($color);
        }

        $em->flush();

        $this->addFlash('success', 'Couleurs par défaut initialisées avec succès!');
        return $this->redirectToRoute('admin_colors_index');
    }
}