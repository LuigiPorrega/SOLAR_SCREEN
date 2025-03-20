// Espera 3 segundos y luego elimina las alertas
window.addEventListener('DOMContentLoaded', (event) => {
    setTimeout(function() {
        // Eliminar las alertas después de 3 segundos
        let successAlert = document.querySelector('.alert-success');
        let errorAlert = document.querySelector('.alert-danger');

        if (successAlert) {
            successAlert.style.display = 'none';
        }

        if (errorAlert) {
            errorAlert.style.display = 'none';
        }
    }, 3000); // 3000ms = 3 segundos
});
