<?php
// ----- EVITAMOS ERRORES EN PRODUCCIÓN. -----
error_reporting(0);

// ----- EVITAMOS CACHE -----
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");

// ----- FUNCIÓN: ENVIO DE MENSAJES A USUARIO. -----
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
    ];

    // Enviar solicitud usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_exec($ch);
    curl_close($ch);
}

// CAPTURA DE INFORMACION DE CHAT
$input = file_get_contents('php://input');
if ($input) {
    $msgRecibido = json_decode($input, true);

    // CAPTURA DE MENSAJES
    if (isset($msgRecibido["message"])) {
        $chat_id = $msgRecibido["message"]["chat"]["id"];
        $first_name = $msgRecibido["message"]["from"]["first_name"];
        $text = $msgRecibido["message"]["text"];

        if ($text == "/start" || strtolower($text) == "hola" || str_contains(strtolower($text), "hola")) {
            $response = "Hola " . $first_name . ", soy NetHelp. ¿Cómo puedo ayudarte en esta ocasión?";
            sendMessage($chat_id, $response);
        }

        if ($text == "/end" || strtolower($text) == "adios" || str_contains(strtolower($text), "adios") || str_contains(strtolower($text), "salu ")) {
            $response = "Hola " . $first_name . ", soy NetHelp. ¿Cómo puedo ayudarte en esta ocasión?";
            sendMessage($chat_id, $response);
        }
    }
}
?>
