<?php
// ----- EVITAMOS ERRORES EN PRODUCCIÓN. -----
error_reporting(E_ALL & ~E_NOTICE);

// ----- EVITAMOS CACHE -----
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");

// ----- FUNCIÓN: ENVÍO DE MENSAJES A USUARIO. -----
function sendMessage($chat_id, $text, $k = '', $remove_keyboard = false) {
    $bot_token = getenv('BOT_TOKEN_CS21024'); 
    
    if (!$bot_token) {
        error_log("Token de bot no encontrado. Asegúrate de que está configurado correctamente.");
        return;
    }

    $url = "https://api.telegram.org/bot$bot_token/sendMessage";

    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'Markdown' // Habilitamos el modo Markdown
    ];

    // Validación de teclado inline
    if (!empty($k)) {
        $data['reply_markup'] = $k;
    }

    // Deshabilitar el teclado normal
    if ($remove_keyboard) {
        $data['reply_markup'] = json_encode([
            'remove_keyboard' => true
        ]);
    }

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

    // CAPTURA DE MENSAJES
    if (isset($msgRecibido["message"])) {
        $chat_id = $msgRecibido["message"]["chat"]["id"];
        $first_name = $msgRecibido["message"]["from"]["first_name"];
        $text = strtolower(trim($msgRecibido["message"]["text"])); // Normaliza el texto recibido

        // Respuesta a "hola" o "/start"
        if ($text == "/start" || $text == "hola" || str_contains($text, "hola")) {
            $response = "Hola " . $first_name . ", soy NetHelp. ¿Cómo puedo ayudarte en esta ocasión?";
            // Creación de teclado inline
            $keyboard = [
                [
                    ['text' => '1. No tengo Internet 🛜', 'callback_data' => 'no_internet'],
                    ['text' => '2. Fallas con el Internet ⚡', 'callback_data' => 'fallas_internet'],
                ],
                [
                    ['text' => '3. Verificar Factura 💸', 'callback_data' => 'verificar_factura'],
                    ['text' => '4. Salir', 'callback_data' => 'salir'],
                ]
            ];
            $key = ['inline_keyboard' => $keyboard];
            $k = json_encode($key);
            
            // Enviar mensaje con teclado inline y deshabilitar teclado normal
            sendMessage($chat_id, $response, $k, true);
        }
        
        // Respuesta a "/autor"
        elseif ($text == "/autor" || str_contains($text, "autor")) {
            $response = "El creador de este bot es:\n" .
                        "**Autor:** Iván Alexander Carranza Sánchez.\n" .
                        "**Correo:** cs21024@ues.edu.sv\n" .
                        "**Tel:** +503 6193 4490\n";
            sendMessage($chat_id, $response, '', true);
        }

        // Respuesta a "adios", "/end" o "salu"
        elseif ($text == "/end" || $text == "adios" || str_contains($text, "salir") || str_contains($text, "adios") || str_contains($text, "salu")) {
            $response = "Un gusto ayudarte, estamos a la orden para ayudarte 👋.";
            sendMessage($chat_id, $response, '', true);
        }
    }

    if (isset($msgRecibido['callback_query'])) {
        $bot_token = getenv('BOT_TOKEN_CS21024'); // Obtén el token aquí para usarlo luego

        $chat_id = $msgRecibido['callback_query']['message']['chat']['id'];
        $callback_id = $msgRecibido['callback_query']['id'];  // Capturamos el ID de la consulta
        $callback_data = $msgRecibido['callback_query']['data'];

        // Respuesta vacía para eliminar el teclado inline
        $clear_keyboard = json_encode([
            'inline_keyboard' => []  // Esto elimina el teclado inline
        ]);

        // Notificar a Telegram que la acción fue recibida (para quitar el "cargando")
        $url = "https://api.telegram.org/bot$bot_token/answerCallbackQuery";
        $data = ['callback_query_id' => $callback_id];
        file_get_contents($url . '?' . http_build_query($data));

        // Manejo de las diferentes respuestas del teclado inline
        switch ($callback_data) {
            case 'no_internet':
                $response = "¿Tienes encendido tu router?";
                sendMessage($chat_id, $response, $clear_keyboard, true); // Remover el teclado
                break;
            case 'fallas_internet':
                sendMessage($chat_id, "Describe las fallas que estás experimentando.", $clear_keyboard, true);
                break;
            case 'verificar_factura':
                sendMessage($chat_id, "Puedes verificar tu factura en la página web del proveedor.", $clear_keyboard, true);
                break;
            case 'salir':
                $response = "Un gusto ayudarte, estamos a la orden para ayudarte 👋.";
                sendMessage($chat_id, $response, $clear_keyboard, true);
                break;
        }
    }
}
?>
