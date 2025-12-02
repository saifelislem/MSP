<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Modele;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:init-system',
    description: 'Initialiser le système complet (admin + modèles)',
)]
class InitSystemCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🚀 Initialisation du système MSP');

        // 1. Créer l'admin
        $io->section('1. Création de l\'administrateur');
        
        $existingAdmin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@msp.com']);
        
        if ($existingAdmin) {
            $io->warning('Un admin existe déjà avec l\'email admin@msp.com');
        } else {
            $admin = new User();
            $admin->setEmail('admin@msp.com');
            $admin->setName('Admin MSP');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));

            $this->entityManager->persist($admin);
            $this->entityManager->flush();

            $io->success('✅ Admin créé avec succès!');
            $io->table(
                ['Email', 'Mot de passe', 'Rôle'],
                [['admin@msp.com', 'admin123', 'ADMIN']]
            );
        }

        // 2. Créer les modèles
        $io->section('2. Création des modèles de lettres');

        $existingModeles = $this->entityManager->getRepository(Modele::class)->findAll();
        
        if (count($existingModeles) > 0) {
            $io->warning(count($existingModeles) . ' modèle(s) existe(nt) déjà');
        } else {
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

            foreach ($modeles as $data) {
                $modele = new Modele();
                $modele->setNom($data['nom']);
                $modele->setImage($data['image']);
                $modele->setPrixBase($data['prix']);
                $modele->setDescription($data['desc']);
                $modele->setActif(true);

                $this->entityManager->persist($modele);
            }

            $this->entityManager->flush();

            $io->success('✅ ' . count($modeles) . ' modèles créés avec succès!');
        }

        // Résumé final
        $io->section('📊 Résumé');
        $io->success('Système initialisé avec succès!');
        
        $io->table(
            ['Élément', 'Statut'],
            [
                ['Admin', '✅ Créé'],
                ['Modèles', '✅ Créés'],
                ['Base de données', '✅ À jour'],
            ]
        );

        $io->note([
            'Connexion admin:',
            '  URL: http://localhost:8000/login',
            '  Email: admin@msp.com',
            '  Mot de passe: admin123',
            '',
            '⚠️  Changez le mot de passe après la première connexion!'
        ]);

        return Command::SUCCESS;
    }
}
