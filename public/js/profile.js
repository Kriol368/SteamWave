//mostrar contenido de abajo
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Evita el comportamiento predeterminado del enlace

            document.querySelectorAll('.nav-link').forEach(item => item.classList.remove('selected'));
            this.classList.add('selected');

            document.querySelectorAll('.content').forEach(content => {
                content.style.display = 'none';
            });

            const target = this.getAttribute('data-target');
            const contentToShow = document.getElementById(target);
            if (contentToShow) {
                contentToShow.style.display = 'block';
            }
        });
    });
});
