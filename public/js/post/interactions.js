$(document).ready(function() {
     $.ajax({
            url: '/send/message',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ message: message, chat: chatId }), // Enviar el ID del chat
            success: function(response) {
                if (response.status === 'success') {
                    $('#response').text('Mensaje enviado: ' + response.message);
                    $('#message').val(''); // Limpiar el campo de entrada
                    scrollToBottom(); // Desplazar hacia abajo después de enviar el mensaje
                } else {
                    $('#response').text('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                $('#response').text('Error: ' + error);
            }
        });
}