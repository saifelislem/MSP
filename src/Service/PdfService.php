<?php

namespace App\Service;

use App\Entity\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfService
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Générer une facture PDF pour une commande
     */
    public function generateInvoicePdf(Order $order): string
    {
        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($options);

        // Générer le HTML depuis le template
        $html = $this->twig->render('pdf/invoice.html.twig', [
            'order' => $order,
            'customerName' => $order->getCustomerName(),
        ]);

        // Charger le HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $dompdf->render();

        // Retourner le PDF en string
        return $dompdf->output();
    }

    /**
     * Télécharger la facture PDF
     */
    public function downloadInvoicePdf(Order $order): void
    {
        $pdf = $this->generateInvoicePdf($order);
        $filename = 'facture_' . $order->getOrderNumber() . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        
        echo $pdf;
        exit;
    }
}
