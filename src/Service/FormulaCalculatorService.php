<?php

namespace App\Service;

use App\Entity\Modele;

class FormulaCalculatorService
{
    /**
     * Calcule le prix selon la formule définie dans le modèle
     */
    public function calculatePrice(Modele $modele, array $parameters): float
    {
        $formule = $modele->getFormuleCalcul();
        $prixBase = $modele->getPrixBase();
        
        // Si pas de formule, retourner le prix de base
        if (empty($formule)) {
            return $prixBase;
        }
        
        // Variables disponibles pour la formule
        $variables = [
            'largeur' => $parameters['largeur'] ?? 0,
            'hauteur' => $parameters['hauteur'] ?? 0,
            'prixBase' => $prixBase,
            'quantite' => $parameters['quantite'] ?? 1
        ];
        
        try {
            // Remplacer les variables dans la formule
            $formuleCalculee = $this->replaceVariables($formule, $variables);
            
            // Évaluer la formule mathématique de manière sécurisée
            $result = $this->evaluateFormula($formuleCalculee);
            
            return max(0, round($result, 2)); // Prix minimum 0, arrondi à 2 décimales
            
        } catch (\Exception $e) {
            // En cas d'erreur, retourner le prix de base
            return $prixBase;
        }
    }
    
    /**
     * Remplace les variables dans la formule
     */
    private function replaceVariables(string $formule, array $variables): string
    {
        foreach ($variables as $variable => $valeur) {
            $formule = str_replace($variable, (string)$valeur, $formule);
        }
        
        return $formule;
    }
    
    /**
     * Évalue une formule mathématique de manière sécurisée
     */
    private function evaluateFormula(string $formule): float
    {
        // Nettoyer la formule - autoriser seulement les caractères mathématiques sûrs
        $formule = preg_replace('/[^0-9+\-*\/\(\)\.\s]/', '', $formule);
        
        // Vérifier que la formule n'est pas vide après nettoyage
        if (empty(trim($formule))) {
            throw new \InvalidArgumentException('Formule invalide');
        }
        
        // Évaluer la formule
        $result = eval("return $formule;");
        
        if (!is_numeric($result)) {
            throw new \InvalidArgumentException('Résultat de formule non numérique');
        }
        
        return (float)$result;
    }
    
    /**
     * Valide une formule avant sauvegarde
     */
    public function validateFormula(string $formule): bool
    {
        if (empty($formule)) {
            return true; // Formule vide = valide (utilise prix de base)
        }
        
        try {
            // Test avec des valeurs par défaut
            $testVariables = [
                'largeur' => 10,
                'hauteur' => 5,
                'prixBase' => 20,
                'quantite' => 1
            ];
            
            $formuleTest = $this->replaceVariables($formule, $testVariables);
            $this->evaluateFormula($formuleTest);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}