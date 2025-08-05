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

// Configuración de correo (SMTP)
$mailConfig = [
    'smtp_host' => 'smtp.gmail.com',    // Servidor SMTP
    'smtp_port' => 587,                 // Puerto SMTP (587 para TLS)
    'smtp_user' => 'tucorreo@gmail.com', // Tu correo electrónico
    'smtp_pass' => 'tucontraseña',       // Tu contraseña
    'from_email' => 'tucorreo@gmail.com', // Correo remitente
    'from_name' => 'Sistema de Backups'  // Nombre remitente
];
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
        $backupFile .= '.gz';
        
        // Preguntar por correo electrónico para enviar el backup
        if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $emailSent = sendBackupByEmail($_POST['email'], $backupFile);
            if ($emailSent) {
                header("Location: backup_manager.php?mensaje=Backup+creado+y+enviado+exitosamente");
            } else {
                header("Location: backup_manager.php?mensaje=Backup+creado+pero+error+al+enviar+por+correo");
            }
        } else {
            header("Location: backup_manager.php?mensaje=Backup+creado+exitosamente");
        }
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

// Función para enviar backup por correo
function sendBackupByEmail($toEmail, $backupFilePath) {
    global $mailConfig;
    
    // Verificar que el archivo existe
    if (!file_exists($backupFilePath)) {
        return false;
    }
    
    // Configurar PHPMailer
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $mailConfig['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['smtp_user'];
        $mail->Password = $mailConfig['smtp_pass'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $mailConfig['smtp_port'];
        
        // Remitente
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        
        // Destinatario
        $mail->addAddress($toEmail);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Copia de seguridad de la base de datos - ' . date('Y-m-d H:i:s');
        $mail->Body    = 'Se adjunta la copia de seguridad de la base de datos generada el ' . date('Y-m-d H:i:s');
        $mail->AltBody = 'Se adjunta la copia de seguridad de la base de datos';
        
        // Adjuntar el archivo
        $mail->addAttachment($backupFilePath);
        
        // Enviar el correo
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Registrar el error
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
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
    <title>Gestor de Backups</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Gestor de Backups</h1>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <p class="success"><?= htmlspecialchars(urldecode($_GET['mensaje'])) ?></p>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <p class="error"><?= htmlspecialchars(urldecode($_GET['error'])) ?></p>
        <?php if (isset($_GET['debug'])): ?>
            <pre><?= htmlspecialchars(urldecode($_GET['debug'])) ?></pre>
        <?php endif; ?>
    <?php endif; ?>
    
    <div>
        <h2>Crear nuevo backup</h2>
        <form action="backup_manager.php?action=create" method="post">
            <label for="email">Enviar backup a (opcional):</label>
            <input type="email" name="email" id="email" placeholder="correo@destino.com">
            <button type="submit">Crear Backup</button>
        </form>
    </div>
    
    <div>
        <h2>Backups existentes</h2>
        <?php
        if (file_exists($backupDir)) {
            $files = glob($backupDir . "*.{sql,sql.gz}", GLOB_BRACE);
            if (count($files) > 0) {
                echo '<table>';
                echo '<tr><th>Archivo</th><th>Tamaño</th><th>Fecha</th><th>Acciones</th></tr>';
                
                foreach ($files as $file) {
                    $filename = basename($file);
                    $filesize = formatFileSize(filesize($file));
                    $filedate = date("Y-m-d H:i:s", filemtime($file));
                    
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($filename) . '</td>';
                    echo '<td>' . htmlspecialchars($filesize) . '</td>';
                    echo '<td>' . htmlspecialchars($filedate) . '</td>';
                    echo '<td class="actions">';
                    echo '<a href="backup_manager.php?action=download&file=' . urlencode($filename) . '">Descargar</a> | ';
                    echo '<a href="backup_manager.php?action=restore&file=' . urlencode($filename) . '" onclick="return confirm(\'¿Restaurar este backup? Se sobrescribirán los datos actuales.\')">Restaurar</a> | ';
                    echo '<a href="backup_manager.php?action=delete&file=' . urlencode($filename) . '" onclick="return confirm(\'¿Eliminar este backup permanentemente?\')">Eliminar</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
            } else {
                echo '<p>No hay backups disponibles.</p>';
            }
        } else {
            echo '<p>El directorio de backups no existe.</p>';
        }
        ?>
    </div>
</body>
</html>