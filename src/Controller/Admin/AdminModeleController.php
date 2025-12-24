<?php

namespace App\Controller\Admin;

use App\Entity\Modele;
use App\Repository\ModeleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/modeles')]
#[IsGranted('ROLE_ADMIN')]
class AdminModeleController extends AbstractController
{
    #[Route('/', name: 'admin_modeles_index')]
    public function index(ModeleRepository $modeleRepository): Response
    {
        return $this->render('admin/modeles/index.html.twig', [
            'modeles' => $modeleRepository->findAllForAdmin(),
        ]);
    }

    #[Route('/new', name: 'admin_modeles_new')]
    public function new(Request $request, EntityManagerInterface $em, \App\Service\FormulaCalculatorService $formulaCalculator): Response
    {
        if ($request->isMethod('POST')) {
            $formuleCalcul = $request->request->get('formuleCalcul');
            
            // Valider la formule si elle est fournie
            if (!empty($formuleCalcul) && !$formulaCalculator->validateFormula($formuleCalcul)) {
                $this->addFlash('error', 'La formule de calcul est invalide. Vérifiez la syntaxe.');
                $categories = $em->getRepository(\App\Entity\Category::class)->findBy([], ['ordre' => 'ASC', 'nom' => 'ASC']);
                return $this->render('admin/modeles/new.html.twig', [
                    'categories' => $categories,
                    'formData' => $request->request->all()
                ]);
            }
            
            $modele = new Modele();
            $modele->setNom($request->request->get('nom'));
            $modele->setDescription($request->request->get('description'));
            $modele->setImage($request->request->get('image'));
            $modele->setPrixBase((float)$request->request->get('prixBase'));
            $modele->setFormuleCalcul($formuleCalcul);
            $modele->setActif($request->request->get('actif') === '1');

            // Gérer la catégorie
            $categoryId = $request->request->get('category_id');
            if ($categoryId) {
                $category = $em->getRepository(\App\Entity\Category::class)->find($categoryId);
                if ($category) {
                    $modele->setCategory($category);
                }
            }

            $em->persist($modele);
            $em->flush();

            $this->addFlash('success', 'Modèle créé avec succès.');
            return $this->redirectToRoute('admin_modeles_index');
        }

        $categories = $em->getRepository(\App\Entity\Category::class)->findBy([], ['ordre' => 'ASC', 'nom' => 'ASC']);
        return $this->render('admin/modeles/new.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_modeles_edit')]
    public function edit(Modele $modele, Request $request, EntityManagerInterface $em, \App\Service\FormulaCalculatorService $formulaCalculator): Response
    {
        if ($request->isMethod('POST')) {
            $formuleCalcul = $request->request->get('formuleCalcul');
            
            // Valider la formule si elle est fournie
            if (!empty($formuleCalcul) && !$formulaCalculator->validateFormula($formuleCalcul)) {
                $this->addFlash('error', 'La formule de calcul est invalide. Vérifiez la syntaxe.');
                $categories = $em->getRepository(\App\Entity\Category::class)->findBy([], ['ordre' => 'ASC', 'nom' => 'ASC']);
                return $this->render('admin/modeles/edit.html.twig', [
                    'modele' => $modele,
                    'categories' => $categories
                ]);
            }
            
            $modele->setNom($request->request->get('nom'));
            $modele->setDescription($request->request->get('description'));
            $modele->setImage($request->request->get('image'));
            $modele->setPrixBase((float)$request->request->get('prixBase'));
            $modele->setFormuleCalcul($formuleCalcul);
            $modele->setActif($request->request->get('actif') === '1');

            // Gérer la catégorie
            $categoryId = $request->request->get('category_id');
            if ($categoryId) {
                $category = $em->getRepository(\App\Entity\Category::class)->find($categoryId);
                $modele->setCategory($category);
            } else {
                $modele->setCategory(null);
            }

            $em->flush();

            $this->addFlash('success', 'Modèle modifié avec succès.');
            return $this->redirectToRoute('admin_modeles_index');
        }

        $categories = $em->getRepository(\App\Entity\Category::class)->findBy([], ['ordre' => 'ASC', 'nom' => 'ASC']);
        return $this->render('admin/modeles/edit.html.twig', [
            'modele' => $modele,
            'categories' => $categories
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_modeles_toggle', methods: ['POST'])]
    public function toggle(Modele $modele, EntityManagerInterface $em): Response
    {
        $modele->setActif(!$modele->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut du modèle mis à jour.');
        return $this->redirectToRoute('admin_modeles_index');
    }

    #[Route('/{id}/delete', name: 'admin_modeles_delete', methods: ['POST'])]
    public function delete(Modele $modele, EntityManagerInterface $em): Response
    {
        $em->remove($modele);
        $em->flush();

        $this->addFlash('success', 'Modèle supprimé avec succès.');
        return $this->redirectToRoute('admin_modeles_index');
    }
}
