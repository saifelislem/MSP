<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categories')]
class AdminCategoryController extends AbstractController
{
    #[Route('/', name: 'admin_categories_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findBy([], ['ordre' => 'ASC', 'nom' => 'ASC']);

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'admin_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $category = new Category();
            $category->setNom($request->request->get('nom'));
            $category->setDescription($request->request->get('description'));
            $category->setImage($request->request->get('image'));
            $category->setOrdre((int) $request->request->get('ordre', 0));
            $category->setActif($request->request->get('actif') === '1');
            $category->setSupportsLogo($request->request->get('supportsLogo') === '1');

            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie créée avec succès!');
            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/categories/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_categories_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $category->setNom($request->request->get('nom'));
            $category->setDescription($request->request->get('description'));
            $category->setImage($request->request->get('image'));
            $category->setOrdre((int) $request->request->get('ordre', 0));
            $category->setActif($request->request->get('actif') === '1');
            $category->setSupportsLogo($request->request->get('supportsLogo') === '1');

            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès!');
            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_categories_toggle', methods: ['POST'])]
    public function toggle(Category $category, EntityManagerInterface $em): Response
    {
        $category->setActif(!$category->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut de la catégorie modifié!');
        return $this->redirectToRoute('admin_categories_index');
    }

    #[Route('/{id}/delete', name: 'admin_categories_delete', methods: ['POST'])]
    public function delete(Category $category, EntityManagerInterface $em): Response
    {
        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'Catégorie supprimée avec succès!');
        return $this->redirectToRoute('admin_categories_index');
    }
}
