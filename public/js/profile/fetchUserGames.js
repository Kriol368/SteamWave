document.addEventListener('DOMContentLoaded', () => {
    const userId = document.body.dataset.userId; // This is just for fetching the games list

    fetch(`/user/${userId}/games-list`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const gamesList = document.getElementById('user_games_list');
            const gameCountValue = document.getElementById('game_count_value');

            gamesList.innerHTML = ''; // Clear the list before appending new items
            gameCountValue.textContent = Object.keys(data).length; // Update game count

            Object.entries(data).forEach(([gameId, gameInfo]) => {
                const listItem = document.createElement('li');
                listItem.classList.add('game-item');

                // Calculate achievement progress
                const totalAchievements = gameInfo.total_achievements ?? 0;
                const unlockedAchievements = gameInfo.unlocked_achievements ?? 0;
                const progressPercentage = totalAchievements > 0 ? (unlockedAchievements / totalAchievements) * 100 : 0;

                listItem.innerHTML = `
                    <a href="${gameRouteBase}${gameId}" class="game-link">
                        <img class="game-icon-profile" src="${gameInfo.icon}" alt="${gameInfo.name} icon">
                        <div class="game-info">
                            <strong class="game-name">${gameInfo.name}</strong> 
                            <span class="game-id">(ID: ${gameId})</span>
                            <p class="game-playtime">Playtime: ${(gameInfo.playtime_forever / 60).toFixed(1)} hours</p>

                            ${gameInfo.logo ? `<img class="game-logo" src="${gameInfo.logo}" alt="${gameInfo.name} logo">` : ''}

                            <!-- Achievement Progress Bar -->
                            <div class="achievement-progress">
                                <div class="progress-bar" style="width: ${progressPercentage}%;"></div>
                            </div>
                            <p class="achievement-info">
                                ${unlockedAchievements} / ${totalAchievements} Achievements Unlocked
                            </p>
                        </div>
                    </a>
                `;

                gamesList.appendChild(listItem);
            });
        })
        .catch(error => console.error('There was a problem with the fetch operation:', error));
});
