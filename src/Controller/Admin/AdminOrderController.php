<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
class AdminOrderController extends AbstractController
{
    #[Route('/', name: 'admin_orders_index')]
    public function index(OrderRepository $orderRepository, Request $request, LoggerInterface $logger = null): Response
    {
        $status = $request->query->get('status');
        
        if ($status) {
            $orders = $orderRepository->findByStatus($status);
        } else {
            $orders = $orderRepository->findAllOrdered();
        }

        // Debug: Log le nombre de commandes
        if ($logger) {
            $logger->info('Admin Orders Index - Nombre de commandes: ' . count($orders));
            foreach ($orders as $order) {
                $logger->info('Commande: ' . $order->getOrderNumber() . ' - ' . $order->getCustomerName());
            }
        }

        // Statistiques rapides pour l'admin
        $totalOrders = $orderRepository->count([]);
        $pendingCount = $orderRepository->countByStatus('pending');
        $processingCount = $orderRepository->countByStatus('processing');
        $completedCount = $orderRepository->countByStatus('completed');
        $cancelledCount = $orderRepository->countByStatus('cancelled');
        $totalRevenue = method_exists($orderRepository, 'getTotalRevenue') ? $orderRepository->getTotalRevenue() : 0.0;

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $orders,
            'current_status' => $status,
            'totalOrders' => $totalOrders,
            'pendingCount' => $pendingCount,
            'processingCount' => $processingCount,
            'completedCount' => $completedCount,
            'cancelledCount' => $cancelledCount,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    #[Route('/{id}', name: 'admin_orders_show', requirements: ['id' => '\d+'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/status', name: 'admin_orders_update_status', methods: ['POST'])]
    public function updateStatus(Order $order, Request $request, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $newStatus = $request->request->get('status');
        $valid = in_array($newStatus, ['pending', 'processing', 'completed', 'cancelled']);

        if ($valid) {
            $order->setStatus($newStatus);
            $em->flush();
            $logger->info('Order status updated', ['order_id' => $order->getId(), 'new_status' => $newStatus]);
            $this->addFlash('success', 'Statut de la commande mis à jour avec succès.');
        } else {
            $logger->warning('Invalid order status attempted', ['order_id' => $order->getId(), 'attempt' => $newStatus]);
            $this->addFlash('error', 'Statut invalide.');
        }

        // Si requête AJAX, renvoyer JSON pour permettre une mise à jour UI sans reload
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => $valid,
                'newStatus' => $valid ? $newStatus : null,
                'message' => $valid ? 'Statut mis à jour' : 'Statut invalide'
            ]);
        }

        return $this->redirectToRoute('admin_orders_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}/delete', name: 'admin_orders_delete', methods: ['POST'])]
    public function delete(Order $order, EntityManagerInterface $em): Response
    {
        $em->remove($order);
        $em->flush();

        $this->addFlash('success', 'Commande supprimée avec succès.');
        return $this->redirectToRoute('admin_orders_index');
    }
}
