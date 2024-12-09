document.addEventListener('DOMContentLoaded', () => {
    const tagSelect = document.getElementById('post_form_tag');

    if (!tagSelect) {
        console.error('The dropdown element with ID "post_form_tag" was not found.');
        return;
    }

    console.log('Dropdown found:', tagSelect); // Log when the dropdown is found

    fetch('/user/games-list')
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch games: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (typeof data !== 'object') {
                console.error('Unexpected data format:', data);
                return;
            }

            console.log('Fetched data:', data); // Log the fetched data

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
