document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggleContentBtn');
    const mainContent = document.getElementById('mainContent');
    const mainContainer = document.getElementById('mainContainer');

    toggleButton.addEventListener('click', function () {
        // Comprobamos si el contenedor ya tiene la clase 'visible'
        if (mainContainer.classList.contains('visible')) {
            // Si la clase 'visible' está presente, volvemos al estado inicial
            mainContainer.classList.remove('visible');
            mainContent.classList.remove('hidden');
            toggleButton.classList.remove('translateButton'); // Quitar el desplazamiento del botón
        } else {
            // Si no tiene la clase 'visible', aplicamos las clases para ocultar
            mainContainer.classList.add('visible');
            mainContent.classList.add('hidden');
            toggleButton.classList.add('translateButton'); // Desplazar el botón
        }
    });
});
