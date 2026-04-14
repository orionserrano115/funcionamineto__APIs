<?php

header("Content-Type: application/json");

/* =========================
   CONEXIÓN A BASE DE DATOS
   ========================= */

$host = "localhost";
$db   = "api_usuarios_db";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

/* =========================
   FUNCIONES CRUD
   ========================= */

function obtenerTodos($pdo) {
    $stmt = $pdo->query("SELECT * FROM usuarios");
    echo json_encode($stmt->fetchAll());
}

function obtenerPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        echo json_encode($usuario);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Usuario no encontrado"]);
    }
}

function crearUsuario($pdo, $data) {

    if (!isset($data['nombre'], $data['correo'], $data['telefono'])) {
        http_response_code(400);
        echo json_encode(["error" => "Datos incompletos"]);
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombre, correo, telefono) VALUES (?, ?, ?)"
    );

    try {
        $stmt->execute([
            $data['nombre'],
            $data['correo'],
            $data['telefono']
        ]);

        http_response_code(201);
        echo json_encode([
            "message" => "Usuario creado",
            "id" => $pdo->lastInsertId()
        ]);

    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(["error" => "Correo ya existe"]);
    }
}

function actualizarUsuario($pdo, $id, $data) {

    if (!isset($data['nombre'], $data['correo'], $data['telefono'])) {
        http_response_code(400);
        echo json_encode(["error" => "Datos incompletos"]);
        return;
    }

    $stmt = $pdo->prepare(
        "UPDATE usuarios 
         SET nombre = ?, correo = ?, telefono = ?
         WHERE id = ?"
    );

    $stmt->execute([
        $data['nombre'],
        $data['correo'],
        $data['telefono'],
        $id
    ]);

    echo json_encode(["message" => "Usuario actualizado"]);
}

function eliminarUsuario($pdo, $id) {

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(["message" => "Usuario eliminado"]);
}

/* =========================
   ROUTER SIMPLE
   ========================= */

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;

switch ($method) {

    case "GET":
        if ($id) {
            obtenerPorId($pdo, $id);
        } else {
            obtenerTodos($pdo);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        crearUsuario($pdo, $data);
        break;

    case "PUT":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID requerido"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        actualizarUsuario($pdo, $id, $data);
        break;

    case "DELETE":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID requerido"]);
            exit;
        }
        eliminarUsuario($pdo, $id);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
}
