// Fonctions pour gérer le panier et le modal de produit

// Mise à jour du compteur du panier
function updateCartCounter(count) {
    const cartCounters = document.querySelectorAll('[data-notify]');
    cartCounters.forEach(counter => {
        counter.setAttribute('data-notify', count || 0);
    });
}

// Charger le compteur du panier au chargement de la page
function loadCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            updateCartCounter(data.count);
        })
        .catch(error => console.log('Erreur chargement panier:', error));
}

// Fonction pour mettre à jour le badge du panier (avec produits en attente)
function updateCartBadge() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const currentCount = data.count || 0;
            // Compter tous les produits en attente
            const pendingProducts = JSON.parse(sessionStorage.getItem('pendingProducts') || '[]');
            const totalCount = currentCount + pendingProducts.length;
            
            // Mettre à jour tous les badges du panier
            const badges = document.querySelectorAll('[data-notify]');
            badges.forEach(badge => {
                badge.setAttribute('data-notify', totalCount);
            });
        })
        .catch(error => {
            console.error('Erreur lors de la mise à jour du compteur:', error);
        });
}

// Afficher une notification
function showNotification(title, message, type = 'success') {
    if (typeof swal !== 'undefined') {
        swal(title, message, type);
    } else {
        alert(message);
    }
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    updateCartBadge();
    
    // Gestion des boutons +/- pour la quantité
    const btnNumProductDown = document.querySelectorAll('.btn-num-product-down');
    const btnNumProductUp = document.querySelectorAll('.btn-num-product-up');
    
    btnNumProductDown.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.num-product');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });
    });
    
    btnNumProductUp.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.num-product');
            let value = parseInt(input.value);
            input.value = value + 1;
        });
    });
});
