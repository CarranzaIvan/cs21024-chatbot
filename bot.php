<?php
// ----- EVITAMOS ERRORES EN PRODUCCIÓN. -----
error_reporting(E_ALL & ~E_NOTICE);

// ----- EVITAMOS CACHE -----
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");

// ---- FUNCIÓN DE ENVIO DE FOTOS ---
function sendPhoto($chat_id, $photoPath, $caption = '') {
    $bot_token = getenv('BOT_TOKEN_CS21024'); 
    
    if (!$bot_token) {
        error_log("Token de bot no encontrado. Asegúrate de que está configurado correctamente.");
        return;
    }

    $url = "https://api.telegram.org/bot$bot_token/sendPhoto";

    $data = [
        'chat_id' => $chat_id,
        'caption' => $caption, // Opcional
        'parse_mode' => 'Markdown' // Habilitamos el modo Markdown
    ];

    // Usamos CURLFile para enviar un archivo local
    if (file_exists($photoPath)) {
        $data['photo'] = new CURLFile($photoPath);
    } else {
        error_log("La imagen no se encontró en la ruta especificada: $photoPath");
        return;
    }

    // Enviar solicitud usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    // Ejecución y manejo de errores
    $result = curl_exec($ch);
    if ($result === false) {
        error_log('Error en la solicitud cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
}

// ----- FUNCIÓN: ENVÍO DE MENSAJES A USUARIO. -----
function sendMessage($chat_id, $text, $k = '') {
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

    // Validación de teclado
    if (!empty($k)) {
        $data['reply_markup'] = $k;
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

    // CAPTURA DE MENSAJES ESCRITOR - INICIALES
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
            sendMessage($chat_id, $response, $k);
        }

        // Respuesta a "/humano"
        elseif ($text == "/humano" || str_contains($text, "humano")) {
            $response = "¿Puedes seleccionar la compañia la cual te esta proporcionando servicios de Internet?";
            // Creación de teclado inline
            $keyboard = [
                [
                    ['text' => '1. Claro 🔴', 'callback_data' => 'claro'],
                    ['text' => '2. Movistar Ⓜ', 'callback_data' => 'movistar'],
                ],
                [
                    ['text' => '3. Tigo 🔵', 'callback_data' => 'tigo'],
                    ['text' => '4. Digicel ⚪', 'callback_data' => 'digicel'],
                ],
                [
                    ['text' => 'Volver', 'callback_data' => 'volver'],
                    ['text' => 'Salir', 'callback_data' => 'salir'],
                ]
            ];
            $key = ['inline_keyboard' => $keyboard];
            $k = json_encode($key);
            sendMessage($chat_id, $response, $k);
        }

        // Respuesta a "/autor"
        elseif ($text == "/autor" || str_contains($text, "autor")) {
            $response = "El creador de este bot es:\n" .
                        "**Autor:** Iván Alexander Carranza Sánchez.\n" .
                        "**Correo:** cs21024@ues.edu.sv\n" .
                        "**Tel:** +503 6193 4490\n";
            sendMessage($chat_id, $response);
        }

        // Respuesta a "adios", "/end" o "salu"
        elseif ($text == "/end" || $text == "adios" || str_contains($text, "salir") || str_contains($text, "adios") || str_contains($text, "salu")) {
            $response = "Un gusto ayudarte, estamos a la orden para ayudarte 👋.";
            sendMessage($chat_id, $response);
        }
    }

    // CAPTURA DE MENSAJES SELECCIONABLES
    if (isset($msgRecibido['callback_query'])) {
        $bot_token = getenv('BOT_TOKEN_CS21024'); // Obtén el token aquí para usarlo luego

        $chat_id = $msgRecibido['callback_query']['message']['chat']['id'];
        $first_name = $msgRecibido['callback_query']['message']["first_name"];
        $callback_id = $msgRecibido['callback_query']['id'];  // Capturamos el ID de la consulta
        $callback_data = $msgRecibido['callback_query']['data'];

        // Respuesta vacía para eliminar el teclado inline
        $clear_keyboard = json_encode(['inline_keyboard' => []]);

        // Notificar a Telegram que la acción fue recibida (para quitar el "cargando")
        $url = "https://api.telegram.org/bot$bot_token/answerCallbackQuery";
        $data = ['callback_query_id' => $callback_id];
        file_get_contents($url . '?' . http_build_query($data));

        // Manejo de las diferentes respuestas del teclado inline
        switch ($callback_data) {
            case 'no_internet':
                $response = "¿Tienes encendido tu router?";
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $keyboard = [
                    [
                        ['text' => 'Sí ✅', 'callback_data' => 'router_on'],
                        ['text' => 'No ❌', 'callback_data' => 'router_off'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                        ['text' => 'Salir', 'callback_data' => 'salir'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'fallas_internet':
                sendMessage($chat_id, "Describe las fallas que estás experimentando.", $clear_keyboard);
                break;
            case 'verificar_factura':
                sendMessage($chat_id, "Puedes verificar tu factura en la página web del proveedor.", $clear_keyboard);
                break;
            case 'salir':
                $response = "Un gusto ayudarte, estamos a la orden para ayudarte 👋.";
                sendMessage($chat_id, $response, $clear_keyboard);
                break;
            case 'router_on':
                sendMessage($chat_id, "¡Perfecto! Ahora verifica si tienes conexión a Internet.", $clear_keyboard);
                $response = "¿Tu problema ha sido solucionado?";
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $keyboard = [
                    [
                        ['text' => 'Sí ✅', 'callback_data' => 'salir'],
                        ['text' => 'No ❌', 'callback_data' => 'next_router'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'router_off':
                sendMessage($chat_id, "Por favor, enciende tu router y verifica de nuevo.", $clear_keyboard);
                $photo = "./Recursos/router-modem-on.png"; // Asegúrate de que esta ruta es correcta
                $indicaciones = "PASOS PARA ENCENDER EL ROUTER/MODEM\n".
                "1. Enchufa el router a la energia electrica y enciéndelo.\n". 
                "2. Asegúrate de que las luces indicadoras estén encendidas (ver imagen superior de referencia).\n".
                "3. Busca la red Wi-Fi predeterminada en tu dispositivo (el nombre y la contraseña están en la etiqueta del router) o fueron proporcionados por tu proveedor de servicios.";
                sendPhoto($chat_id, $photo, $indicaciones);
                $response = "¿Tu problema ha sido solucionado?";
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $keyboard = [
                    [
                        ['text' => 'Sí ✅', 'callback_data' => 'salir'],
                        ['text' => 'No ❌', 'callback_data' => 'next_router'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'next_router':
                $response = "¿Tiene encendido el WI-FI de tu dispositivo?";
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $keyboard = [
                    [
                        ['text' => 'Sí ✅', 'callback_data' => 'wifi_on'],
                        ['text' => 'No ❌', 'callback_data' => 'wifi_off'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                        ['text' => 'Salir', 'callback_data' => 'salir'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'wifi_on':
                sendMessage($chat_id, "¡Perfecto! Ahora verifica si tienes conexión a Internet.", $clear_keyboard);
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $response = "¿Tu problema ha sido solucionado?";
                $keyboard = [
                    [
                        ['text' => 'Sí ✅', 'callback_data' => 'salir'],
                        ['text' => 'No ❌', 'callback_data' => 'next_wifi'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'wifi_off':
                sendMessage($chat_id, "Por favor, enciende tu router y verifica de nuevo.", $clear_keyboard);
                $response = "¿Cual es tu dispositivo?";
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $keyboard = [
                    [
                        ['text' => 'Teléfono Móvil 📱', 'callback_data' => 'telefono_wifi'],
                        ['text' => 'Computadora 💻', 'callback_data' => 'computadora_wifi'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'telefono_wifi':
                $photo = "./Recursos/wifi-telefono.jpg"; // Asegúrate de que esta ruta es correcta
                $indicaciones = "Pasos para encender el Wi-Fi en un teléfono \n".
                "1. Desbloquea tu teléfono.\n".
                "2. Accede a la pantalla de inicio.\n".
                "3. Busca y abre la aplicación de \"Configuración\" (o \"Ajustes\").\n".
                "4. Encuentra y selecciona \"Conexiones\" o \"Redes\".\n".
                "5. Toca en \"Wi-Fi\".\n".
                "6. Activa el interruptor de Wi-Fi (debería cambiar a \"On\" o \"Activado\").\n".
                "7. Selecciona tu red Wi-Fi de la lista disponible.\n".
                "8. Ingresa la contraseña de la red, si es necesario, y toca \"Conectar\".\n".
                "9. Verifica que esté conectado (deberías ver un icono de Wi-Fi en la barra de estado).";
                sendPhoto($chat_id, $photo, $indicaciones);
                $response = "¿Tu problema ha sido solucionado?";
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $keyboard = [
                    [
                        ['text' => 'Sí ✅', 'callback_data' => 'salir'],
                        ['text' => 'No ❌', 'callback_data' => 'next_wifi'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'computadora_wifi':
                $photo = "./Recursos/wifi-computadora.jpg"; // Asegúrate de que esta ruta es correcta
                $indicaciones = "Enciende tu computadora y accede a tu cuenta.
                1. Haz clic en el icono de red en la esquina inferior derecha de la barra de tareas.
                2. Asegúrate de que el Wi-Fi esté activado (puedes ver un icono de Wi-Fi).
                3. Si el Wi-Fi está apagado, haz clic en \"Activar Wi-Fi\".
                4. Busca las redes disponibles y selecciona tu red Wi-Fi.
                5. Haz clic en \"Conectar\".
                6. Ingresa la contraseña de la red, si es necesario.
                7. Confirma la conexión y verifica que esté conectado (deberías ver el icono de Wi-Fi en la barra de tareas).";
                sendPhoto($chat_id, $photo, $indicaciones);
                $response = "¿Tu problema ha sido solucionado?";
                // Crear un nuevo teclado con opciones "Sí", "No", "Volver" y "Salir"
                $keyboard = [
                    [
                        ['text' => 'Sí ✅', 'callback_data' => 'salir'],
                        ['text' => 'No ❌', 'callback_data' => 'next_wifi'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k); // Enviar mensaje con nuevo teclado
                break;
            case 'next_wifi':
                // Redirige a la lógica de la respuesta a "/humano"
                $response = "Parece que tu situación es un poco compleja. Te recomiendo que hables con alguien de fuera para obtener una mejor perspectiva y asesoría.\n".
                "¿Puedes seleccionar la compañia la cual te esta proporcionando servicios de Internet?";
                // Creación de teclado inline
                $keyboard = [
                    [
                        ['text' => '1. Claro 🔴', 'callback_data' => 'claro'],
                        ['text' => '2. Movistar Ⓜ', 'callback_data' => 'movistar'],
                    ],
                    [
                        ['text' => '3. Tigo 🔵', 'callback_data' => 'tigo'],
                        ['text' => '4. Digicel ⚪', 'callback_data' => 'digicel'],
                    ],
                    [
                        ['text' => 'Volver', 'callback_data' => 'volver'],
                        ['text' => 'Salir', 'callback_data' => 'salir'],
                    ]
                ];
                $key = ['inline_keyboard' => $keyboard];
                $k = json_encode($key);
                sendMessage($chat_id, $response, $k);
                break;
            case 'volver':
                // Regresar al teclado anterior
                $response = "Hola, " . $first_name . " ¿Cómo puedo ayudarte en esta ocasión?";
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
                sendMessage($chat_id, $response, $k); // Regresar al teclado anterior
                break;
            // Agregamos atencion a internet
            case 'claro':
                $photo = "./Recursos/logo_Claro.png"; // Asegúrate de que esta ruta es correcta
                $caption = "Para una atención personalizada, te invitamos a comunicarte con nuestro equipo de soporte al cliente Claro. Por favor, llama al +503 2250 5555.";
                sendPhoto($chat_id, $photo, $caption);
                break;
            case 'tigo':
                $photo = "./Recursos/logo_Tigo.png"; // Asegúrate de que esta ruta es correcta
                $caption = "Para una atención personalizada, te invitamos a comunicarte con nuestro equipo de soporte al cliente Tigo. Por favor, llama al +503 2207 4000.";
                sendPhoto($chat_id, $photo, $caption);
                break;
            case 'movistar':
                $photo = "./Recursos/logo_Movistar.png"; // Asegúrate de que esta ruta es correcta
                $caption = "Para una atención personalizada, te invitamos a comunicarte con nuestro equipo de soporte al cliente Telefonica. Por favor, llama al +503 7119-7119.";
                sendPhoto($chat_id, $photo, $caption);
                break; 
            case 'digicel':
                $photo = "./Recursos/logo_Digicel.png"; // Asegúrate de que esta ruta es correcta
                $caption = "Para una atención personalizada, te invitamos a comunicarte con nuestro equipo de soporte al cliente Digicel. Por favor, llama al +503 2504-3444.";
                sendPhoto($chat_id, $photo, $caption);
                break; 
        }
    }
}
?>