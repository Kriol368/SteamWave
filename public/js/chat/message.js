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

    // Evento al presionar Enter dentro del campo de texto
    $('#message').keydown(function(event) {
        if (event.which === 13) {  // 13 es el código de la tecla Enter
            event.preventDefault();  // Evitar la acción predeterminada del Enter (salto de línea)
            sendMessage();
            $('#message').val('');  // Limpiar el campo de entrada después de presionar Enter
        }
    });

    // Función para desplazar la scrollbar hacia abajo
    function scrollToBottom() {
        var chatContainer = $('.chats-content');
        chatContainer.scrollTop(chatContainer[0].scrollHeight);
    }

    // Desplazar hacia abajo cuando se carguen los mensajes al iniciar
    scrollToBottom();
});
