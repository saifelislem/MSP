<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/test')]
#[IsGranted('ROLE_ADMIN')]
class TestOrdersController extends AbstractController
{
    #[Route('/orders', name: 'admin_test_orders')]
    public function testOrders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();
        
        $html = '<h1>TEST - Liste des Commandes</h1>';
        $html .= '<p>Nombre total: ' . count($orders) . '</p>';
        $html .= '<hr>';
        
        if (count($orders) === 0) {
            $html .= '<p style="color: red;">AUCUNE COMMANDE TROUVÉE!</p>';
        } else {
            $html .= '<table border="1" cellpadding="10">';
            $html .= '<tr><th>ID</th><th>Numéro</th><th>Date</th><th>Client</th><th>Email</th><th>Total</th><th>Statut</th></tr>';
            
            foreach ($orders as $order) {
                $html .= '<tr>';
                $html .= '<td>' . $order->getId() . '</td>';
                $html .= '<td><strong>' . $order->getOrderNumber() . '</strong></td>';
                $html .= '<td>' . $order->getCreatedAt()->format('d/m/Y H:i') . '</td>';
                $html .= '<td>' . ($order->getCustomerName() ?? 'N/A') . '</td>';
                $html .= '<td>' . ($order->getCustomerEmail() ?? 'N/A') . '</td>';
                $html .= '<td>' . number_format($order->getTotal(), 2) . '€</td>';
                $html .= '<td>' . $order->getStatusLabel() . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</table>';
        }
        
        $html .= '<hr>';
        $html .= '<p><a href="/admin/orders">← Retour à la liste normale</a></p>';
        
        return new Response($html);
    }
}
