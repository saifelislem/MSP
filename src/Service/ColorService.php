<?php

namespace App\Service;

use App\Repository\ColorRepository;

class ColorService
{
    private ColorRepository $colorRepository;

    public function __construct(ColorRepository $colorRepository)
    {
        $this->colorRepository = $colorRepository;
    }

    /**
     * Récupérer les couleurs disponibles pour l'interface client
     */
    public function getAvailableColorsForClient(): array
    {
        return [
            'facade' => $this->colorRepository->findAvailableForFacade(),
            'side' => $this->colorRepository->findAvailableForSide(),
        ];
    }

    /**
     * Vérifier si une couleur est disponible
     */
    public function isColorAvailable(string $hexCode, string $type = 'both'): bool
    {
        $color = $this->colorRepository->findByHexCode($hexCode);
        
        if (!$color || !$color->isInStock()) {
            return false;
        }

        return match($type) {
            'facade' => $color->canBeUsedForFacade(),
            'side' => $color->canBeUsedForSide(),
            default => $color->canBeUsedForFacade() || $color->canBeUsedForSide(),
        };
    }

    /**
     * Décrémenter le stock d'une couleur
     */
    public function decreaseStock(string $hexCode, int $quantity = 1): bool
    {
        $color = $this->colorRepository->findByHexCode($hexCode);
        
        if (!$color || $color->getStock() < $quantity) {
            return false;
        }

        $color->decreaseStock($quantity);
        return true;
    }

    /**
     * Récupérer les couleurs avec stock faible
     */
    public function getLowStockColors(): array
    {
        return $this->colorRepository->findLowStock();
    }
}