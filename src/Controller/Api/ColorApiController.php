<?php

namespace App\Controller\Api;

use App\Service\ColorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/colors')]
class ColorApiController extends AbstractController
{
    #[Route('/available', name: 'api_colors_available', methods: ['GET'])]
    public function getAvailableColors(ColorService $colorService): JsonResponse
    {
        $colors = $colorService->getAvailableColorsForClient();

        return $this->json([
            'facade' => array_map(fn($color) => [
                'value' => $color->getHexCode(),
                'label' => $color->getDisplayName(),
                'stock' => $color->getStock(),
                'isLowStock' => $color->isLowStock(),
            ], $colors['facade']),
            'side' => array_map(fn($color) => [
                'value' => $color->getHexCode(),
                'label' => $color->getDisplayName(),
                'stock' => $color->getStock(),
                'isLowStock' => $color->isLowStock(),
            ], $colors['side']),
        ]);
    }
}