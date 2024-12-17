document.addEventListener("DOMContentLoaded", function () {
    const searchButtonContainer = document.querySelector('.search-button-container');
    const searchButton = document.querySelector('.search-button');
    const searchBar = document.querySelector('.searchBar');
    const searchInput = document.querySelector('.search-input');

    // Inicialmente ocultar la barra con un transform fuera de la pantalla (100px a la derecha)
    searchBar.style.display = 'none'; // La barra está oculta al inicio
    searchBar.style.transform = 'translateX(100px)'; // Iniciar 100px a la derecha
    searchBar.style.opacity = '0';

    // Definir la animación de aparición
    const appearAnimation = `
        @keyframes appearFromLeft {
            0% {
                transform: translateX(100px); /* Empieza desde 100px más a la derecha */
                opacity: 0;
            }
            100% {
                transform: translateX(0); /* Llega a la posición original */
                opacity: 1;
            }
        }
    `;
    const styleSheet = document.createElement("style");
    styleSheet.type = "text/css";
    styleSheet.innerText = appearAnimation;
    document.head.appendChild(styleSheet);

    // Mostrar la barra de búsqueda al hacer clic en el botón
    searchButton.addEventListener('click', function () {
        searchButtonContainer.style.opacity = '0';
        searchButtonContainer.style.visibility = 'hidden';
        searchButtonContainer.style.transition = 'opacity 0.4s ease-in-out, visibility 0.4s ease-in-out';

        setTimeout(() => {
            searchButtonContainer.style.display = 'none';
            searchBar.style.display = 'block';
            searchBar.style.animation = 'appearFromLeft 0.4s ease-in-out forwards';
        }, 400); // Coincide con la duración de la transición

        searchInput.focus();
    });

    // Ocultar la barra de búsqueda si el campo queda vacío y pierde el foco
    searchInput.addEventListener('blur', function () {
        if (!searchInput.value.trim()) {
            searchBar.style.animation = 'none';  // Detenemos cualquier animación anterior
            searchBar.style.opacity = '0';
            searchBar.style.transform = 'translateX(100px)'; // Vuelve a 100px a la derecha

            setTimeout(() => {
                searchBar.style.display = 'none';
                searchButtonContainer.style.display = 'flex';
                searchButtonContainer.style.opacity = '1';
                searchButtonContainer.style.visibility = 'visible';
            }, 400); // Coincide con la duración de la animación
        }
    });
});
