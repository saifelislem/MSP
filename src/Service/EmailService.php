<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class EmailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private string $fromEmail;
    private string $fromName;

    public function __construct(
        MailerInterface $mailer,
        LoggerInterface $logger,
        string $fromEmail = 'noreply@enseignes.com',
        string $fromName = 'Enseignes Personnalisées'
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    /**
     * Envoyer la facture au client après paiement
     */
    public function sendInvoiceEmail(Order $order): bool
    {
        try {
            $customerEmail = $order->getCustomerEmail();
            
            if (!$customerEmail) {
                $this->logger->warning('Cannot send invoice: no customer email', [
                    'order_id' => $order->getId(),
                    'order_number' => $order->getOrderNumber()
                ]);
                return false;
            }

            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($customerEmail)
                ->subject('Facture - Commande ' . $order->getOrderNumber())
                ->htmlTemplate('emails/invoice.html.twig')
                ->context([
                    'order' => $order,
                    'customerName' => $order->getCustomerName(),
                ]);

            $this->mailer->send($email);

            $this->logger->info('Invoice email sent successfully', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'customer_email' => $customerEmail
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to send invoice email', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Envoyer une notification à l'admin pour nouvelle commande
     */
    public function sendNewOrderNotification(Order $order, string $adminEmail): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->fromEmail, $this->fromName))
                ->to($adminEmail)
                ->subject('Nouvelle commande - ' . $order->getOrderNumber())
                ->htmlTemplate('emails/new_order_admin.html.twig')
                ->context([
                    'order' => $order,
                ]);

            $this->mailer->send($email);

            $this->logger->info('New order notification sent to admin', [
                'order_id' => $order->getId(),
                'order_number' => $order->getOrderNumber(),
                'admin_email' => $adminEmail
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to send new order notification', [
                'order_id' => $order->getId(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
