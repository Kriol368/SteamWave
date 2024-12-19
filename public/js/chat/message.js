$(document).ready(function() {
    $('#sendMessageButton').click(function() {
        var url = $(location).attr('href'),
            parts = url.split("/"),
            chat = parts[parts.length-1];

        var message = $('#message').val();

        if (message.trim() === '') {
            /* este alert se debería cambiar*/
            alert('Por favor, escribe un mensaje.');
            return;
        }

        $.ajax({
            url: '/send/message',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ message: message, chat: chat }),
            success: function(response) {
                $('#response').text('Mensaje enviado: ' + response.message);
                $('#message').val("");
            },
            error: function(xhr) {
                $('#response').text('Error: ' + xhr.message);
            }
        });
    });
});