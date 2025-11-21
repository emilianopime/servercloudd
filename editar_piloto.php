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
    die("Error de conexión: " . $mysqli->connect_error);
}

/* --------------------------------------------------------
   1. OBTENER EL PILOTO SELECCIONADO
-------------------------------------------------------- */
if (!isset($_POST["piloto_id"])) {
    header("Location: registros.php?error=sin_piloto");
    exit();
}

$piloto_id = $_POST["piloto_id"];

/* Recuperar información del piloto */
$stmt = $mysqli->prepare("SELECT * FROM pilotos WHERE num = ? LIMIT 1");
$stmt->bind_param("i", $piloto_id);
$stmt->execute();
$result = $stmt->get_result();
$piloto = $result->fetch_assoc();
$stmt->close();

if (!$piloto) {
    header("Location: registros.php?error=piloto_no_existe");
    exit();
}

/* --------------------------------------------------------
   2. SI PRESIONÓ GUARDAR → ACTUALIZAR
-------------------------------------------------------- */
if (isset($_POST["guardar_cambios"])) {

    $nuevos = $_POST["puntos"];

    $stmt = $mysqli->prepare("UPDATE pilotos SET puntos = ? WHERE num = ?");
    $stmt->bind_param("ii", $nuevos, $piloto_id);
    $stmt->execute();
    $stmt->close();

    header("Location: registros.php?exito=1");
    exit();
}

/* --------------------------------------------------------
   3. SI PRESIONÓ CANCELAR → REGRESAR
-------------------------------------------------------- */
if (isset($_POST["cancelar"])) {
    header("Location: registros.php?cancelado=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar piloto</title>
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
        position: relative;
    }
    
    .form-container {
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0, 60, 120, 0.15);
        padding: 40px;
        width: 100%;
        max-width: 800px;
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
    
    /* Dropdowns */
    .dropdown {
        position: relative;
        display: inline-block;
        width: 100%;
        margin-bottom: 20px;
    }
    
    .dropdown-select {
        width: 100%;
        padding: 15px 20px;
        font-size: 16px;
        border: 2px solid #e1eff7;
        border-radius: 10px;
        background-color: white;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%232a7aa5' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 20px center;
        transition: all 0.3s ease;
    }
    
    .dropdown-select:focus {
        outline: none;
        border-color: #2a7aa5;
        box-shadow: 0 0 0 3px rgba(42, 122, 165, 0.2);
    }
    
    /* Sección de pilotos */
    .pilots-container {
        margin-top: 20px;
        animation: fadeIn 0.5s ease;
    }
    
    .pilots-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .pilot-button {
        flex: 1;
        min-width: 150px;
        padding: 12px 15px;
        background-color: #f0f7ff;
        border: 2px solid #e1eff7;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        color: #2a7aa5;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .pilot-button:hover {
        background-color: #e6f2ff;
        border-color: #2a7aa5;
    }
    
    .pilot-button.active {
        background-color: #2a7aa5;
        color: white;
        border-color: #2a7aa5;
    }
    
    /* Información del piloto */
    .pilot-info {
        display: none;
        background-color: #f8fbff;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid #e1eff7;
        text-align: left;
        animation: fadeIn 0.5s ease;
    }
    
    .pilot-info.active {
        display: block;
    }
    
    .pilot-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .pilot-number {
        background-color: #2a7aa5;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
    }
    
    .pilot-name {
        font-size: 22px;
        font-weight: 600;
        color: #2a7aa5;
    }
    
    .team-badge {
        background-color: #e6f2ff;
        color: #2a7aa5;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
        margin: 10px 0;
    }
    
    .pilot-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 15px;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-label {
        font-size: 14px;
        color: #5a8ca5;
        margin-bottom: 5px;
    }
    
    .detail-value {
        font-size: 16px;
        font-weight: 600;
        color: #2a7aa5;
    }
    
    /* Formularios */
    .edit-form {
        margin-top: 20px;
        padding: 20px;
        background-color: #f0f7ff;
        border-radius: 10px;
        border: 1px solid #e1eff7;
    }
    
    .form-group {
        margin-bottom: 15px;
        text-align: left;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #2a7aa5;
        font-weight: 600;
    }
    
    .form-input {
        width: 100%;
        padding: 10px 15px;
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
    
    /* Botones */
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
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
        transform: translateY(-2px);
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
    }
    
    .nav-button:hover {
        background-color: #1e6a95;
        transform: translateY(-3px);
    }
    
    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        justify-content: center;
    }
    
    /* Grid de pilotos */
    .all-pilots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .pilot-card {
        background-color: #f8fbff;
        border: 1px solid #e1eff7;
        border-radius: 10px;
        padding: 20px;
        text-align: left;
        transition: all 0.3s ease;
    }
    
    .pilot-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(42, 122, 165, 0.15);
    }
    
    /* Elementos decorativos */
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
    
    /* Mensajes */
    .mensaje {
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
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
    
    /* Botón volver */
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
        z-index: 1000;
    }
    
    .btn-volver:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
        color: white;
        text-decoration: none;
    }
    
    /* Animaciones */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive */
    @media (max-width: 480px) {
        .form-container {
            padding: 30px 25px;
            max-width: 95%;
        }
        
        h2 {
            font-size: 24px;
        }
        
        .pilots-buttons {
            flex-direction: column;
        }
        
        .pilot-details {
            grid-template-columns: 1fr;
        }
        
        .all-pilots-grid {
            grid-template-columns: 1fr;
        }
        
        .button-group {
            flex-direction: column;
        }
        
        .btn, .nav-button {
            width: 100%;
        }
        
        .btn-volver {
            position: relative;
            top: 0;
            left: 0;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        body {
            padding: 10px;
            display: block;
        }
    }
    
    @media (max-width: 768px) {
        .form-container {
            max-width: 95%;
        }
        
        .pilot-details {
            grid-template-columns: 1fr;
        }
    }
</style>
</head>
<body>
    <a href="menu.php" class="btn-volver">
        ← Volver al Menú
    </a>

    <div class="form-container">
        <h2>Editar información del piloto</h2>
        <div class="accent"></div>

        <!-- Información del piloto -->
        <div class="pilot-info active">
            <div class="pilot-header">
                <div class="pilot-number"><?php echo $piloto["num"]; ?></div>
                <div class="pilot-name"><?php echo $piloto["nombre"]; ?></div>
            </div>
            
            <div class="pilot-details">
                <div class="detail-item">
                    <span class="detail-label">Equipo</span>
                    <span class="detail-value"><?php echo $piloto["equipo"]; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nacionalidad</span>
                    <span class="detail-value"><?php echo $piloto["nacionalidad"]; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Puntos Actuales</span>
                    <span class="detail-value"><?php echo $piloto["puntos"]; ?></span>
                </div>
            </div>

            <!-- Formulario de edición -->
            <div class="edit-form">
                <h4 style="color: #2a7aa5; margin-bottom: 15px;">Actualizar Puntos</h4>
                <form method="POST" action="">
                    <input type="hidden" name="piloto_id" value="<?php echo $piloto["num"]; ?>">
                    
                    <div class="form-group">
                        <label for="puntos">Nuevos Puntos:</label>
                        <input type="number" class="form-input" id="puntos" name="puntos" 
                               value="<?php echo $piloto["puntos"]; ?>" 
                               min="0" max="1000" required>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="guardar_cambios" class="btn btn-primary">
                            Guardar Cambios
                        </button>
                        <button type="submit" name="cancelar" class="btn btn-secondary">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
