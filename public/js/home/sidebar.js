// Asegúrate de incluir Color Thief: https://lokeshdhakar.com/projects/color-thief/
// Puedes usar un CDN para incluirlo en tu proyecto, por ejemplo:
// <script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.2/color-thief.umd.js"></script>

document.addEventListener('DOMContentLoaded', () => {
    // Obtiene la sección con fondo y la imagen del fondo
    const sideProfileSection = document.querySelector('.sideprofile-section');
    const backgroundImageUrl = sideProfileSection.style.backgroundImage;

    if (backgroundImageUrl && backgroundImageUrl.includes('url(')) {
        // Extrae la URL de la imagen
        const imageUrl = backgroundImageUrl.slice(5, -2); // Quita `url("...")`

        // Crea un objeto de imagen para cargar la URL
        const image = new Image();
        image.crossOrigin = "Anonymous"; // Habilitar CORS para evitar problemas
        image.src = imageUrl;

        // Una vez cargada la imagen, extraer los colores
        image.onload = () => {
            const colorThief = new ColorThief();
            const colors = colorThief.getPalette(image, 3); // Obtiene los 3 colores principales

            if (colors && colors.length >= 3) {
                // Convierte los colores a formato RGB
                const [color1, color2, color3] = colors.map(color => `rgb(${color.join(',')}, 0.7)`);

                // Aplica el degradado linear como nuevo fondo
                sideProfileSection.style.backgroundImage = `linear-gradient(135deg, ${color1}, ${color2}, ${color3})`;
            }
        };

        // Manejo de errores en caso de que no se cargue la imagen
        image.onerror = () => {
            console.error('Error al cargar la imagen para extraer los colores.');
        };
    } else {
        console.warn('No se encontró una imagen de fondo en la sección .sideprofile-section.');
    }
});
