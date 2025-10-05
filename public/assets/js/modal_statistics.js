// modal for product statistics 

document.querySelectorAll('.open-text-modal').forEach(button => {
  button.addEventListener('click', function() {
    const id = this.getAttribute('data-id');
    fetch(`/statistic/resume/recap?key=${id}`)
      .then(response => {
        const contentType = response.headers.get('Content-Type');
        if (contentType.includes('application/json')) {
          return response.json();
        } else {
          return response.text();
        }
      })
      .then(data => {
    const modalTitle = document.getElementById('titleContent');
    const modalContent = document.getElementById('modalContent');

    if (typeof data === 'object') {
        modalTitle.innerHTML = data.title || 'Statistiques produit';
        modalContent.innerHTML = `
            <div class="stats-container">
                <div class="stat-card">
                    <h6>Produit le plus vendu</h6>
                    <p>ID: ${data.top_selling.id_product}</p>
                    <p>Niveau: ${data.top_selling.level}</p>
                </div>
                <hr>
                <div class="stat-card">
                    <h6>Produit le moins vendu</h6>
                    <p>ID: ${data.least_selling.id_product}</p>
                    <p>Niveau: ${data.least_selling.level}</p>
                </div>
                <hr>
                <div class="stat-card">
                    <h6>Stock faible</h6>
                    <p>ID: ${data.low_stock.id_product}</p>
                    <p>Niveau: ${data.low_stock.level}</p>
                </div>
                <hr>
                <div class="stat-card">
                    <h6>Rupture de stock</h6>
                    <p>ID: ${data.out_of_stock.id_product}</p>
                    <p>Niveau: ${data.out_of_stock.level}</p>
                </div>
            </div>
        `;
    } else {
        modalTitle.innerHTML = 'Contenu';
        modalContent.innerHTML = data;
    }

    const modal = new bootstrap.Modal(document.getElementById('dynamicModal'));
    modal.show();
})

  });
});
