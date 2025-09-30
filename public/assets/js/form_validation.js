document.addEventListener("DOMContentLoaded", function () {
    const forms = document.querySelectorAll("form");

    forms.forEach(form => {
        form.addEventListener("submit", function (e) {
            let errors = false;

            // Vérifie chaque input/textarea/select
            const inputs = form.querySelectorAll("input, textarea, select");
            inputs.forEach(input => {

                const errorDiv = document.getElementById(input.name + "Error");
                if (!errorDiv) return;
                errorDiv.textContent = "";

                if (input.hasAttribute("required") && !input.value.trim()) {
                    errorDiv.textContent = "Ce champ est obligatoire.";
                    errors = true;
                    return;
                }

                if ((input.name === "email") && input.value) {
                    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!regexEmail.test(input.value) || input.value.length > 100) {
                        errorDiv.textContent = "Invalid email address.";
                        errors = true;
                    }
                }

                if ((input.name === "first_name") && input.value) {
                    const regexName = /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/;
                    if (!regexName.test(input.value)) {
                        errorDiv.textContent = "This field must contain only letters.";
                        errors = true;
                    }else if (input.value.length > 50){
                        errorDiv.textContent = "The field must contain fewer than 50 characters.";
                        errors = true;
                    }
                }
                if ((input.name === "last_name") && input.value) {
                    const regexName = /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/;
                    if (!regexName.test(input.value)) {
                        errorDiv.textContent = "This field must contain only letters.";
                        errors = true;
                    }else if (input.value.length > 50){
                        errorDiv.textContent = "The field must contain fewer than 50 characters.";
                        errors = true;        
                    }   
                }

                if (input.name === "sku" && input.value) {
                    if (input.value.length < 3 || input.value.length > 20) {
                        errorDiv.textContent = "The SKU must contain between 3 and 20 characters.";
                        errors = true;
                    }
                }
                if (input.name === "name" && input.value) {
                    if (input.value.length < 3 || input.value.length > 50) {
                        errorDiv.textContent = "The name must contain between 3 and 50 characters.";
                        errors = true;
                    }
                }
                if (input.name === "description" && input.value) {
                    if (input.value.length > 255) {
                        errorDiv.textContent = "The description cannot exceed 255 characters.";
                        errors = true;
                    }
                }
                if (input.name === "category" && input.value) {
                    if (input.value.length > 50) {
                        errorDiv.textContent = "The category cannot exceed 50 characters.";
                        errors = true;
                    }
                }
                if ((input.name === "quantity" || input.name === "purchase" || input.name === "selling") && input.value) {
                    const num = parseFloat(input.value);
                    if (isNaN(num) || num < 0) {
                        errorDiv.textContent = "This field must be a positive number.";
                        errors = true;
                    }
                }
                if (input.name === "password" && input.value) {
                    if (input.value.length < 8 || input.value.length > 50) {
                        errorDiv.textContent = "The password must contain between 3 and 50 characters.";
                        errors = true;
                    }
                }
                if (input.tagName === "SELECT" && input.value) {
                    const validOptions = ["manager", "user", "guest"];
                    if (!validOptions.includes(input.value)) {
                        errorDiv.textContent = "Please select a valid option.";
                        errors = true;
                    }
                }

                
    
                
            });
            const currentPass = form.querySelector("[name='current_password']");
            const newPass1 = form.querySelector("[name='new_password1']");
            const newPass2 = form.querySelector("[name='new_password2']");
            const errorDiv = document.getElementById("new_password2Error");

            if (currentPass && newPass1 && newPass2) {
                // Vérifie si au moins un champ est rempli
                if (currentPass.value || newPass1.value || newPass2.value) {
                    if (!currentPass.value || !newPass1.value || !newPass2.value) {
                        errorDiv.textContent = "All password fields must be completed.";
                        errors = true;
                    } else if (currentPass.value.length < 8 || currentPass.value.length > 50){
                        errorDiv.textContent = "The password must becontain between 8 and 50 characters.";
                        errors = true;
                    } else if (newPass1.value.length < 8 || newPass1.value.length > 50){
                        errorDiv.textContent = "The password must becontain between 8 and 50 characters.";
                        errors = true;
                    } else if (newPass2.value.length < 8 || newPass2.value.length > 50){
                        errorDiv.textContent = "The password must becontain between 8 and 50 characters.";
                        errors = true;
                    } else if (newPass1.value !== newPass2.value) {
                        errorDiv.textContent = "The new passwords do not match.";
                        errors = true;
                    }
                }
            }

            


            if (errors) {
                e.preventDefault();
            }

        });
    });
});
