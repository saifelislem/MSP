<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        
        // Gérer les requêtes AJAX depuis le modal
        if ($request->isMethod('POST') && $request->isXmlHttpRequest()) {
            // Traitement manuel des données pour les requêtes AJAX
            $data = $request->request->all('product');
            
            if (isset($data['text']) && isset($data['largeur']) && isset($data['hauteur']) && isset($data['typeEcriture'])) {
                $product->setText($data['text']);
                $product->setLargeur((int)$data['largeur']);
                $product->setHauteur((int)$data['hauteur']);
                $product->setTypeEcriture($data['typeEcriture']);
                
                $entityManager->persist($product);
                $entityManager->flush();
                
                return $this->json([
                    'success' => true, 
                    'message' => 'Produit créé avec succès',
                    'productId' => $product->getId()
                ]);
            }
            
            return $this->json(['success' => false, 'message' => 'Données manquantes']);
        }
        
        // Traitement normal du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/create-and-add-to-cart', name: 'app_product_create_and_add', methods: ['POST'])]
    public function createAndAddToCart(Request $request, EntityManagerInterface $entityManager): Response
    {
        $text = $request->request->get('text');
        $largeur = $request->request->get('largeur');
        $hauteur = $request->request->get('hauteur');
        $typeEcriture = $request->request->get('typeEcriture');
        $imageUrl = $request->request->get('imageUrl');
        $modeleName = $request->request->get('modeleName');
        
        if (!$text || !$largeur || !$hauteur || !$typeEcriture) {
            return $this->json([
                'success' => false,
                'message' => 'Données manquantes'
            ], 400);
        }
        
        $product = new Product();
        $product->setText($text);
        $product->setLargeur((int)$largeur);
        $product->setHauteur((int)$hauteur);
        $product->setTypeEcriture($typeEcriture);
        $product->setImageUrl($imageUrl);
        $product->setModeleName($modeleName);
        
        $entityManager->persist($product);
        $entityManager->flush();
        
        return $this->json([
            'success' => true,
            'message' => 'Produit créé avec succès',
            'productId' => $product->getId()
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}
