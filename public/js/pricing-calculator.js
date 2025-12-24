// Système de calcul de prix en temps réel
class PricingCalculator {
    constructor() {
        this.currentProductId = null;
        this.debounceTimer = null;
        this.init();
    }

    init() {
        // Écouter les changements sur les champs de dimensions
        document.addEventListener('input', (e) => {
            if (e.target.matches('#modal-product-hauteur, #modal-product-largeur, #modal-product-quantity')) {
                this.debouncedCalculate();
            }
        });

        // Écouter les changements de quantité avec les boutons +/-
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-num-product-down, .btn-num-product-up')) {
                setTimeout(() => this.debouncedCalculate(), 100);
            }
        });
    }

    setProduct(productId) {
        this.currentProductId = productId;
        this.calculatePrice();
    }

    debouncedCalculate() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.calculatePrice();
        }, 300);
    }

    async calculatePrice() {
        if (!this.currentProductId) return;

        const dimensions = this.getDimensions();
        const priceElement = document.getElementById('calculated-price');
        const breakdownElement = document.getElementById('price-breakdown');

        // Afficher un indicateur de calcul
        if (priceElement) {
            priceElement.innerHTML = '<i class="zmdi zmdi-refresh zmdi-hc-spin"></i> Calcul...';
        }

        try {
            const response = await fetch(`/api/pricing/calculate/${this.currentProductId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    dimensions: dimensions
                })
            });

            if (!response.ok) {
                throw new Error('Erreur de calcul');
            }

            const data = await response.json();

            // Mettre à jour l'affichage du prix
            if (priceElement) {
                priceElement.textContent = data.formatted_price;
            }

            // Afficher le détail du calcul si disponible
            if (breakdownElement && data.use_custom_pricing) {
                let breakdown = '';
                
                if (data.base_price > 0) {
                    breakdown += `Prix de base: ${this.formatPrice(data.base_price)} €`;
                }

                if (dimensions.length > 0 || dimensions.width > 0) {
                    const surface = (dimensions.length || 0) * (dimensions.width || 0);
                    if (surface > 0) {
                        breakdown += `${breakdown ? ' + ' : ''}Surface: ${surface.toFixed(1)} ${data.pricing_unit}²`;
                    }
                }

                if (dimensions.quantity > 1) {
                    breakdown += `${breakdown ? ' × ' : ''}Quantité: ${dimensions.quantity}`;
                }

                breakdownElement.textContent = breakdown;
            }

        } catch (error) {
            console.error('Erreur lors du calcul du prix:', error);
            if (priceElement) {
                priceElement.textContent = 'Erreur de calcul';
            }
        }
    }

    getDimensions() {
        const hauteurInput = document.getElementById('modal-product-hauteur');
        const largeurInput = document.getElementById('modal-product-largeur');
        const quantityInput = document.getElementById('modal-product-quantity');

        return {
            length: parseFloat(largeurInput?.value || 0),
            width: parseFloat(hauteurInput?.value || 0),
            height: 0.5, // Épaisseur fixe de 5mm
            quantity: parseInt(quantityInput?.value || 1)
        };
    }

    formatPrice(price) {
        return parseFloat(price).toFixed(2).replace('.', ',');
    }
}

// Initialiser le calculateur de prix
const pricingCalculator = new PricingCalculator();

// Fonction globale pour définir le produit actuel
window.setPricingProduct = function(productId) {
    pricingCalculator.setProduct(productId);
};

// Fonction pour calculer manuellement (si nécessaire)
window.calculatePrice = function() {
    pricingCalculator.calculatePrice();
};