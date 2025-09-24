const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const actionInput = document.getElementById('actionInput');

const initialValue = searchInput.value; // valeur au chargement

searchInput.addEventListener('input', () => {

    if (searchInput.value === '' || searchInput.value === initialValue) {
        searchBtn.textContent = '✖️'; // affiche la croix si vide ou inchangé
        actionInput.value = 'reset';
    } else {
        searchBtn.textContent = '🔍'; // affiche la loupe si modifié
        actionInput.value = 'search';
    }
});
