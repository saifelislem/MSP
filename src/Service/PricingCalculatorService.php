<?php

namespace App\Service;

use App\Entity\Product;

class PricingCalculatorService
{
    public function calculatePrice(Product $product, array $dimensions = []): float
    {
        // Si le produit n'utilise pas de prix personnalisé, retourner le prix de base
        if (!$product->isUseCustomPricing()) {
            return $product->getPrice();
        }

        $basePrice = $product->getBasePrice() ?? 0;
        $pricePerUnit = $product->getPricePerUnit() ?? 0;
        $formula = $product->getPricingFormula();
        $factors = $product->getPricingFactors() ?? [];

        // Si pas de formule, utiliser le calcul simple
        if (!$formula) {
            return $this->calculateSimplePrice($basePrice, $pricePerUnit, $dimensions, $product->getPricingUnit());
        }

        // Calcul avec formule personnalisée
        return $this->calculateWithFormula($formula, $basePrice, $pricePerUnit, $dimensions, $factors);
    }

    private function calculateSimplePrice(float $basePrice, float $pricePerUnit, array $dimensions, string $unit): float
    {
        $totalPrice = $basePrice;

        // Calcul basé sur les dimensions
        switch ($unit) {
            case 'cm':
            case 'm':
                $length = $dimensions['length'] ?? 0;
                $width = $dimensions['width'] ?? 0;
                
                if ($unit === 'm') {
                    $length = $length / 100; // Convertir cm en m
                    $width = $width / 100;
                }
                
                // Surface = longueur × largeur
                $surface = $length * $width;
                $totalPrice += $surface * $pricePerUnit;
                break;
                
            case 'linear_cm':
            case 'linear_m':
                $length = $dimensions['length'] ?? 0;
                
                if ($unit === 'linear_m') {
                    $length = $length / 100; // Convertir cm en m
                }
                
                $totalPrice += $length * $pricePerUnit;
                break;
                
            case 'piece':
                $quantity = $dimensions['quantity'] ?? 1;
                $totalPrice += $quantity * $pricePerUnit;
                break;
        }

        return max(0, $totalPrice);
    }

    private function calculateWithFormula(string $formula, float $basePrice, float $pricePerUnit, array $dimensions, array $factors): float
    {
        // Variables disponibles dans la formule
        $variables = [
            'base_price' => $basePrice,
            'price_per_unit' => $pricePerUnit,
            'length' => $dimensions['length'] ?? 0,
            'width' => $dimensions['width'] ?? 0,
            'height' => $dimensions['height'] ?? 0,
            'quantity' => $dimensions['quantity'] ?? 1,
            'surface' => ($dimensions['length'] ?? 0) * ($dimensions['width'] ?? 0),
            'perimeter' => 2 * (($dimensions['length'] ?? 0) + ($dimensions['width'] ?? 0)),
        ];

        // Ajouter les facteurs personnalisés
        foreach ($factors as $factor) {
            if (isset($factor['name']) && isset($dimensions[$factor['name']])) {
                $variables[$factor['name']] = $dimensions[$factor['name']];
            }
        }

        // Remplacer les variables dans la formule
        $evaluableFormula = $formula;
        foreach ($variables as $name => $value) {
            $evaluableFormula = str_replace('{' . $name . '}', $value, $evaluableFormula);
        }

        // Sécuriser l'évaluation (permettre seulement les opérations mathématiques de base)
        if (preg_match('/^[0-9+\-*\/\.\(\)\s]+$/', $evaluableFormula)) {
            try {
                $result = eval("return $evaluableFormula;");
                return max(0, (float)$result);
            } catch (\Exception $e) {
                // En cas d'erreur, retourner le prix de base
                return $basePrice;
            }
        }

        return $basePrice;
    }

    public function getAvailableVariables(): array
    {
        return [
            'base_price' => 'Prix de base',
            'price_per_unit' => 'Prix par unité',
            'length' => 'Longueur (cm)',
            'width' => 'Largeur (cm)',
            'height' => 'Hauteur (cm)',
            'quantity' => 'Quantité',
            'surface' => 'Surface (longueur × largeur)',
            'perimeter' => 'Périmètre (2 × (longueur + largeur))',
        ];
    }

    public function validateFormula(string $formula): bool
    {
        // Vérifier que la formule contient seulement des caractères autorisés
        return preg_match('/^[0-9+\-*\/\.\(\)\s\{\}a-zA-Z_]+$/', $formula);
    }
}