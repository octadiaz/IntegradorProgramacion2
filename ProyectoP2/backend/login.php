<?php
include_once 'database.php';

// Configuración de CORS - siempre enviar estos encabezados
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Manejar la solicitud OPTIONS para las preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Responder con los encabezados CORS y un código 200 (OK)
    http_response_code(200);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    try {
        // Obtener datos del POST
        $data = json_decode(file_get_contents("php://input"));

        // Verificar si el usuario y la contraseña están definidos
        if (!empty($data->usuario) && !empty($data->password)) {
            $usuario = $data->usuario;
            $password = $data->password;

            // Consulta SQL para buscar el usuario en la base de datos
            $sql = "SELECT * FROM Usuarios WHERE Usuario = :usuario";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":usuario", $usuario);
            $stmt->execute();

            // Verificar si se encontró el usuario
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verificar la contraseña (deberías usar hash, aquí solo es un ejemplo)
                if (password_verify($password, $user['Password'])) {
                    // Login exitoso
                    header('Content-Type: application/json');
                    echo json_encode(["status" => true, "message" => "Login exitoso"]);
                } else {
                    // Contraseña incorrecta
                    header('Content-Type: application/json');
                    echo json_encode(["status" => false, "message" => "Contraseña incorrecta"]);
                }
            } else {
                // Usuario no encontrado
                header('Content-Type: application/json');
                echo json_encode(["status" => false, "message" => "Usuario no encontrado"]);
            }
        } else {
            // Campos obligatorios faltantes
            header('Content-Type: application/json');
            echo json_encode(["status" => false, "message" => "Faltan campos obligatorios"]);
        }
    } catch (PDOException $exception) {
        // Devolver un mensaje de error si ocurre un problema
        header('Content-Type: application/json');
        echo json_encode(["status" => false, "message" => "Error en la consulta: " . $exception->getMessage()]);
    }
} else {
    // Devolver un mensaje de error si no se puede conectar a la base de datos
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "message" => "No se pudo conectar a la base de datos"]);
}
?>