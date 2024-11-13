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
            Object.entries(data).forEach(([gameId, gameInfo]) => {
                const listItem = document.createElement('li');
                listItem.innerHTML = `
                    <strong>${gameInfo.name}</strong> (ID: ${gameId})<br>
                    <img src="${gameInfo.icon}" alt="${gameInfo.name} icon" style="width: 50px; height: 50px;">
                    <p>Playtime: ${(gameInfo.playtime_forever / 60).toFixed(1)} hours</p>
                    ${gameInfo.logo ? `<img src="${gameInfo.logo}" alt="${gameInfo.name} logo" style="width: 100px;">` : ''}
                `;
                gamesList.appendChild(listItem);
            });
        })
        .catch(error => console.error('There was a problem with the fetch operation:', error));
});
