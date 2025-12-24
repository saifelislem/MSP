<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\ModeleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomecontrollerController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository, 
        ModeleRepository $modeleRepository
    ): Response
    {
        return $this->render('front_home.html.twig', [
            'products' => $productRepository->findAll(),
            'modeles' => $modeleRepository->findAllActifs(),
        ]);
    }
}
