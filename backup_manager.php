<?php


// =============================================
// CONFIGURACIÓN PRINCIPAL (¡MODIFICA ESTOS VALORES!)
// =============================================
$db_host = "localhost";    // Servidor de la BD
$db_user = "root";         // Usuario de MySQL
$db_pass = "";             // Contraseña (vacía por defecto en XAMPP)
$db_name = "ponys2";        // Nombre EXACTO de tu base de datos
$backupDir = "backups/";   // Carpeta donde se guardarán los backups
$mysqldumpPath = '"C:\xampp\mysql\bin\mysqldump"'; // Ruta completa a mysqldump
$mysqlPath = '"C:\xampp\mysql\bin\mysql"';         // Ruta completa a mysql
// =============================================

// Verificar conexión y existencia de la BD
function verificarBaseDatos()
{
    global $db_host, $db_user, $db_pass, $db_name;

    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        die("Error de conexión a MySQL: " . $conn->connect_error);
    }

    if (!$conn->select_db($db_name)) {
        die("Error: La base de datos '$db_name' no existe o no tienes permisos");
    }
    $conn->close();
}



// Procesar acciones
$action = isset($_GET['action']) ? $_GET['action'] : null;
$file = isset($_GET['file']) ? $_GET['file'] : null;
$allowedActions = ['create', 'download', 'delete', 'restore'];

if ($action && in_array($action, $allowedActions)) {
    // Validar y sanitizar el nombre del archivo
    if ($file) {
        $file = basename($file); // Previene path traversal
        $filePath = $backupDir . $file;

        if (!file_exists($filePath)) {
            header("Location: backup_manager.php?error=Archivo+no+encontrado");
            exit();
        }
    }

    switch ($action) {
        case 'create':
            createBackup();
            break;
        case 'download':
            downloadBackup($filePath);
            break;
        case 'delete':
            deleteBackup($filePath);
            break;
        case 'restore':
            restoreBackup($filePath);
            break;
    }
}

// Función para crear backup (versión mejorada)
function createBackup()
{
    global $backupDir, $db_host, $db_user, $db_pass, $db_name, $mysqldumpPath;

    verificarBaseDatos(); // Verificar que la BD existe antes de continuar

    $backupFile = $backupDir . "backup_" . date("Y-m-d_H-i-s") . ".sql";

    // Crear directorio si no existe
    if (!file_exists($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            showError("No se pudo crear el directorio de backups");
        }
    }

    // Comando con verificación de errores
    $command = "$mysqldumpPath --no-defaults --user=$db_user --password=$db_pass --host=$db_host $db_name > $backupFile 2>&1";
    system($command, $output);

    // Verificación exhaustiva del backup
    if ($output === 0 && file_exists($backupFile) && filesize($backupFile) > 1024) {
        // Comprimir el backup
        exec("gzip -9 $backupFile");
        header("Location: backup_manager.php?mensaje=Backup+creado+exitosamente");
    } else {
        $errorContent = file_exists($backupFile) ? file_get_contents($backupFile) : "No se generó archivo";
        $errorMsg = "Error al crear backup (Código: $output). ";
        $errorMsg .= "Tamaño archivo: " . (file_exists($backupFile) ? filesize($backupFile) : '0') . " bytes. ";
        $errorMsg .= "Posible solución: Verifica que la base de datos '$db_name' exista y que el usuario '$db_user' tenga permisos.";

        // Guardar log de error
        file_put_contents($backupDir . "error_log.txt", date("[Y-m-d H:i:s] ") . $errorMsg . "\n" . $errorContent, FILE_APPEND);

        // Eliminar archivo corrupto si existe
        if (file_exists($backupFile)) {
            unlink($backupFile);
        }

        showError($errorMsg, $errorContent);
    }
    exit();
}

// Función para descargar backup
function downloadBackup($filePath)
{
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }
    showError("El archivo solicitado no existe");
}

// Función para eliminar backup
function deleteBackup($filePath)
{
    if (unlink($filePath)) {
        header("Location: backup_manager.php?mensaje=Backup+eliminado+exitosamente");
    } else {
        showError("Error al eliminar el archivo de backup");
    }
    exit();
}

// Función para restaurar backup
function restoreBackup($filePath)
{
    global $db_host, $db_user, $db_pass, $db_name, $mysqlPath;

    verificarBaseDatos(); // Verificar que la BD existe antes de restaurar

    // Verificar si es un .sql o .sql.gz
    if (strpos($filePath, '.gz') !== false) {
        $uncompressedFile = str_replace('.gz', '', $filePath);
        exec("gunzip -c $filePath > $uncompressedFile");
        $filePath = $uncompressedFile;
    }

    // Comando para restaurar con verificación de errores
    $command = "$mysqlPath --no-defaults --user=$db_user --password=$db_pass --host=$db_host $db_name < $filePath 2>&1";
    system($command, $output);

    if ($output === 0) {
        header("Location: backup_manager.php?mensaje=Base+de+datos+restaurada+exitosamente");
    } else {
        $errorContent = file_exists($filePath) ? file_get_contents($filePath) : "No se pudo leer el archivo";
        showError("Error al restaurar backup (Código: $output). Verifica que el archivo no esté corrupto.", $errorContent);
    }
    exit();
}

// Función para mostrar errores
function showError($message, $debugInfo = "")
{
    $url = "backup_manager.php?error=" . urlencode($message);
    if (!empty($debugInfo)) {
        $url .= "&debug=" . urlencode($debugInfo);
    }
    header("Location: $url");
    exit();
}

// Función para formatear el tamaño del archivo
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: rgb(238, 238, 238);
            padding-top: 20px;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card-header {
            background-color: #2c3e50;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .btn-primary {
            background-color: #2c3e50;
            border: none;
        }

        .btn-primary:hover {
            background-color: #1a252f;
        }

        .backup-file {
            font-family: monospace;
        }

        .file-actions {
            min-width: 180px;
            text-align: right;
        }

        .file-size {
            color: #6c757d;
            font-size: 0.9em;
        }

        pre.error-log {
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }

        .config-table td:first-child {
            font-weight: bold;
            width: 40%;
        }

        .backup-reminder {
            background-color: #fff3cd;
            background-image: repeating-linear-gradient(-45deg,
                    transparent,
                    transparent 10px,
                    rgba(255, 215, 0, 0.1) 10px,
                    rgba(255, 215, 0, 0.1) 20px);
            border-left: 5px solid #ffc107 !important;
            animation: pulse 2s infinite;
        }

        .backup-reminder h4 {
            color: #856404;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1.2rem;
        }

        .backup-reminder .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            font-weight: bold;
            min-width: 180px;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
        }

        .backup-reminder .btn-danger:hover {
            background-color: rgb(117, 215, 137);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(47, 255, 0, 0.4);
            border-color: rgb(78, 220, 53);
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
            }
        }

        .text-underline {
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 3px;
        }

        .backup-reminder {
            animation: breathe 3s ease-in-out infinite, border-pulse 3s ease infinite;
            transform-origin: center;
            transition: all 0.3s ease;
        }


        .backup-reminder:hover {
            animation: breathe 1.5s ease-in-out infinite, border-pulse 1.5s ease infinite;
        }

        .backup-reminder .btn-danger {
            animation: btn-pulse 1.8s ease infinite;
            transition: all 0.3s;
        }

        @keyframes btn-pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            }

            50% {
                transform: scale(1.08);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            }
        }

        .backup-reminder .btn-danger:hover {
            animation: btn-pulse 0.8s ease infinite;
            transform: scale(1.05);
        }
        .navbar-brand img {
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.2);
        }

        .center-nav {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .user-icon {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        /* Estilos para móvil */
        @media (max-width: 991.98px) {
            .center-nav {
                justify-content: flex-start;
                /* Alinear a la izquierda en móvil */
            }

            .navbar-nav.ms-auto {
                margin-left: 0 !important;
                /* Eliminar margen automático en móvil */
            }

            .user-icon {
                padding: 0.5rem 0;
                /* Añadir espacio vertical en móvil */
            }
        }
    </style>
    <link rel="stylesheet" href="css/navbar.css">
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
   
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="media/logoinc.png" alt="Logo" height="40">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Contenedor para centrar los elementos principales -->
            <div class="center-nav">
                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link" href="create.php"><i class="bi bi-plus-circle"></i> Crear Alumno</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="redireccion.php"><i class="bi bi-book"></i> Nivel de lectura</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="asistenciaDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-clipboard-check"></i> Asistencia
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="PasarLista.php"><i class="bi bi-list-check"></i> Pasar
                                    Lista</a></li>
                            <li><a class="dropdown-item" href="reporteDetallado.php"><i
                                        class="bi bi-clipboard-data"></i>
                                    Gestión de asistencia</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportesDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-exclamation-triangle"></i> Reportes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="ReportarAlumno.php"><i class="bi bi-plus-circle"></i>
                                    Reportar alumno</a></li>
                            <li><a class="dropdown-item" href="GestionReporte.php"><i class="bi bi-gear"></i> Gestión de
                                    reportes</a></li>
                            <li><a class="dropdown-item" href="suspensiones.php"><i class="bi bi-clock-history"></i>
                                    Historial de reportes</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="masDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i> Más
                        </a>
                        <ul class="dropdown-menu">

                            <li><a class="dropdown-item" href="editarAlumnos.php"><i class="bi bi-arrow-left-right"></i>
                                    Mover alumnos</a></li>
                            <li><a class="dropdown-item" href="Cambio_avanzado.php"><i
                                        class="bi bi-arrow-left-right"></i>
                                    Mover alumnos (Avanzado)</a></li>
                            <li><a class="dropdown-item" href="baja_alumno.php"><i class="bi bi-person-x"></i> Dar de
                                    baja a
                                    alumno</a></li>
                            <li><a class="dropdown-item" href="ver_bajas.php"><i class="bi bi-people"></i> Gestión de
                                    bajas</a></li>

                    </li>
                    <li><a class="dropdown-item" href="familias.php"><i class="bi bi-people-fill"></i> Familias</a>
                    </li>
                    <li><a class="dropdown-item" href="Selector_Grupos_Promedios.php"><i
                                class="bi bi-journal-check"></i> Calificaciones</a></li>
                    <hr class="dropdown-divider">
                    <li><a class="dropdown-item" href="actualizarlosgrados.php"><i class="bi bi-arrow-up-circle"></i>
                            Actualizar
                            grado</a></li>
                    <li>
                    <li><a class="dropdown-item" href="backup_manager.php"><i class="bi bi-database"></i> Backup</a></li>

                </ul>
                </li>
                </ul>
            </div>

            <!-- Ícono de usuario -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link user-icon" href="login.php" title="Usuario">
                        <i class="bi bi-person-circle"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <div class="container">


        <br>
        <div class="backup-reminder alert alert-warning border-warning border-3 shadow-lg">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="alert-heading mb-1"><i class="bi bi-exclamation-triangle-fill"></i> ¡BACKUP
                        OBLIGATORIO!</h4>
                    <p class="mb-0 fw-bold">Por favor, <u>CREA un backup</u> al menos <span class="text-danger">una vez
                            al día</span> en cuanto termines la jornada laboral.</p>
                    <p class="small mt-1 mb-0">Este proceso es <strong>fundamental</strong> para proteger la información
                        institucional.</p>
                </div>
                <div class="ms-3">
                    <a href="backup_manager.php?action=create" class="btn btn-danger">
                        <i class="bi bi-database-fill-down"></i> Crear Backup Ahora
                    </a>
                </div>
            </div>
        </div>
        <br>
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars(urldecode($_GET['mensaje'])) ?></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <h5><i class="bi bi-exclamation-triangle"></i> Error</h5>
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>

                <?php if (isset($_GET['debug'])): ?>
                    <hr>
                    <h6>Detalles técnicos:</h6>
                    <pre class="error-log"><?= htmlspecialchars(urldecode($_GET['debug'])) ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-database-check"></i> Gestión de Backups</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>Backups de la Base de Datos</h5>
                    <div>
                        <a href="backup_manager.php?action=create" class="btn btn-primary me-2">
                            <i class="bi bi-plus-circle"></i> Crear Backup
                        </a>
                        <a href="backup_manager.php?action=create&debug=1" class="btn btn-outline-secondary"
                            title="Modo debug">
                            <i class="bi bi-bug"></i>
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre del Archivo</th>
                                <th>Tamaño</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $backups = glob($backupDir . "*.sql*");
                            if (count($backups) > 0) {
                                rsort($backups);
                                foreach ($backups as $backup) {
                                    $fileName = basename($backup);
                                    $fileSize = filesize($backup);
                                    $fileDate = date("Y-m-d H:i:s", filemtime($backup));
                                    ?>
                                    <tr>
                                        <td class="backup-file"><?= $fileName ?></td>
                                        <td class="file-size"><?= formatFileSize($fileSize) ?></td>
                                        <td><?= $fileDate ?></td>
                                        <td class="file-actions text-start d-flex gap-2">
                                            <a href="backup_manager.php?action=download&file=<?= urlencode($fileName) ?>"
                                                class="btn btn-sm btn-primary" title="Descargar">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <a href="backup_manager.php?action=restore&file=<?= urlencode($fileName) ?>"
                                                class="btn btn-sm btn-warning"
                                                onclick="return confirm('¿Restaurar este backup? Se sobrescribirán los datos actuales.')"
                                                title="Restaurar">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                            <a href="backup_manager.php?action=delete&file=<?= urlencode($fileName) ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar este backup permanentemente?')"
                                                title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay backups disponibles</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-gear"></i> Configuración Actual</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered config-table">
                    <tr>
                        <td>Servidor MySQL:</td>
                        <td><?= $db_host ?></td>
                    </tr>
                    <tr>
                        <td>Base de datos:</td>
                        <td><?= $db_name ?></td>
                    </tr>
                    <tr>
                        <td>Usuario MySQL:</td>
                        <td><?= $db_user ?></td>
                    </tr>
                    <tr>
                        <td>Ubicación backups:</td>
                        <td><?= realpath($backupDir) ?: $backupDir ?></td>
                    </tr>
                    <tr>
                        <td>Ruta mysqldump:</td>
                        <td><?= $mysqldumpPath ?></td>
                    </tr>
                </table>

                <div class="alert alert-warning mt-3">
                    <h5><i class="bi bi-exclamation-triangle"></i> Solución de problemas</h5>
                    <p>Si recibes errores:</p>
                    <ol>
                        <li>Verifica que la base de datos <strong><?= $db_name ?></strong> exista en phpMyAdmin</li>
                        <li>Comprueba que el usuario <strong><?= $db_user ?></strong> tenga permisos sobre la BD</li>
                        <li>Asegúrate que el directorio <strong><?= $backupDir ?></strong> tenga permisos de escritura
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>