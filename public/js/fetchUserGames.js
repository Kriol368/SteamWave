// public/js/fetchUserGames.js
document.addEventListener("DOMContentLoaded", function() {
    const gameList = document.querySelector('#user_games_list');
    if (gameList) {
        fetch('/user/games-list')
            .then(response => response.json())
            .then(data => {
                gameList.innerHTML = '';  // Clear the "Loading games..." message
                data.forEach(game => {
                    const listItem = document.createElement('li');
                    listItem.textContent = game.name;
                    gameList.appendChild(listItem);
                });
            })
            .catch(error => {
                console.error('Error fetching user games:', error);
                gameList.innerHTML = '<li>Failed to load games.</li>';
            });
    }
});
