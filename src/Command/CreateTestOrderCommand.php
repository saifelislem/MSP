<?php

namespace App\Command;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-order',
    description: 'Créer une commande de test pour vérifier l\'admin',
)]
class CreateTestOrderCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🛒 Création d\'une commande de test');

        // Récupérer un produit existant
        $product = $this->entityManager->getRepository(Product::class)->findOneBy([]);
        
        if (!$product) {
            $io->error('Aucun produit trouvé. Créez d\'abord un produit.');
            return Command::FAILURE;
        }

        // Créer la commande
        $order = new Order();
        $order->setTotal(25.50);
        $order->setCustomerName('Client Test');
        $order->setCustomerEmail('test@example.com');
        $order->setCustomerPhone('0612345678');
        $order->setNotes('Commande de test créée automatiquement');

        // Créer l'article de commande
        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setQuantity(2);
        $orderItem->setPrice(12.75);
        $orderItem->setOrder($order);

        $order->addOrderItem($orderItem);

        // Sauvegarder
        $this->entityManager->persist($order);
        $this->entityManager->persist($orderItem);
        $this->entityManager->flush();

        $io->success('✅ Commande de test créée avec succès!');
        
        $io->table(
            ['Numéro', 'Client', 'Total', 'Articles'],
            [[$order->getOrderNumber(), $order->getCustomerName(), $order->getTotal() . '€', '1']]
        );

        $io->note([
            'Vérifiez dans l\'admin:',
            '  1. Allez sur http://localhost:8000/login',
            '  2. Connectez-vous (admin@msp.com / admin123)',
            '  3. Cliquez sur "Commandes"',
            '  4. Vous devriez voir la commande: ' . $order->getOrderNumber()
        ]);

        return Command::SUCCESS;
    }
}