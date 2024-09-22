<?php
// ----- EVITAMOS ERRORES EN PRODUCCIÓN. -----
error_reporting(0);

// ----- EVITAMOS CACHE -----
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");

// ----- FUNCIÓN: ENVÍO DE MENSAJES A USUARIO CON TECLADO INLINE -----
function sendMessageWithInlineKeyboard($chat_id, $text) {
    $bot_token = getenv('BOT_TOKEN_CS21024'); 

    if (!$bot_token) {
        error_log("Token de bot no encontrado. Asegúrate de que está configurado correctamente.");
        return;
    }

    $url = "https://api.telegram.org/bot$bot_token/sendMessage";

    // Definir el teclado inline con 3 opciones
    $inline_keyboard = [
        'inline_keyboard' => [
            [['text' => 'Opción 1', 'callback_data' => 'opcion_1']],
            [['text' => 'Opción 2', 'callback_data' => 'opcion_2']],
            [['text' => 'Opción 3', 'callback_data' => 'opcion_3']]
        ]
    ];

    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => json_encode($inline_keyboard),
        'parse_mode' => 'Markdown'
    ];

    // Enviar solicitud usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    // Ejecución y manejo de errores
    $result = curl_exec($ch);
    if ($result === false) {
        error_log('Error en la solicitud cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
}

// ----- FUNCIÓN: RESPONDER AL SELECCIONAR UNA OPCIÓN -----
function sendMessage($chat_id, $text) {
    $bot_token = getenv('BOT_TOKEN_CS21024');

    if (!$bot_token) {
        error_log("Token de bot no encontrado. Asegúrate de que está configurado correctamente.");
        return;
    }

    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];

    // Enviar solicitud usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Ejecución y manejo de errores
    $result = curl_exec($ch);
    if ($result === false) {
        error_log('Error en la solicitud cURL: ' . curl_error($ch));
    }

    curl_close($ch);
}

// CAPTURA DE INFORMACIÓN DE CHAT
$input = file_get_contents('php://input');
if ($input) {
    $msgRecibido = json_decode($input, true);

    // Si se recibe un mensaje normal
    if (isset($msgRecibido["message"])) {
        $chat_id = $msgRecibido["message"]["chat"]["id"];
        $first_name = $msgRecibido["message"]["from"]["first_name"];
        $text = strtolower(trim($msgRecibido["message"]["text"])); // Normaliza el texto recibido

        // Si el usuario envía "/start" o "hola"
        if ($text == "/start" || $text == "hola" || str_contains($text, "hola")) {
            $response = "Hola " . $first_name . ", selecciona una de las siguientes opciones:";
            sendMessageWithInlineKeyboard($chat_id, $response); // Enviar opciones
        }
    }

    // Si se recibe un callback query (cuando se presiona una opción)
    elseif (isset($msgRecibido["callback_query"])) {
        $chat_id = $msgRecibido["callback_query"]["message"]["chat"]["id"];
        $callback_data = $msgRecibido["callback_query"]["data"]; // Recoge el valor de "callback_data"

        // Manejar la opción seleccionada
        if ($callback_data == "opcion_1") {
            sendMessage($chat_id, "Has seleccionado *Opción 1*.");
        } elseif ($callback_data == "opcion_2") {
            sendMessage($chat_id, "Has seleccionado *Opción 2*.");
        } elseif ($callback_data == "opcion_3") {
            sendMessage($chat_id, "Has seleccionado *Opción 3*.");
        }
    }
}
?>
