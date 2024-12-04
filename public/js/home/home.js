document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav-element').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default behavior of the link

            // Remove 'selected' class from all nav elements
            document.querySelectorAll('.nav-element').forEach(item => item.classList.remove('selected'));
            this.classList.add('selected');

            // Hide all feeds and show the clicked one
            document.querySelectorAll('.feed').forEach(content => {
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
