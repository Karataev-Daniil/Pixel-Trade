jQuery(document).ready(function($) {
    let receiverId = null;

    $('.open-chat').click(function() {
        receiverId = $(this).data('receiver');
        $('#chat-popup').show();
        loadMessages();
    });

    $('#send-chat').click(function() {
        const message = $('#chat-input').val();
        if (!message) return;

        $.post(ajaxurl, {
            action: 'send_private_message',
            receiver_id: receiverId,
            message: message,
        }, function() {
            $('#chat-input').val('');
            loadMessages();
        });
    });

    function loadMessages() {
        $.get(ajaxurl, {
            action: 'load_private_messages',
            receiver_id: receiverId
        }, function(response) {
            $('#chat-messages').html(response);
        });
    }
});
