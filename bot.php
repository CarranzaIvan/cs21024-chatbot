<?php
// ----- EVITAMOS ERRORES EN PRODUCCIÓN -----
error_reporting(0);

// ----- EVITAMOS CACHE -----
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");

// ----- CONSTANTES -----
define('BOT_TOKEN', getenv('BOT_TOKEN_CS21024'));

// ----- FUNCIÓN: ENVÍO DE MENSAJES A USUARIO -----
function sendMessage($chat_id, $text, $keyboard = '') {
    if (!BOT_TOKEN) {
        error_log("Token de bot no encontrado. Asegúrate de que está configurado correctamente.");
        return;
    }

    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'Markdown'
    ];

    if (!empty($keyboard)) {
        $data['reply_markup'] = $keyboard;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    // Ejecutar y manejar errores
    $result = curl_exec($ch);
    if ($result === false) {
        error_log('Error en la solicitud cURL: ' . curl_error($ch));
    }

    curl_close($ch);
}

// ----- CAPTURA DE INFORMACIÓN DE CHAT -----
$input = file_get_contents('php://input');
if ($input) {
    $msgRecibido = json_decode($input, true);

    // CAPTURA DE MENSAJES
    if (isset($msgRecibido["message"])) {
        $chat_id = $msgRecibido["message"]["chat"]["id"];
        $first_name = htmlspecialchars($msgRecibido["message"]["from"]["first_name"]);
        $text = strtolower(trim($msgRecibido["message"]["text"]));

        // Respuestas
        $responses = [
            "/start" => "Hola $first_name, soy NetHelp. ¿Cómo puedo ayudarte en esta ocasión?",
            "hola" => "Hola $first_name, soy NetHelp. ¿Cómo puedo ayudarte en esta ocasión?",
            "no tengo internet" => "¿Tienes encendido tu router?",
            "/autor" => "El creador de este bot es:\n**Autor:** Iván Alexander Carranza Sánchez.\n**Correo:** cs21024@ues.edu.sv\n**Tel:** +503 6193 4490\n",
            "/end" => "Un gusto ayudarte, estamos a la orden para ayudarte 👋."
        ];

        // Respuesta a "hola" o "/start"
        if (array_key_exists($text, $responses)) {
            $response = $responses[$text];
            if ($text == "/start" || $text == "hola") {
                $keyboard = [['1. No tengo Internet 🛜.', '2. Fallas con el internet ⚡.', '3. Verificar factura 💸.', '4. Salir']];
                $k = json_encode(['one_time_keyboard' => true, 'resize_keyboard' => true, 'keyboard' => $keyboard]);
                sendMessage($chat_id, $response, $k);
            } else {
                sendMessage($chat_id, $response);
            }
        }
    }
}
?>
