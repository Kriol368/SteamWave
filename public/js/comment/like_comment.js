document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', () => {
            const commentId = button.dataset.id;

            fetch(`/comment/${commentId}/like`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error); // Display error if user already liked
                    } else {
                        // Update the like count and disable the button
                        button.textContent = `Liked (${data.likes})`;
                        button.disabled = true;
                        button.classList.add('liked');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    });
});
