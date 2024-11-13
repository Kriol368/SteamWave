document.addEventListener('DOMContentLoaded', () => {
    // Debugging: Check if tag-select element is found in the DOM
    const tagSelect = document.getElementById('tag-select');

    if (!tagSelect) {
        console.error('The dropdown element with ID "tag-select" was not found.');
        return;
    }

    // Fetch the game list from the server
    fetch('/user/games-list')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch games: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Ensure the data is in the expected format
            if (typeof data !== 'object') {
                console.error('Unexpected data format:', data);
                return;
            }

            // Populate the dropdown with the fetched data
            Object.entries(data).forEach(([gameId, gameTitle]) => {
                const option = document.createElement('option');
                option.value = gameId;
                option.textContent = gameTitle;
                tagSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching game list:', error));
});
