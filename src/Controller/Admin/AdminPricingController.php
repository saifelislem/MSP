<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Service\PricingCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/pricing')]
class AdminPricingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PricingCalculatorService $pricingCalculator
    ) {}

    #[Route('/', name: 'admin_pricing_index')]
    public function index(): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('admin/pricing/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_pricing_edit')]
    public function edit(Product $product, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            $product->setUseCustomPricing($data['use_custom_pricing'] ?? false);
            $product->setBasePrice($data['base_price'] ? (float)$data['base_price'] : null);
            $product->setPricePerUnit($data['price_per_unit'] ? (float)$data['price_per_unit'] : null);
            $product->setPricingUnit($data['pricing_unit'] ?? 'cm');
            $product->setPricingFormula($data['pricing_formula'] ?? null);
            
            // Gérer les facteurs de prix
            $factors = [];
            if (!empty($data['factor_names'])) {
                foreach ($data['factor_names'] as $index => $name) {
                    if (!empty($name)) {
                        $factors[] = [
                            'name' => $name,
                            'label' => $data['factor_labels'][$index] ?? $name,
                            'type' => $data['factor_types'][$index] ?? 'number',
                            'default' => $data['factor_defaults'][$index] ?? 0
                        ];
                    }
                }
            }
            $product->setPricingFactors($factors);

            $this->entityManager->flush();

            $this->addFlash('success', 'Configuration de prix mise à jour avec succès !');
            return $this->redirectToRoute('admin_pricing_index');
        }

        return $this->render('admin/pricing/edit.html.twig', [
            'product' => $product,
            'available_variables' => $this->pricingCalculator->getAvailableVariables(),
        ]);
    }
}