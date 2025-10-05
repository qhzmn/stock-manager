// manage the search barre
 
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const actionInput = document.getElementById('actionInput');

const initialValue = searchInput.value; 

searchInput.addEventListener('input', () => {

    if (searchInput.value === '' || searchInput.value === initialValue) {
        searchBtn.textContent = '✖️'; 
        actionInput.value = 'reset';
    } else {
        searchBtn.textContent = '🔍';
        actionInput.value = 'search';
    }
});
