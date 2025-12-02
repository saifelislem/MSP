<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/debug')]
class DebugController extends AbstractController
{
    #[Route('/orders-check', name: 'admin_debug_orders')]
    public function checkOrders(OrderRepository $orderRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $roles = $user ? $user->getRoles() : [];
        
        // Récupérer toutes les commandes directement
        $allOrders = $orderRepository->findAll();
        $ordersCount = count($allOrders);
        
        // Récupérer via requête SQL directe
        $connection = $em->getConnection();
        $sql = 'SELECT COUNT(*) as total FROM `order`';
        $result = $connection->executeQuery($sql)->fetchAssociative();
        $sqlCount = $result['total'] ?? 0;
        
        // Dernières commandes
        $recentOrders = $orderRepository->findBy([], ['createdAt' => 'DESC'], 10);
        
        return $this->render('admin/debug/orders.html.twig', [
            'user' => $user,
            'roles' => $roles,
            'orders_count_repo' => $ordersCount,
            'orders_count_sql' => $sqlCount,
            'recent_orders' => $recentOrders,
            'all_orders' => $allOrders,
        ]);
    }
}
