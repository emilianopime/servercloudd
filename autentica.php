<?php
session_start();
date_default_timezone_set("America/Mexico_City");

$host = "localhost";
$user_db = "root";
$pass_db = "";
$dbname = "proyecto";
$port = 3306;

$mysqli = new mysqli($host, $user_db, $pass_db, $dbname, $port);
if ($mysqli->connect_errno) {
    error_log("DB connect error: " . $mysqli->connect_error);
    header("Location: form.php?error=4");
    exit();
}



if (isset($_COOKIE["usuario"])) {

    $usuario_cookie = $_COOKIE["usuario"];

    if ($stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE id = ? LIMIT 1")) {
        $stmt->bind_param("i", $usuario_cookie);
        $stmt->execute();
        $result = $stmt->get_result();
        $registro = $result->fetch_assoc();
        $stmt->close();

        if ($registro) {
            $_SESSION["usuario_id"] = $registro["id"];
            header("Location: menu.php");
            exit();
        } else {
            setcookie("usuario", "", time() - 3600);
        }
    }
}


if (!isset($_POST['user']) || !isset($_POST['pass'])) {
    header("Location: form.php?error=3");
    exit();
}

$usuario_input = $_POST['user'];
$pass_input = $_POST['pass'];

if (filter_var($usuario_input, FILTER_VALIDATE_INT) === false) {
    header("Location: form.php?error=1");
    exit();
}

$usuario = (int)$usuario_input;

if ($stmt = $mysqli->prepare("SELECT id, password FROM usuarios WHERE id = ? LIMIT 1")) {
    $stmt->bind_param("i", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $registro = $result->fetch_assoc();
    $stmt->close();
} else {
    error_log("Prepare failed: " . $mysqli->error);
    header("Location: form.php?error=3");
    exit();
}

if (!$registro) {
    header("Location: form.php?error=1");
    exit();
}

if ($pass_input == $registro["password"]) {

    $_SESSION["usuario_id"] = $registro["id"];

    setcookie("usuario", $registro["id"], time() + 60*5);

    header("Location: menu.php");
    exit();
} else {
    header("Location: form.php?error=2");
    exit();
}

?>
