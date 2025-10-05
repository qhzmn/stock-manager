// manage the validation of login form

document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("loginForm");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");

    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");

    form.addEventListener("submit", function (e) {
        let valid = true;

        emailError.textContent = "";
        passwordError.textContent = "";

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            emailError.textContent = "Adresse e-mail invalide.";
            valid = false;
        }

        if (password.length < 6) {
            passwordError.textContent = "Le mot de passe doit contenir au moins 6 caractères.";
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});
