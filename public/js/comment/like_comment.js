document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const commentId = button.dataset.id;

            try {
                const response = await fetch(`/comment/${commentId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ _token: button.dataset.token })
                });

                const responseText = await response.text();
                console.log('Response text:', responseText);

                const data = JSON.parse(responseText);
                if (data.success) {
                    button.textContent = `Liked (${data.likes})`;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
});
