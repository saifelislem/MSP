<?php

namespace App\Entity;

use App\Repository\ColorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ColorRepository::class)]
class Color
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 7)]
    private ?string $hexCode = null;

    #[ORM\Column(length: 10)]
    private ?string $emoji = null;

    #[ORM\Column]
    private ?int $stock = 0;

    #[ORM\Column]
    private ?int $minStock = 5; // Seuil d'alerte stock faible

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\Column(length: 20)]
    private ?string $type = 'both'; // 'facade', 'side', 'both'

    #[ORM\Column]
    private ?int $sortOrder = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getHexCode(): ?string
    {
        return $this->hexCode;
    }

    public function setHexCode(string $hexCode): static
    {
        $this->hexCode = $hexCode;
        return $this;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(string $emoji): static
    {
        $this->emoji = $emoji;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getMinStock(): ?int
    {
        return $this->minStock;
    }

    public function setMinStock(int $minStock): static
    {
        $this->minStock = $minStock;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Méthodes utilitaires
    public function isInStock(): bool
    {
        return $this->stock > 0 && $this->isActive;
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minStock && $this->stock > 0;
    }

    public function canBeUsedForFacade(): bool
    {
        return in_array($this->type, ['facade', 'both']) && $this->isInStock();
    }

    public function canBeUsedForSide(): bool
    {
        return in_array($this->type, ['side', 'both']) && $this->isInStock();
    }

    public function getDisplayName(): string
    {
        return $this->emoji . ' ' . $this->name;
    }

    public function decreaseStock(int $quantity = 1): void
    {
        $this->stock = max(0, $this->stock - $quantity);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function increaseStock(int $quantity = 1): void
    {
        $this->stock += $quantity;
        $this->updatedAt = new \DateTimeImmutable();
    }
}