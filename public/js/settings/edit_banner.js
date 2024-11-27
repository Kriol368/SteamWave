document.addEventListener('DOMContentLoaded', () => {
    const bannerSelect = document.querySelector('#banner-select-container select'); // Finds the select element inside #banner-select-container

    if (!bannerSelect) {
        console.error('The select element for "banner" was not found.');
        return;
    }

    console.log('Select dropdown for banner found:', bannerSelect);

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

            console.log('Fetched data:', data);

            // Clear existing options
            bannerSelect.innerHTML = '';

            // Populate new options
            Object.entries(data).forEach(([gameId, gameInfo]) => {
                if (gameInfo && gameInfo.name) {
                    const option = document.createElement('option');
                    option.value = gameId;
                    option.textContent = gameInfo.name;
                    bannerSelect.appendChild(option);
                } else {
                    console.warn('Invalid game data for ID:', gameId, gameInfo);
                }
            });
        })
        .catch(error => console.error('Error fetching game list:', error));
});
