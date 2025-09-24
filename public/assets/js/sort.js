document.addEventListener("DOMContentLoaded", () => {
    const dropbtn = document.querySelector(".dropbtn");
    const dropdown = document.querySelector(".dropdown-content");

    if (dropbtn && dropdown) {
        dropbtn.addEventListener("click", (e) => {
            e.stopPropagation();
            dropdown.classList.toggle("show");
        });

        // Fermer si clic ailleurs
        document.addEventListener("click", () => {
            dropdown.classList.remove("show");
        });
    }
});

