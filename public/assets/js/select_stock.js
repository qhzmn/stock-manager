document.querySelectorAll('tr[data-ref]').forEach(row => {
    row.addEventListener('click', () => {
        const ref = row.getAttribute('data-ref');

        fetch('traitement.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ref: ref })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Réponse du serveur PHP :', data);
            alert("Produit sélectionné : " + data.nom); // par exemple
        })
        .catch(error => {
            console.error('Erreur AJAX :', error);
        });
    });
});