<?php
// Obtener el contenido del mensaje entrante
$update = json_decode(file_get_contents('php://input'), true);

// Extraer el chat ID y el mensaje
$chat_id = $update["message"]["chat"]["id"];
$text = $update["message"]["text"];

// Responder al comando /start
if ($text == "/start") {
    $response = "Probando conexion 2";
    sendMessage($chat_id, $response);
}

// Función para enviar un mensaje a Telegram
function sendMessage($chat_id, $text) {
    $bot_token = "7791693312:AAEJmYrdEWyRvcvdY4I4-sNEjnC2r9h6u3k";
    $url = "https://api.telegram.org/bot7791693312:AAEJmYrdEWyRvcvdY4I4-sNEjnC2r9h6u3k/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
    ];
    file_get_contents($url . "?" . http_build_query($data));
}
?>