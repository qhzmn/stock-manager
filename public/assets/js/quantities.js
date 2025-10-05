// manage the quantities for stock entry and exit

document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.querySelector("tbody[data-mode]");
    const mode = tbody.dataset.mode;
    document.querySelectorAll(".qty-input").forEach(input => {
        input.addEventListener("input", () => {
            const row = input.closest("tr");
            const stockSpan = row.querySelector(".stock-total");
            const baseStock = parseInt(stockSpan.dataset.base, 10) || 0;
            const qty = parseInt(input.value, 10) || 0;
            
            if (mode === "exit") {
                stockSpan.textContent = baseStock - qty;
            } else {
                stockSpan.textContent = baseStock + qty;
            }
        });
    });
});




