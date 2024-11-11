// public/js/populateTagSelect.js
document.addEventListener("DOMContentLoaded", function() {
    const tagSelect = document.querySelector('#tag_select');
    if (tagSelect) {
        fetch('/user/games-list')
            .then(response => response.json())
            .then(data => {
                tagSelect.innerHTML = '<option value="">Select a game</option>';
                data.forEach(game => {
                    const option = document.createElement('option');
                    option.value = game.appid;
                    option.textContent = game.name;
                    tagSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching user games:', error);
            });
    }
});
