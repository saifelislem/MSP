<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/download/{id}', name: 'invoice_download')]
    public function download(Order $order, PdfService $pdfService): Response
    {
        try {
            // Générer le PDF
            $pdf = $pdfService->generateInvoicePdf($order);
            $filename = 'facture_' . $order->getOrderNumber() . '.pdf';

            // Retourner le PDF en téléchargement
            return new Response(
                $pdf,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération de la facture: ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/view/{id}', name: 'invoice_view')]
    public function view(Order $order, PdfService $pdfService): Response
    {
        try {
            // Générer le PDF
            $pdf = $pdfService->generateInvoicePdf($order);
            $filename = 'facture_' . $order->getOrderNumber() . '.pdf';

            // Afficher le PDF dans le navigateur
            return new Response(
                $pdf,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération de la facture: ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
}
