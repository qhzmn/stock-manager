function selectProducts(callback = "addAlert") {
    console.log('fonction select products');
    //sessionStorage.setItem('alert_name', document.getElementById('name').value);
    //sessionStorage.setItem('alert_level', document.getElementById('level').value);
    window.location.href = "/product/select?callback=" + encodeURIComponent(callback);
}

// Au retour sur la page du formulaire
document.addEventListener('DOMContentLoaded', () => {
    const savedName = sessionStorage.getItem('alert_name');
    const savedLevel = sessionStorage.getItem('alert_level');
    if (savedName) document.getElementById('name').value = savedName;
    if (savedLevel) document.getElementById('level').value = savedLevel;
});