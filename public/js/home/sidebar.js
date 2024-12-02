document.addEventListener("DOMContentLoaded", () => {
    const profileSection = document.querySelector(".sideprofile-section");
    const profileContent = document.querySelector(".sideprofile-content");

    if (profileSection) {
        const backgroundUrl = profileSection.style.backgroundImage
            .slice(5, -2) // Extrae la URL eliminando `url("")`
            .trim();

        if (backgroundUrl) {
            const img = new Image();
            img.crossOrigin = "Anonymous"; // Asegura que la imagen sea accesible
            img.src = backgroundUrl;

            img.onload = () => {
                Vibrant.from(img).getPalette().then(palette => {
                    if (palette) {
                        const vibrantColor = palette.Vibrant?.getHex() || "#1e2d4b";
                        const mutedColor = palette.Muted?.getHex() || "#184352";

                        // Aplicar el gradiente al contenido
                        profileContent.style.background = `linear-gradient(135deg, ${vibrantColor}, ${mutedColor})`;

                        // Quitar la imagen de fondo en profileSection
                        profileSection.style.backgroundImage = "none";
                    }
                }).catch(err => console.error("Error al procesar los colores:", err));
            };

            img.onerror = () => console.error("No se pudo cargar la imagen:", backgroundUrl);
        }
    }
});
