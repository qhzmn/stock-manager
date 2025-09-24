document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector(".form-container");
    const qtyInputs = document.querySelectorAll(".qty-input");
    const comment = document.getElementById("comment");
    const tableError = document.getElementById("tableError");
    const commentError = document.getElementById("commentError");


    

    form.addEventListener("submit", function (e) {
        let valid = true;
        let hasPositiveQuantity = false;

        // Réinitialise les messages d'erreur
        tableError.textContent = "";
        commentError.textContent = "";

        qtyInputs.forEach(input => {
            const value = parseInt(input.value, 10);
            const max = input.max ? parseInt(input.max, 10) : null;

            if (!isNaN(value) && value > 0) {
                hasPositiveQuantity = true;
            }

            if (isNaN(value) || value < 0) {
                valid = false;
                tableError.textContent = "Quantities must be positive numbers.";
            } else if (max !== null && value > max) {
                valid = false;
                tableError.textContent = `Quantity cannot exceed available stock (${max}).`;
            }
        });


       




        if (!hasPositiveQuantity) {
            valid = false;
            tableError.textContent = "You must select at least one product with quantity > 0.";
        }

        // Vérification du commentaire
        if (comment.value.trim().length > 255) {
            valid = false;
            commentError.textContent = "Comment cannot exceed 255 characters.";
        }

        if (!valid) {
            e.preventDefault(); // Stop form submit
        }

    
    });
});
