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


$equipos_query = "SELECT DISTINCT equipo FROM pilotos ORDER BY equipo";
$equipos_result = $mysqli->query($equipos_query);




$numeros_existentes = array();
$numeros_query = "SELECT num, nombre FROM pilotos";
$numeros_result = $mysqli->query($numeros_query);
if ($numeros_result) {
    while ($row = $numeros_result->fetch_assoc()) {
        $numeros_existentes[$row['num']] = $row['nombre'];
    }
}

// Procesar creación de nuevo piloto
if (isset($_POST['crear_piloto'])) {
    $numero = intval($_POST['numero']);
    $nombre = trim($_POST['nombre']);
    $nacionalidad = trim($_POST['nacionalidad']);
    $equipo = $_POST['equipo'];
    
    // Inicializar array de errores
    $errores = array();
    
    // Validar número único y rango
    if ($numero < 1 || $numero > 99) {
        $errores['numero'] = "El número debe estar entre 1 y 99";
    } elseif (array_key_exists($numero, $numeros_existentes)) {
        // Mensaje específico indicando qué piloto ya usa ese número
        $piloto_existente = $numeros_existentes[$numero];
        $errores['numero'] = "El número $numero ya está siendo utilizado por el piloto: $piloto_existente";
    }
    
    // Validar nombre - Solo letras, espacios, acentos y algunos caracteres especiales
    if (empty($nombre)) {
        $errores['nombre'] = "El nombre es obligatorio";
    } else {
        // Expresión regular CORREGIDA para nombre: letras, espacios, acentos, ñ, Ñ, guiones, apóstrofes
        $regex_nombre = '/^[a-zA-Z\s\'\-\"áéíóúÁÉÍÓÚñÑüÜàèìòùÀÈÌÒÙäëïöüÄËÏÖÜâêîôûÂÊÎÔÛãõÃÕçÇ]+$/';
        if (!preg_match($regex_nombre, $nombre)) {
            $errores['nombre'] = "El nombre contiene caracteres no permitidos. Solo se aceptan letras, espacios, acentos, ñ, guiones y apóstrofes.";
        } elseif (strlen($nombre) > 100) {
            $errores['nombre'] = "El nombre no puede tener más de 100 caracteres";
        }
    }
    
    // Validar nacionalidad - Solo letras, espacios y acentos
    if (empty($nacionalidad)) {
        $errores['nacionalidad'] = "La nacionalidad es obligatoria";
    } else {
        // Expresión regular CORREGIDA para nacionalidad: solo letras, espacios y acentos
        $regex_nacionalidad = '/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑüÜàèìòùÀÈÌÒÙäëïöüÄËÏÖÜâêîôûÂÊÎÔÛãõÃÕçÇ]+$/';
        if (!preg_match($regex_nacionalidad, $nacionalidad)) {
            $errores['nacionalidad'] = "La nacionalidad contiene caracteres no permitidos. Solo se aceptan letras, espacios y acentos.";
        } elseif (strlen($nacionalidad) > 50) {
            $errores['nacionalidad'] = "La nacionalidad no puede tener más de 50 caracteres";
        }
    }
    
    // Validar equipo
    if (empty($equipo)) {
        $errores['equipo'] = "Debe seleccionar un equipo";
    }
    
    // Si no hay errores, insertar el nuevo piloto
    if (empty($errores)) {
        $stmt = $mysqli->prepare("INSERT INTO pilotos (num, nombre, nacionalidad, equipo, puntos) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("isss", $numero, $nombre, $nacionalidad, $equipo);
        
        if ($stmt->execute()) {
            $mensaje_exito = "Piloto creado exitosamente";
            // Limpiar campos del formulario
            $numero = $nombre = $nacionalidad = $equipo = '';
        } else {
            $mensaje_error = "Error al crear el piloto: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensaje_error = "Por favor corrija los errores del formulario";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Piloto - Fórmula 1</title>
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
            max-width: 600px;
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
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2a7aa5;
            font-weight: 600;
            font-size: 16px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1eff7;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #2a7aa5;
            box-shadow: 0 0 0 3px rgba(42, 122, 165, 0.2);
        }
        
        .dropdown-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1eff7;
            border-radius: 8px;
            font-size: 16px;
            background-color: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%232a7aa5' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            transition: all 0.3s ease;
        }
        
        .dropdown-select:focus {
            outline: none;
            border-color: #2a7aa5;
            box-shadow: 0 0 0 3px rgba(42, 122, 165, 0.2);
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #2a7aa5;
            color: white;
            box-shadow: 0 4px 15px rgba(42, 122, 165, 0.3);
        }
        
        .btn-primary:hover {
            background-color: #1e6a95;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(42, 122, 165, 0.4);
        }
        
        .btn-secondary {
            background-color: #f0f7ff;
            color: #2a7aa5;
            border: 2px solid #e1eff7;
        }
        
        .btn-secondary:hover {
            background-color: #e6f2ff;
            border-color: #2a7aa5;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }
        
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
        
        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
            text-align: center;
        }
        
        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-text {
            color: #5a8ca5;
            font-size: 14px;
            margin-top: 5px;
            font-style: italic;
        }
        
        .btn-volver {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-volver:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        @media (max-width: 480px) {
            .form-container {
                padding: 30px 25px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <a href="menu.php" class="btn-volver">
        ← Volver al Menú
    </a>

    <div class="form-container">
        <h2>Crear Nuevo Piloto</h2>
        <div class="accent"></div>
        <p class="subtitle">Complete la información del nuevo piloto</p>
        
        <?php if (isset($mensaje_exito)): ?>
            <div class="mensaje mensaje-exito">
                <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($mensaje_error)): ?>
            <div class="mensaje mensaje-error">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="numero">Número del Piloto:</label>
                <input type="number" class="form-input" id="numero" name="numero" 
                       value="<?php echo isset($numero) ? htmlspecialchars($numero) : ''; ?>" 
                       min="1" max="99" required
                       placeholder="Ingrese un número entre 1 y 99">
                <div class="info-text">El número debe ser único y no estar en uso por otro piloto</div>
                <?php if (isset($errores['numero'])): ?>
                    <div class="mensaje-temporal"><?php echo $errores['numero']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="nombre">Nombre Completo:</label>
                <input type="text" class="form-input" id="nombre" name="nombre" 
                       value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" 
                       maxlength="100" required
                       placeholder="Ingrese el nombre del piloto">
                <div class="info-text">Solo se permiten letras, espacios, acentos y caracteres especiales de nombres (ñ, Ñ, -, ')</div>
                <?php if (isset($errores['nombre'])): ?>
                    <div class="mensaje-temporal"><?php echo $errores['nombre']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="nacionalidad">Nacionalidad:</label>
                <input type="text" class="form-input" id="nacionalidad" name="nacionalidad" 
                       value="<?php echo isset($nacionalidad) ? htmlspecialchars($nacionalidad) : ''; ?>" 
                       maxlength="50" required
                       placeholder="Ingrese la nacionalidad del piloto">
                <div class="info-text">Solo se permiten letras, espacios y acentos</div>
                <?php if (isset($errores['nacionalidad'])): ?>
                    <div class="mensaje-temporal"><?php echo $errores['nacionalidad']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="equipo">Equipo:</label>
                <select class="dropdown-select" id="equipo" name="equipo" required>
                    <option value="" disabled selected>Seleccione un equipo</option>
                    <?php if ($equipos_result && $equipos_result->num_rows > 0): ?>
                        <?php $equipos_result->data_seek(0); ?>
                        <?php while($equipo = $equipos_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($equipo['equipo']); ?>" 
                                    <?php echo (isset($equipo) && $equipo === $equipo['equipo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($equipo['equipo']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay equipos disponibles</option>
                    <?php endif; ?>
                </select>
                <?php if (isset($errores['equipo'])): ?>
                    <div class="mensaje-temporal"><?php echo $errores['equipo']; ?></div>
                <?php endif; ?>
            </div>
            
            <div class="button-group">
                <button type="submit" name="crear_piloto" class="btn btn-primary">
                    Crear Piloto
                </button>
            </div>
        </form>
    </div>

    <?php
    $mysqli->close();
    ?>
</body>
</html>
