<?php// Obtener el contenido del mensaje entrante
// Obtener el contenido del mensaje entrante
$update = json_decode(file_get_contents('php://input'), true);

// Manejar la respuesta a los botones primero
if (isset($update["callback_query"])) {
    $callback_data = $update["callback_query"]["data"];
    $chat_id = $update["callback_query"]["message"]["chat"]["id"];
    
    if ($callback_data == "opcion1") {
        $response = "Has seleccionado la Opción 1.";
    } elseif ($callback_data == "opcion2") {
        $response = "Has seleccionado la Opción 2.";
    } elseif ($callback_data == "opcion3") {
        $response = "Has seleccionado la Opción 3.";
    } elseif ($callback_data == "opcion4") {
        $response = "Has seleccionado la Opción 4.";
    } elseif ($callback_data == "opcion5") {
        $response = "Has seleccionado la Opción 5.";
    } else {
        $response = "Opción no reconocida.";
    }
    
    sendMessage($chat_id, $response);
    answerCallbackQuery($update["callback_query"]["id"], "¡Opción seleccionada!");

    return; // Salir aquí para evitar procesar mensajes de texto si es un callback
}

// Verificar si hay un mensaje y extraer el chat ID y el texto del mensaje
if (isset($update["message"])) {
    $chat_id = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];

    // Responder al comando /start o hola
    if ($text == "/start" || strtolower($text) == "hola") {
        $response = "Hola, soy NetHelp. ¿Cómo puedo ayudarte en esta ocasión?";
        sendMessage($chat_id, $response, createKeyboard());
    } 
    // Responder al comando /end o adios
    elseif ($text == "/end" || strtolower($text) == "adios") {
        $response = "Un gusto ayudarte, estamos a la orden para ayudarte 🫡.";
        sendMessage($chat_id, $response);
    } 
    // Manejar mensajes no reconocidos
    else {
        $response = "Tenemos falla al comprender tu mensaje, puedes comunicarte con cs21024@ues.edu.sv, él tratará de atender tu consulta y agregar nuevas funcionalidades al sistema para un mejor servicio.";
        sendMessage($chat_id, $response);
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

function sendMessage($chat_id, $text, $reply_markup = null) {
    $bot_token = getenv('BOT_TOKEN_CS21024');
    
    if (!$bot_token) {
        error_log("Token de bot no encontrado. Asegúrate de que está configurado correctamente.");
        return;
    }
    
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => json_encode($reply_markup) // Agregar el teclado si existe
    ];
    file_get_contents($url . "?" . http_build_query($data));
}

function answerCallbackQuery($callback_query_id, $text) {
    $bot_token = getenv('BOT_TOKEN_CS21024');
    
    if (!$bot_token) {
        error_log("Token de bot no encontrado. Asegúrate de que está configurado correctamente.");
        return;
    }
    
    $url = "https://api.telegram.org/bot$bot_token/answerCallbackQuery";
    $data = [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => false // Cambiar a true si deseas mostrar una alerta
    ];
    file_get_contents($url . "?" . http_build_query($data));
}

?>
