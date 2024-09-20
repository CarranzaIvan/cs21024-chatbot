<?php
// Obtener el contenido del mensaje entrante
$update = json_decode(file_get_contents('php://input'), true);

// Verificar si hay un mensaje y extraer el chat ID y el texto del mensaje
if (isset($update["message"])) {
    $chat_id = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];

    // Responder al comando /start
    if ($text == "/start" || strtolower($text) == "hola") {
        $response = "Hola, soy NetHelp. ¿Cómo puedo ayudarte en esta ocasión?";
        sendMessage($chat_id, $response, createKeyboard());
    }
    
    // Responder al comando /end
    if ($text == "/end" || strtolower($text) == "adios") {
        $response = "Un gusto ayudarte, estamos a la orden para ayudarte 🫡.";
        sendMessage($chat_id, $response);
    } else {
        $response = "Tenemos falla al comprender tu mensaje, puedes comunicarte con cs21024@ues.edu.sv, él tratará de atender tu consulta y agregar nuevas funcionalidades al sistema para un mejor servicio.";
        sendMessage($chat_id, $response);
    }
    
    // Manejar la respuesta a los botones
    if (isset($update["callback_query"])) {
        $callback_data = $update["callback_query"]["data"];
        $chat_id = $update["callback_query"]["message"]["chat"]["id"];
        
        if ($callback_data == "opcion1") {
            $response = "Has seleccionado la Opción 1.";
            sendMessage($chat_id, $response);
        } elseif ($callback_data == "opcion2") {
            $response = "Has seleccionado la Opción 2.";
            sendMessage($chat_id, $response);
        } elseif ($callback_data == "opcion3") {
            $response = "Has seleccionado la Opción 3.";
            sendMessage($chat_id, $response);
        } elseif ($callback_data == "opcion4") {
            $response = "Has seleccionado la Opción 4.";
            sendMessage($chat_id, $response);
        } elseif ($callback_data == "opcion5") {
            $response = "Has seleccionado la Opción 5.";
            sendMessage($chat_id, $response);
        } else {
            $response = "Opción no reconocida.";
            sendMessage($chat_id, $response);
        }
    }
}

// Función para crear el teclado
function createKeyboard() {
    return [
        'inline_keyboard' => [
            [
                ['text' => 'Consulta 1', 'callback_data' => 'opcion1']
            ],
            [
                ['text' => 'Consulta 2', 'callback_data' => 'opcion2']
            ],
            [
                ['text' => 'Consulta 3', 'callback_data' => 'opcion3']
            ],
            [
                ['text' => 'Consulta 4', 'callback_data' => 'opcion4']
            ],
            [
                ['text' => 'Consulta 5', 'callback_data' => 'opcion5']
            ]
        ]
    ];
}

// Función para enviar un mensaje a Telegram
function sendMessage($chat_id, $text, $reply_markup = null) {
    $bot_token = "7791693312:AAEJmYrdEWyRvcvdY4I4-sNEjnC2r9h6u3k";
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => json_encode($reply_markup) // Agregar el teclado si existe
    ];
    file_get_contents($url . "?" . http_build_query($data));
}
?>
