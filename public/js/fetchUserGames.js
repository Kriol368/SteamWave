// public/js/fetchUserGames.js

document.addEventListener('DOMContentLoaded', () => {
    fetch('/user/games-list')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const gamesList = document.getElementById('user_games_list');
            Object.entries(data).forEach(([gameId, gameTitle]) => {
                const listItem = document.createElement('li');
                listItem.textContent = `${gameTitle} (ID: ${gameId})`;
                gamesList.appendChild(listItem);
            });
        })
        .catch(error => console.error('There was a problem with the fetch operation:', error));
});
