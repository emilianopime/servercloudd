<?php
session_start();
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


if (isset($_SESSION["usuario_id"])) {
    header("Location: menu.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario</title>
    <style>
        /* Estilos generales */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f7ff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .form-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 60, 120, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid #e1eff7;
        }
        
        h2 {
            color: #2a7aa5; /* Azul más saturado */
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
            letter-spacing: -0.5px;
        }
        
        /* Estilos del formulario */
        form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            color: #3a6a85;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            font-size: 16px;
        }
        
        input[type="text"], input[type="password"], input[type="number"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #a8d1e8;
            border-radius: 8px;
            background-color: #f5fbff;
            font-size: 16px;
            color: #2a4a62;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus, input[type="password"]:focus, input[type="number"]:focus {
            outline: none;
            border-color: #2a7aa5;
            box-shadow: 0 0 0 3px rgba(42, 122, 165, 0.25);
        }
        
        input[type="text"]:hover, input[type="password"]:hover, input[type="number"]:hover {
            border-color: #2a7aa5;
            background-color: #edf7ff;
        }
        
        select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #a8d1e8;
            border-radius: 8px;
            background-color: #f5fbff;
            font-size: 16px;
            color: #2a4a62;
            transition: all 0.3s ease;
            appearance: none;
            background-repeat: no-repeat;
            background-position: right 16px center;
            cursor: pointer;
        }
        
        select:focus {
            outline: none;
            border-color: #2a7aa5;
            box-shadow: 0 0 0 3px rgba(42, 122, 165, 0.25);
        }
        
        select:hover {
            border-color: #2a7aa5;
            background-color: #edf7ff;
        }
        
        button {
            background-color: #2a7aa5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(42, 122, 165, 0.3);
            margin-top: 10px;
        }
        
        button:hover {
            background-color: #1e6a95;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(42, 122, 165, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        /* Estilos para las opciones del select */
        option {
            padding: 10px;
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
        
        /* Mensaje de error */
        .error-message {
            background-color: rgba(201, 60, 60, 0.1);
            border: 1px solid #c93c3cff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #a52a2aff;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }
        
        /* Mensaje de información */
        .info-message {
            background-color: rgba(42, 122, 165, 0.1);
            border: 1px solid #2a7aa5;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 8px;
            color: #2a4a62;
            font-size: 14px;
            font-weight: 400;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .form-container {
                padding: 30px 25px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    
    <div class="form-container">
        <h2>Formulario</h2>
        <div class="accent"></div>
        <form action="autentica.php" method="post">
        <?php
            if (isset ($_GET['error'])){
                if ($_GET["error"] == "1" )
                echo '<div class="error-message">Usuario incorrecto</div>';
                if ($_GET["error"] == "2" )
                echo '<div class="error-message">Contraseña incorrecto</div>';
                if ($_GET["error"] == "3" )
                echo '<div class="error-message">Debes iniciar sesión</div>';
                if ($_GET["error"] == "4" )
                echo '<div class="error-message">Error</div>';
            }
        ?>
            <div class="form-group">
                <label for="user">Usuario:</label>
                <input type="number" id="user" name="user" placeholder="Ingresa tu número de usuario" required>
            </div>
            <div class="form-group">
                <label for="pass">Contraseña:</label>
                <input type="password" id="pass" name="pass" placeholder="Contraseña" required>
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>