<?php
session_start();

// Contraseñas iniciales
$default_passwords = [
    'Direccion' => '1703',
    'Docente' => 'momj'
];

// Cargar contraseñas desde archivo o usar las predeterminadas
if (file_exists('passwords.json')) {
    $passwords = json_decode(file_get_contents('passwords.json'), true);
} else {
    $passwords = $default_passwords;
    file_put_contents('passwords.json', json_encode($passwords));
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['change_password'])) {
    $userType = $_POST['user_type'];
    $password = $_POST['password'];

    if (isset($passwords[$userType]) && $password == $passwords[$userType]) {
        $_SESSION['user_type'] = $userType;
        header('Location: index.php');
        exit();
    } else {
        $error = 'Credenciales incorrectas';
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_direccion_pass = $_POST['current_direccion_pass'];
    $user_type_to_change = $_POST['user_type_to_change'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if ($current_direccion_pass != $passwords['Direccion']) {
        $password_error = 'La contraseña actual de Dirección es incorrecta';
    } elseif ($new_password != $confirm_password) {
        $password_error = 'Las nuevas contraseñas no coinciden';
    } elseif (empty($new_password)) {
        $password_error = 'La nueva contraseña no puede estar vacía';
    } else {
        // Actualizar contraseña
        $passwords[$user_type_to_change] = $new_password;
        file_put_contents('passwords.json', json_encode($passwords));
        $password_success = 'Contraseña actualizada correctamente';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --primary-hover: #1a252f;
            --secondary-color: rgb(240, 240, 240);
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        /* Estilos generales */
        body {
            background-color: var(--secondary-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        /* Contenedor principal */
        .login-container {
            max-width: 480px;
            width: 100%;
            margin: 0 auto;
        }

        /* Tarjeta de login */
        .login-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .login-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        /* Encabezado de la tarjeta */
        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.75rem;
            text-align: center;
        }

        .card-header h2 {
            font-weight: 600;
            margin: 0;
            font-size: 1.75rem;
        }

        /* Cuerpo de la tarjeta */
        .card-body {
            padding: 2rem;
            background-color: white;
        }

        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-container img {
            max-height: 80px;
            width: auto;
        }

        /* Formularios */
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            transition: var(--transition);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.15);
        }

        /* Botones */
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: var(--border-radius);
            padding: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-outline-secondary {
            border-radius: var(--border-radius);
        }

        /* Alertas */
        .alert {
            border-radius: var(--border-radius);
            padding: 1rem;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: none;
            color: #dc3545;
        }

        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            border: none;
            color: #198754;
        }

        /* Enlaces */
        .link-container {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #eee;
        }

        .link-container a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
        }

        .link-container a:hover {
            color: var(--primary-hover);
        }

        .link-container a i {
            margin-right: 0.5rem;
        }

        /* Opciones de tipo de usuario */
        .user-type-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .user-type-option {
            flex: 1;
            text-align: center;
        }

        .user-type-option input[type="radio"] {
            display: none;
        }

        .user-type-option label {
            display: block;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .user-type-option input[type="radio"]:checked+label {
            border-color: var(--primary-color);
            background-color: rgba(44, 62, 80, 0.05);
        }

        .password-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 1.25rem;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            background: none;
            border: none;
            padding: 0;
            z-index: 2;
            /* Asegura que esté sobre el input */
        }

    

        .password-container input[type="password"],
        .password-container input[type="text"] {
            padding-right: 45px;
        }



        /* Modal */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-close {
            filter: invert(1);
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="logo-container">
                <img src="media/Logito.png" alt="Logo" class="img-fluid">
            </div>

            <div class="card login-card">
                <div class="card-header">
                    <h2>Menú de Acceso</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($password_error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $password_error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($password_success)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $password_success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-4">
                            <h5 class="mb-3">Seleccione su tipo de usuario:</h5>
                            <div class="user-type-options">
                                <div class="user-type-option">
                                    <input type="radio" name="user_type" id="direccion" value="Direccion" required>
                                    <label for="direccion">
                                        <i class="bi bi-person-badge me-2"></i>Dirección
                                    </label>
                                </div>
                                <div class="user-type-option">
                                    <input type="radio" name="user_type" id="docente" value="Docente" required>
                                    <label for="docente">
                                        <i class="bi bi-person-video3 me-2"></i>Docente
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="password-container">
                            <label for="password">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>

                        </div>

                        <div class="d-flex justify-content-between gap-3 mt-4">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Ingresar
                            </button>

                            <button type="button" class="btn btn-outline-secondary flex-grow-1" data-bs-toggle="modal"
                                data-bs-target="#changePasswordModal">
                                <i class="bi bi-key me-2"></i> Cambiar contraseña
                            </button>
                        </div>
                    </form>

                    <div class="link-container">
                        <a href="agradecimiento.php">
                            <i class="bi bi-heart-fill"></i> Agradecimientos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Cambiar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="login.php">
                    <div class="modal-body">
                        <input type="hidden" name="change_password" value="1">

                        <div class="mb-3">
                            <label for="current_direccion_pass" class="form-label">Contraseña actual de
                                Dirección</label>
                            <input type="password" class="form-control" id="current_direccion_pass"
                                name="current_direccion_pass" required>
                        </div>

                        <div class="mb-3">
                            <label for="user_type_to_change" class="form-label">Tipo de usuario a cambiar</label>
                            <select class="form-select" id="user_type_to_change" name="user_type_to_change" required>
                                <option value="Direccion">Dirección</option>
                                <option value="Docente">Docente</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility - Versión corregida
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        });
    </script>
</body>

</html>