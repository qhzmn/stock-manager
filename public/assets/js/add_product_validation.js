document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("productForm");
    
    const skuInput = document.getElementById("SKU");
    const nameInput = document.getElementById("name");
    const descriptionInput = document.getElementById("description");
    const quantityInput = document.getElementById("quantity");
    const purchaseInput = document.getElementById("purchase");
    const sellingInput = document.getElementById("selling");
    const categoryInput = document.getElementById("category");

    const skuError = document.getElementById("skuError");
    const nameError = document.getElementById("nameError");
    const descriptionError = document.getElementById("descriptionError");
    const quantityError = document.getElementById("quantityError");
    const purchaseError = document.getElementById("purchaseError");
    const sellingError = document.getElementById("sellingError");
    const categoryError = document.getElementById("categoryError");

    form.addEventListener("submit", function (e) {
        let valid = true;

        // Réinitialise les messages d'erreur
        skuError.textContent = "";
        nameError.textContent = "";
        descriptionError.textContent = "";
        quantityError.textContent = "";
        purchaseError.textContent = "";
        sellingError.textContent = "";
        categoryError.textContent = "";

        const sku = skuInput.value.trim();
        const name = nameInput.value.trim();
        const description = descriptionInput.value.trim();
        const quantity = quantityInput.value.trim();
        const purchase = purchaseInput.value.trim();
        const selling = sellingInput.value.trim();
        const category = categoryInput.value.trim();




        // Vérification du SKU
        if (sku === "") {
            skuError.textContent = "The SKU is required.";
            valid = false;
        } else if (!/^[a-zA-Z0-9]{3,20}$/.test(sku)) {
            skuError.textContent = "The SKU must contain between 3 and 20 characters.";
            valid = false;
        }
        // Vérification du nom
        if (name === "") {
            nameError.textContent = "The name is required.";
            valid = false;
        } else if (!/^[\p{L}\d\s-]{3,50}$/u.test(name)) {
            nameError.textContent = "The name mus contain between 3 and 50 characters.";
            valid = false;
        }

        // Vérification de la description (facultative mais limiter la longueur)
        if (description.length > 255) {
            descriptionError.textContent = "The description cannot exceed 255 characters.";
            valid = false;
        }

        // Vérification de la quantité
        if (isNaN(quantity) || quantity < 0) {
            quantityError.textContent = "The quantity must be a positive number.";
            valid = false;
        }

        // Vérification du prix d'achat
        if (isNaN(purchase) || purchase < 0) {
            purchaseError.textContent = "The purchase price must be a positive number.";
            valid = false;
        }

        // Vérification du prix de vente
        if (isNaN(selling) || selling < 0) {
            sellingError.textContent = "The selling price must be a positive number.";
            valid = false;
        }

        // Vérification de la catégorie
        if (!/^[\p{L}\d\s-]{0,50}$/u.test(category)) {
            categoryError.textContent = "The category cannot exceed 50 characters.";
            valid = false;
        }


        if (!valid) {
            e.preventDefault(); // Empêche l'envoi du formulaire
        }
    });
});
