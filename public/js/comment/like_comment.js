document.addEventListener('DOMContentLoaded', function () {
    // Like/Unlike buttons for comments
    const likeButtons = document.querySelectorAll('.like-btn');

    likeButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const button = e.target;
            const commentId = button.dataset.id;
            const action = button.classList.contains('btn-danger') ? 'unlike' : 'like'; // Determine if it's like or unlike action
            const url = `/comment/${commentId}/${action}`; // Assuming this is the route format

            // Make AJAX request
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': button.dataset.csrfToken // Get the CSRF token from the button
                },
                body: JSON.stringify({}),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the button text and class
                        if (action === 'like') {
                            button.textContent = `Unlike (${data.likesCount})`;
                            button.classList.remove('btn-success');
                            button.classList.add('btn-danger');
                        } else {
                            button.textContent = `Like (${data.likesCount})`;
                            button.classList.remove('btn-danger');
                            button.classList.add('btn-success');
                        }
                    } else {
                        alert('Something went wrong!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong!');
                });
        });
    });
});
