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

// ---------------------------------------------------------
// MODULO: GESTOR DE ARCHIVOS, MATERIAS Y PAGINACIÓN
// ---------------------------------------------------------

// CONFIGURACIÓN
$carpetaPrincipal = 'descargas';
$itemsPorPagina = 15; // Límite de elementos por página

// Rutas
$rutaSistema = __DIR__ . DIRECTORY_SEPARATOR . $carpetaPrincipal . DIRECTORY_SEPARATOR;
$rutaWeb = $carpetaPrincipal . '/';

$todasLasTareas = []; // Array para guardar TODO antes de paginar
$contadorGlobal = 1;  // Contador continuo

if (is_dir($rutaSistema)) {
    
    // 1. Buscamos CARPETAS (Materias)
    $carpetasMaterias = glob($rutaSistema . '*', GLOB_ONLYDIR);
    
    if ($carpetasMaterias) {
        foreach ($carpetasMaterias as $rutaCarpeta) {
            $nombreMateria = basename($rutaCarpeta);
            $archivosMateria = glob($rutaCarpeta . DIRECTORY_SEPARATOR . '*.pdf');
            
            if ($archivosMateria) {
                natcasesort($archivosMateria);
                foreach ($archivosMateria as $archivo) {
                    $nombreArchivo = basename($archivo);
                    $todasLasTareas[] = [
                        'id' => $contadorGlobal++, // Asignamos ID y aumentamos
                        'materia' => $nombreMateria,
                        'titulo' => "Tarea " . $contadorGlobal, // Opcional, si usas el ID
                        'ruta_descarga' => $rutaWeb . $nombreMateria . '/' . $nombreArchivo,
                        'nombre_real' => $nombreArchivo
                    ];
                }
            }
        }
    }

    // 2. Buscamos archivos SUELTOS (General)
    $archivosSueltos = glob($rutaSistema . '*.pdf');
    if ($archivosSueltos) {
        natcasesort($archivosSueltos);
        foreach ($archivosSueltos as $archivo) {
            $nombreArchivo = basename($archivo);
            $todasLasTareas[] = [
                'id' => $contadorGlobal++,
                'materia' => 'General',
                'titulo' => "Tarea " . $contadorGlobal,
                'ruta_descarga' => $rutaWeb . $nombreArchivo,
                'nombre_real' => $nombreArchivo
            ];
        }
    }
}

// --- LÓGICA DE PAGINACIÓN ---

// 1. Obtener página actual de la URL (si no existe, es 1)
$paginaActual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
if ($paginaActual < 1) $paginaActual = 1;

// 2. Calcular totales
$totalArchivos = count($todasLasTareas);
$totalPaginas = ceil($totalArchivos / $itemsPorPagina);

// Ajustar página actual si se pasa del total
if ($paginaActual > $totalPaginas && $totalPaginas > 0) {
    $paginaActual = $totalPaginas;
}

// 3. Cortar el array para mostrar solo lo que corresponde
$indiceInicio = ($paginaActual - 1) * $itemsPorPagina;
// array_slice extrae una parte del array
$tareasParaMostrar = array_slice($todasLasTareas, $indiceInicio, $itemsPorPagina);

// ---------------------------------------------------------
// FIN MODULO
// ---------------------------------------------------------
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Descargas</title>
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

            /* --- TABLA --- */
            .table-wrapper {
                margin-top: 20px;
                overflow-x: auto;
                min-height: 400px; /* Altura mínima para evitar saltos bruscos */
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .custom-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                text-align: left;
            }

            .custom-table th {
                background-color: #f0f7ff;
                color: #2a7aa5;
                padding: 15px;
                font-weight: 600;
                border-bottom: 2px solid #e1eff7;
                text-transform: uppercase;
                font-size: 0.85rem;
                letter-spacing: 0.5px;
            }

            .custom-table td {
                padding: 12px 15px; /* Un poco menos de padding vertical */
                border-bottom: 1px solid #f0f0f0;
                color: #555;
                vertical-align: middle;
                font-size: 0.95rem;
            }

            .custom-table tr:last-child td {
                border-bottom: none;
            }

            .custom-table tr:hover {
                background-color: #fafbfc;
            }

            /* BADGES */
            .badge-materia {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: 700;
                background-color: #e3f2fd;
                color: #1976d2;
                text-transform: capitalize;
                letter-spacing: 0.3px;
            }
            
            .badge-general {
                background-color: #f5f5f5;
                color: #616161;
            }

            .link-descarga {
                color: #2a7aa5;
                text-decoration: none;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: color 0.2s;
            }

            .link-descarga:hover {
                color: #1e6a95;
                text-decoration: underline;
            }

            .empty-state {
                color: #888;
                font-style: italic;
                padding: 40px;
                background: #f9f9f9;
                border-radius: 8px;
            }

            /* --- PAGINACIÓN --- */
            .pagination {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 8px;
                margin-top: 20px;
                padding-top: 15px;
                border-top: 1px solid #e1eff7;
            }

            .page-link {
                padding: 8px 14px;
                border: 1px solid #e1eff7;
                background-color: white;
                color: #2a7aa5;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.2s;
            }

            .page-link:hover {
                background-color: #f0f7ff;
                border-color: #2a7aa5;
            }

            .page-link.active {
                background-color: #2a7aa5;
                color: white;
                border-color: #2a7aa5;
            }

            .page-link.disabled {
                background-color: #f9f9f9;
                color: #ccc;
                cursor: not-allowed;
                border-color: #eee;
                pointer-events: none;
            }

            .page-info {
                margin: 0 10px;
                color: #888;
                font-size: 14px;
            }
            
            @media (max-width: 480px) {
                .form-container { padding: 30px 15px; }
                h2 { font-size: 24px; }
                .custom-table th, .custom-table td { padding: 10px; }
                .badge-materia { font-size: 0.7rem; }
            }
    </style>
</head>
<body>
    <a href="menu.php" class="btn-volver">
        ← Volver al Menú
    </a>

    <div class="form-container">
        <h2>Mis Asignaciones</h2>
        <div class="accent"></div>
        
        <p class="subtitle">
            Mostrando <?php echo count($tareasParaMostrar); ?> de <?php echo $totalArchivos; ?> archivos encontrados
        </p>

        <div class="table-wrapper">
            <?php if (!empty($tareasParaMostrar)): ?>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 25%;">Materia</th>
                            <th>Archivo / Recurso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tareasParaMostrar as $tarea): ?>
                            <tr>
                                <td>
                                    <span style="color: #999; font-weight: bold;">
                                        <?php echo $tarea['id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $claseBadge = ($tarea['materia'] === 'General') ? 'badge-materia badge-general' : 'badge-materia';
                                    ?>
                                    <span class="<?php echo $claseBadge; ?>">
                                        <?php echo htmlspecialchars($tarea['materia']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo htmlspecialchars($tarea['ruta_descarga']); ?>" 
                                       class="link-descarga" 
                                       download 
                                       target="_blank">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                          <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                          <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                       </svg>
                                       <?php echo htmlspecialchars($tarea['nombre_real']); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- CONTROLES DE PAGINACIÓN -->
                <?php if ($totalPaginas > 1): ?>
                <div class="pagination">
                    <!-- Botón Anterior -->
                    <?php if ($paginaActual > 1): ?>
                        <a href="?pag=<?php echo $paginaActual - 1; ?>" class="page-link">&laquo; Anterior</a>
                    <?php else: ?>
                        <span class="page-link disabled">&laquo; Anterior</span>
                    <?php endif; ?>

                    <!-- Números de página -->
                    <?php for($i = 1; $i <= $totalPaginas; $i++): ?>
                        <a href="?pag=<?php echo $i; ?>" class="page-link <?php echo ($i === $paginaActual) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Botón Siguiente -->
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="?pag=<?php echo $paginaActual + 1; ?>" class="page-link">Siguiente &raquo;</a>
                    <?php else: ?>
                        <span class="page-link disabled">Siguiente &raquo;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    No se encontraron archivos en el sistema.
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>