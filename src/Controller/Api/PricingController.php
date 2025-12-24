<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Service\PricingCalculatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/pricing')]
class PricingController extends AbstractController
{
    public function __construct(
        private PricingCalculatorService $pricingCalculator,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/calculate/{id}', name: 'api_pricing_calculate', methods: ['POST'])]
    public function calculatePrice(int $id, Request $request): JsonResponse
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $dimensions = $data['dimensions'] ?? [];

        // Calculer le prix
        $calculatedPrice = $this->pricingCalculator->calculatePrice($product, $dimensions);

        return new JsonResponse([
            'price' => $calculatedPrice,
            'formatted_price' => number_format($calculatedPrice, 2, ',', ' ') . ' €',
            'base_price' => $product->getBasePrice(),
            'use_custom_pricing' => $product->isUseCustomPricing(),
            'pricing_unit' => $product->getPricingUnit(),
            'dimensions' => $dimensions
        ]);
    }

    #[Route('/validate-formula', name: 'api_pricing_validate_formula', methods: ['POST'])]
    public function validateFormula(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $formula = $data['formula'] ?? '';

        $isValid = $this->pricingCalculator->validateFormula($formula);

        return new JsonResponse([
            'valid' => $isValid,
            'message' => $isValid ? 'Formule valide' : 'Formule invalide'
        ]);
    }

    #[Route('/variables', name: 'api_pricing_variables', methods: ['GET'])]
    public function getAvailableVariables(): JsonResponse
    {
        return new JsonResponse([
            'variables' => $this->pricingCalculator->getAvailableVariables()
        ]);
    }
}