<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(nullable: true)]
    private ?int $largeur = null;

    #[ORM\Column(nullable: true)]
    private ?int $hauteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeEcriture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $modeleName = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $mode = 'text'; // 'text' ou 'logo'

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $logoData = null; // Stockage du logo en base64

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoFileName = null;

    #[ORM\Column(nullable: true)]
    private ?float $logoRatio = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $facadeColor = '#2A2A2A'; // Couleur de la façade (texte/logo)

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $sideColor = '#E8E8E8'; // Couleur des côtés (tranches)



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getLargeur(): ?int
    {
        return $this->largeur;
    }

    public function setLargeur(int $largeur): static
    {
        $this->largeur = $largeur;

        return $this;
    }

    public function getHauteur(): ?int
    {
        return $this->hauteur;
    }

    public function setHauteur(int $hauteur): static
    {
        $this->hauteur = $hauteur;

        return $this;
    }

    public function getTypeEcriture(): ?string
    {
        return $this->typeEcriture;
    }

    public function setTypeEcriture(string $typeEcriture): static
    {
        $this->typeEcriture = $typeEcriture;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getModeleName(): ?string
    {
        return $this->modeleName;
    }

    public function setModeleName(?string $modeleName): static
    {
        $this->modeleName = $modeleName;

        return $this;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): static
    {
        $this->mode = $mode;
        return $this;
    }

    public function getLogoData(): ?string
    {
        return $this->logoData;
    }

    public function setLogoData(?string $logoData): static
    {
        $this->logoData = $logoData;
        return $this;
    }

    public function getLogoFileName(): ?string
    {
        return $this->logoFileName;
    }

    public function setLogoFileName(?string $logoFileName): static
    {
        $this->logoFileName = $logoFileName;
        return $this;
    }

    public function getLogoRatio(): ?float
    {
        return $this->logoRatio;
    }

    public function setLogoRatio(?float $logoRatio): static
    {
        $this->logoRatio = $logoRatio;
        return $this;
    }

    public function isLogoMode(): bool
    {
        return $this->mode === 'logo';
    }

    public function isTextMode(): bool
    {
        return $this->mode === 'text' || $this->mode === null;
    }

    public function getFacadeColor(): ?string
    {
        return $this->facadeColor;
    }

    public function setFacadeColor(?string $facadeColor): static
    {
        $this->facadeColor = $facadeColor;
        return $this;
    }

    public function getSideColor(): ?string
    {
        return $this->sideColor;
    }

    public function setSideColor(?string $sideColor): static
    {
        $this->sideColor = $sideColor;
        return $this;
    }

    public function getBasePrice(): ?float
    {
        return $this->basePrice;
    }

    public function setBasePrice(?float $basePrice): static
    {
        $this->basePrice = $basePrice;
        return $this;
    }

    public function getPricingFormula(): ?string
    {
        return $this->pricingFormula;
    }

    public function setPricingFormula(?string $pricingFormula): static
    {
        $this->pricingFormula = $pricingFormula;
        return $this;
    }

    public function getPricingFactors(): ?array
    {
        return $this->pricingFactors;
    }

    public function setPricingFactors(?array $pricingFactors): static
    {
        $this->pricingFactors = $pricingFactors;
        return $this;
    }

    public function isUseCustomPricing(): bool
    {
        return $this->useCustomPricing;
    }

    public function setUseCustomPricing(bool $useCustomPricing): static
    {
        $this->useCustomPricing = $useCustomPricing;
        return $this;
    }

    public function getPricePerUnit(): ?float
    {
        return $this->pricePerUnit;
    }

    public function setPricePerUnit(?float $pricePerUnit): static
    {
        $this->pricePerUnit = $pricePerUnit;
        return $this;
    }

    public function getPricingUnit(): ?string
    {
        return $this->pricingUnit;
    }

    public function setPricingUnit(?string $pricingUnit): static
    {
        $this->pricingUnit = $pricingUnit;
        return $this;
    }

    /**
     * Calcule le prix basé sur les dimensions et la formule personnalisée
     */
    public function calculatePrice(?int $width = null, ?int $height = null): float
    {
        $width = $width ?? $this->largeur ?? 0;
        $height = $height ?? $this->hauteur ?? 0;

        if (!$this->useCustomPricing || !$this->pricingFormula) {
            return $this->basePrice ?? 0.0;
        }

        // Variables disponibles pour la formule
        $variables = [
            'width' => $width,
            'height' => $height,
            'largeur' => $width,
            'hauteur' => $height,
            'surface' => $width * $height,
            'perimeter' => 2 * ($width + $height),
            'perimetre' => 2 * ($width + $height),
            'base_price' => $this->basePrice ?? 0,
            'price_per_unit' => $this->pricePerUnit ?? 0,
        ];

        // Ajouter les facteurs personnalisés
        if ($this->pricingFactors) {
            foreach ($this->pricingFactors as $factor => $value) {
                $variables[$factor] = $value;
            }
        }

        return $this->evaluateFormula($this->pricingFormula, $variables);
    }

    /**
     * Évalue une formule mathématique simple avec des variables
     */
    private function evaluateFormula(string $formula, array $variables): float
    {
        // Remplacer les variables dans la formule
        foreach ($variables as $var => $value) {
            $formula = str_replace('{' . $var . '}', (string)$value, $formula);
        }

        // Sécuriser la formule (autoriser seulement les opérations mathématiques de base)
        $formula = preg_replace('/[^0-9+\-*\/\(\)\.\s]/', '', $formula);
        
        try {
            // Évaluer la formule de manière sécurisée
            $result = eval("return $formula;");
            return is_numeric($result) ? (float)$result : 0.0;
        } catch (Exception $e) {
            return $this->basePrice ?? 0.0;
        }
    }

    /**
     * Obtient le prix formaté avec la devise
     */
    public function getFormattedPrice(?int $width = null, ?int $height = null): string
    {
        $price = $this->calculatePrice($width, $height);
        return number_format($price, 2, ',', ' ') . ' €';
    }
}
