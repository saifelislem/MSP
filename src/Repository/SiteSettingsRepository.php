<?php

namespace App\Repository;

use App\Entity\SiteSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteSettings>
 */
class SiteSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteSettings::class);
    }

    /**
     * Récupère une valeur de paramètre par sa clé
     */
    public function getSettingValue(string $key, mixed $default = null): mixed
    {
        $setting = $this->findOneBy(['settingKey' => $key]);
        
        if (!$setting) {
            return $default;
        }

        return $setting->getFormattedValue();
    }

    /**
     * Définit une valeur de paramètre
     */
    public function setSetting(string $key, mixed $value, string $category = 'general', string $description = '', string $type = 'text'): SiteSettings
    {
        $setting = $this->findOneBy(['settingKey' => $key]);
        
        if (!$setting) {
            $setting = new SiteSettings();
            $setting->setSettingKey($key);
            $setting->setCategory($category);
            $setting->setDescription($description);
            $setting->setType($type);
        }

        $setting->setSettingValue((string)$value);
        
        $this->getEntityManager()->persist($setting);
        $this->getEntityManager()->flush();

        return $setting;
    }

    /**
     * Récupère tous les paramètres par catégorie
     */
    public function getSettingsByCategory(string $category): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.category = :category')
            ->setParameter('category', $category)
            ->orderBy('s.settingKey', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère toutes les catégories
     */
    public function getAllCategories(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('DISTINCT s.category')
            ->where('s.category IS NOT NULL')
            ->orderBy('s.category', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'category');
    }

    /**
     * Récupère tous les paramètres sous forme de tableau clé-valeur
     */
    public function getAllSettingsAsArray(): array
    {
        $settings = $this->findAll();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->getSettingKey()] = $setting->getFormattedValue();
        }

        return $result;
    }
}