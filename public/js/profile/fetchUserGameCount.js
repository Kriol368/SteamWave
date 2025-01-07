document.addEventListener('DOMContentLoaded', () => {
    const userId = document.body.dataset.userId; // Fetch the user ID from the body tag

    if (!userId) {
        console.error('User ID not found. Ensure the "data-user-id" attribute is set.');
        return;
    }

    fetch(`/user/${userId}/games-list`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const gameCountValues = document.querySelectorAll('.game-count-value');  // Select all elements with the class 'game-count'
            if (gameCountValues.length > 0) {
                const count = Object.keys(data).length;

                gameCountValues.forEach(element => {
                    element.textContent = count;  // Update the textContent for each element
                });
            } else {
                console.warn('No elements with the class "game-count-value" were found.');
            }
        })
        .catch(error => console.error('There was a problem with the fetch operation:', error));
});
