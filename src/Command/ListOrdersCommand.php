<?php

namespace App\Command;

use App\Repository\OrderRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-orders',
    description: 'Lister toutes les commandes',
)]
class ListOrdersCommand extends Command
{
    public function __construct(private OrderRepository $orderRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $orders = $this->orderRepository->findAllOrdered();

        if (count($orders) === 0) {
            $io->warning('Aucune commande trouvée dans la base de données.');
            $io->note([
                'Pour créer une commande de test:',
                '  php bin/console app:create-test-order',
                '',
                'Ou passez une commande sur le site:',
                '  http://localhost:8000/cart'
            ]);
            return Command::SUCCESS;
        }

        $io->title('📦 Liste des Commandes');

        $rows = [];
        foreach ($orders as $order) {
            $rows[] = [
                $order->getId(),
                $order->getOrderNumber(),
                $order->getCreatedAt()->format('d/m/Y H:i'),
                $order->getCustomerName() ?? 'N/A',
                $order->getCustomerEmail() ?? 'N/A',
                $order->getOrderItems()->count(),
                number_format($order->getTotal(), 2) . '€',
                $order->getStatusLabel()
            ];
        }

        $io->table(
            ['ID', 'N° Commande', 'Date', 'Client', 'Email', 'Articles', 'Total', 'Statut'],
            $rows
        );

        $io->success(count($orders) . ' commande(s) trouvée(s)');

        $io->note([
            'Pour voir les commandes dans l\'admin:',
            '  1. Allez sur http://localhost:8000/login',
            '  2. Connectez-vous (admin@msp.com / admin123)',
            '  3. Cliquez sur "Commandes"'
        ]);

        return Command::SUCCESS;
    }
}
