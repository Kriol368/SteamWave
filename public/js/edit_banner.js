document.addEventListener('DOMContentLoaded', () => {
    const tagSelect = document.getElementById('user_game');

    if (!tagSelect) {
        console.error('The dropdown element with ID "post_form_tag" was not found.');
        return;
    }


    fetch('/user/games-list')  // Fetch games from the backend
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch games: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (typeof data !== 'object' || Array.isArray(data)) {
                console.error('Invalid games data format:', data);
                return;
            }

            console.log('Fetched data:', data);

            Object.entries(data).forEach(([gameId, gameInfo]) => {
                if (gameInfo && gameInfo.name) {
                    const option = document.createElement('option');
                    option.value = gameId;
                    option.textContent = gameInfo.name;
                    tagSelect.appendChild(option);
                } else {
                    console.warn('Invalid game data for ID:', gameId, gameInfo);
                }
            });
        })
        .catch(error => console.error('Error fetching game list:', error));

});
