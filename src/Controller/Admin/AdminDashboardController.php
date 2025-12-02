<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Repository\ModeleRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        OrderRepository $orderRepository,
        ModeleRepository $modeleRepository,
        ProductRepository $productRepository
    ): Response {
        $stats = [
            'total_orders' => count($orderRepository->findAll()),
            'pending_orders' => $orderRepository->countByStatus('pending'),
            'processing_orders' => $orderRepository->countByStatus('processing'),
            'completed_orders' => $orderRepository->countByStatus('completed'),
            'total_revenue' => $orderRepository->getTotalRevenue(),
            'total_modeles' => count($modeleRepository->findAll()),
            'active_modeles' => count($modeleRepository->findAllActifs()),
            'total_products' => count($productRepository->findAll()),
        ];

        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recent_orders' => $recentOrders,
        ]);
    }
}
