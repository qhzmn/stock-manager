// manage the case 'select-all' / 'deselect all'

document.getElementById('select-all').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('input[name="selected_products[]"]');
        for (let checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });