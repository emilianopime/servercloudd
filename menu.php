<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: form.php?error=3");
    exit();
}

$host = "localhost";
$user_db = "root";
$pass_db = "";
$dbname = "proyecto";
$port = 3306;

$mysqli = new mysqli($host, $user_db, $pass_db, $dbname, $port);
if ($mysqli->connect_errno) {
    error_log("Error de conexión: " . $mysqli->connect_error);
    header("Location: form.php?error=4");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

if ($stmt = $mysqli->prepare("SELECT id, nombre FROM usuarios WHERE id = ? LIMIT 1")) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $registro = $result->fetch_assoc();
    $stmt->close();
} else {
    error_log("Error en prepare: " . $mysqli->error);
    header("Location: form.php?error=3");
    exit();
}

if (!$registro) {
    session_destroy();
    header("Location: form.php?error=3");
    exit();
}

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Navegación</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f2ff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .form-container {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 60, 120, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid #e1eff7;
            text-align: center;
        }
        
        h2 {
            color: #2a7aa5;
            text-align: center;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 28px;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            color: #5a8ca5;
            text-align: center;
            margin-bottom: 35px;
            font-size: 16px;
            font-weight: 400;
        }
        
        /* Estilos para la imagen */
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 25px 0;
            padding: 10px;
        }
        
        .centered-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(42, 122, 165, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .centered-image:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(42, 122, 165, 0.3);
        }
        
        /* Estilos para los botones */
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }
        
        .nav-button {
            background-color: #2a7aa5;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 18px 24px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(42, 122, 165, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }
        
        .nav-button:hover {
            background-color: #1e6a95;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(42, 122, 165, 0.4);
        }
        
        .nav-button:active {
            transform: translateY(0);
        }
        
        .nav-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .nav-button:hover::after {
            left: 100%;
        }
        
        /* Iconos para los botones */
        .button-icon {
            font-size: 20px;
        }
        
        /* Elemento decorativo*/
        .accent {
            height: 4px;
            background-color: #2a7aa5;
            width: 80px;
            margin: 0 auto 25px auto;
            border-radius: 2px;
            position: relative;
        }
        
        .accent:before {
            content: '';
            position: absolute;
            height: 4px;
            width: 20px;
            background-color: #2a7aa5;
            left: 30px;
            border-radius: 2px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .form-container {
                padding: 30px 25px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .centered-image {
                max-width: 90%;
            }
            
            .nav-button {
                padding: 16px 20px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Menú</h2>
        <div class="accent"></div>
        
        
        <div class="button-container">
            <button class="nav-button" onclick="window.location.href='altas.php'">
                <span>Nuevo registro</span>
            </button>

            <button class="nav-button" onclick="window.location.href='registros.php'">
                <span>Editar información</span>
            </button>
            
            <button class="nav-button" onclick="window.location.href='consultas.php'">
                <span>Consultas</span>
            </button>
            
            <button class="nav-button" onclick="window.location.href='descargas.php'">
                <span>Descargar archivos</span>
            </button>
        </div>
    </div>

</body>
</html>