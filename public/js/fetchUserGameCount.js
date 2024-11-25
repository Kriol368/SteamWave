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
            const gameCountValue = document.getElementById('game_count_value');
            const count = Object.keys(data).length;

            gameCountValue.textContent = count; // Update the game count display
        })
        .catch(error => console.error('There was a problem with the fetch operation:', error));
});
