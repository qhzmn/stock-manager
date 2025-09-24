
document.addEventListener("DOMContentLoaded", () => {
    let chartInstance = null; // pour détruire l'ancien graphique

    document.querySelectorAll(".clickable-row").forEach(row => {
        row.addEventListener("click", function() {
            const productId = this.dataset.id;
            const url = "/product/stats?id_product=" + productId;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    console.log("JSON reçu :", data);

                    // Affiche le modal
                    const modal = new bootstrap.Modal(document.getElementById('chartModal'));
                    modal.show();

                    // Si un graphique existe déjà, on le détruit avant d'en créer un nouveau
                    if (chartInstance) {
                        chartInstance.destroy();
                    }

                    const ctx = document.getElementById('myChart').getContext('2d');
                    chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: "Purchase Price",
                                    data: data.datasets[0].data,
                                    borderColor: "blue",
                                    backgroundColor: "blue",
                                    yAxisID: 'yPrices'
                                },
                                {
                                    label: "Selling Price",
                                    data: data.datasets[1].data,
                                    borderColor: "green",
                                    backgroundColor: "green",
                                    yAxisID: 'yPrices'
                                },
                                {
                                    label: "Quantity",
                                    data: data.datasets[2].data,
                                    borderColor: "red",
                                    backgroundColor: "red",
                                    yAxisID: 'yQuantity'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: { position: 'top' }
                            },
                            scales: {
                                yPrices: {
                                    type: 'linear',
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Prix (€)'
                                    }
                                },
                                yQuantity: {
                                    type: 'linear',
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Quantité'
                                    },
                                    grid: {
                                        drawOnChartArea: false // évite que les lignes de grille se superposent
                                    }
                                }
                            }
                        }
                    });

                })
                .catch(err => console.error("Erreur fetch :", err));
        });
    });
});
