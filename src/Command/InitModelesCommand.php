<?php

namespace App\Command;

use App\Entity\Modele;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-modeles',
    description: 'Initialiser les modèles de lettres par défaut',
)]
class InitModelesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $modeles = [
            ['nom' => 'Lettres 3D Acier Inoxydable', 'image' => 'lettres/lettres-3d-en-acier-inoxydable.jpg', 'prix' => 15.00, 'desc' => 'Lettres 3D en acier inoxydable de haute qualité'],
            ['nom' => 'Lettres Boîtier Alu Lumineuses', 'image' => 'lettres/lettres-boitier-en-alu-lumineuses.jpg', 'prix' => 20.00, 'desc' => 'Lettres en aluminium avec éclairage LED intégré'],
            ['nom' => 'Lettres avec Néon LED', 'image' => 'lettres/lettres-avec-néon-led-intégré.jpg', 'prix' => 25.00, 'desc' => 'Lettres avec néon LED pour un effet moderne'],
            ['nom' => 'Lettres avec Ampoules', 'image' => 'lettres/lettres-avec-ampoules.jpg', 'prix' => 22.00, 'desc' => 'Lettres rétro avec ampoules apparentes'],
            ['nom' => 'Lettres Géantes Mariage', 'image' => 'lettres/lettres-géantes-avec-lumières-pour-mariage.jpg', 'prix' => 30.00, 'desc' => 'Lettres géantes lumineuses pour événements'],
            ['nom' => 'Lettres Miroir Infini', 'image' => 'lettres/lettres-en-relief-miroir-infini.jpg', 'prix' => 28.00, 'desc' => 'Lettres avec effet miroir infini'],
            ['nom' => 'Lettres PVC Rétro-éclairées', 'image' => 'lettres/lettres-en-pvc-rétro-éclairées.jpg', 'prix' => 18.00, 'desc' => 'Lettres en PVC avec rétro-éclairage'],
            ['nom' => 'Lettres Polystyrène HD', 'image' => 'lettres/lettres-en-polystyrène-haute-densité-lumineuse.jpg', 'prix' => 16.00, 'desc' => 'Lettres en polystyrène haute densité'],
            ['nom' => 'Lettres Polyestirène Compact', 'image' => 'lettres/lettres-en-polyestirene-compact-lumineuse.jpg', 'prix' => 17.00, 'desc' => 'Lettres compactes en polyestirène'],
            ['nom' => 'Lettres Plexiglas Lumineuses', 'image' => 'lettres/lettres-en-plexiglas-lumineuses.jpg', 'prix' => 24.00, 'desc' => 'Lettres en plexiglas avec éclairage'],
        ];

        $count = 0;
        foreach ($modeles as $data) {
            $modele = new Modele();
            $modele->setNom($data['nom']);
            $modele->setImage($data['image']);
            $modele->setPrixBase($data['prix']);
            $modele->setDescription($data['desc']);
            $modele->setActif(true);

            $this->entityManager->persist($modele);
            $count++;
        }

        $this->entityManager->flush();

        $io->success("$count modèles de lettres ont été créés avec succès!");

        return Command::SUCCESS;
    }
}
