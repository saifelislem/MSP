<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Le nom doit contenir au moins 2 caractères')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
    private ?string $street = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
    #[Assert\Regex(pattern: '/^\d{5}$/', message: 'Le code postal doit contenir 5 chiffres')]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire')]
    private ?string $city = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le pays est obligatoire')]
    private ?string $country = 'France';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $additionalInfo = null;

    #[ORM\Column]
    private ?bool $isDefault = false;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Customer $customer = null;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'billingAddress')]
    private Collection $ordersAsBilling;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'shippingAddress')]
    private Collection $ordersAsShipping;

    public function __construct()
    {
        $this->ordersAsBilling = new ArrayCollection();
        $this->ordersAsShipping = new ArrayCollection();
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;
        return $this;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additionalInfo;
    }

    public function setAdditionalInfo(?string $additionalInfo): static
    {
        $this->additionalInfo = $additionalInfo;
        return $this;
    }

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;
        return $this;
    }

    public function getOrdersAsBilling(): Collection
    {
        return $this->ordersAsBilling;
    }

    public function addOrderAsBilling(Order $order): static
    {
        if (!$this->ordersAsBilling->contains($order)) {
            $this->ordersAsBilling->add($order);
            $order->setBillingAddress($this);
        }
        return $this;
    }

    public function removeOrderAsBilling(Order $order): static
    {
        if ($this->ordersAsBilling->removeElement($order)) {
            if ($order->getBillingAddress() === $this) {
                $order->setBillingAddress(null);
            }
        }
        return $this;
    }

    public function getOrdersAsShipping(): Collection
    {
        return $this->ordersAsShipping;
    }

    public function addOrderAsShipping(Order $order): static
    {
        if (!$this->ordersAsShipping->contains($order)) {
            $this->ordersAsShipping->add($order);
            $order->setShippingAddress($this);
        }
        return $this;
    }

    public function removeOrderAsShipping(Order $order): static
    {
        if ($this->ordersAsShipping->removeElement($order)) {
            if ($order->getShippingAddress() === $this) {
                $order->setShippingAddress(null);
            }
        }
        return $this;
    }

    public function getFullAddress(): string
    {
        $address = $this->street;
        if ($this->additionalInfo) {
            $address .= ', ' . $this->additionalInfo;
        }
        $address .= ', ' . $this->postalCode . ' ' . $this->city;
        if ($this->country !== 'France') {
            $address .= ', ' . $this->country;
        }
        return $address;
    }

    public function __toString(): string
    {
        return $this->name . ' - ' . $this->getFullAddress();
    }
}