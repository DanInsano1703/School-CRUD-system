<?php
// navbar.php
session_start();
if (!isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

$userType = $_SESSION['user_type'];
?>

<head>
    <link rel="stylesheet" href="css/navbar.css">

      <style>
        

.navbar-custom {
    background-color: #2c3e50;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-custom .navbar-brand {
    margin-right: auto;
    margin-left: 0 !important;
}


.navbar-custom .navbar-nav {
    margin-left: auto;
    margin-right: auto;
}

.navbar-custom .nav-link {
    color: white !important;
    padding: 0.5rem 1rem;
}

.navbar-custom .nav-link:hover {
    color:rgb(0, 187, 255) !important;
}

.navbar-custom .dropdown-menu {
    background-color: #2c3e50;
    border: none;
}

.navbar-custom .dropdown-item {
    color: white;
}

.navbar-custom .dropdown-item:hover {
    background-color: #1a252f;
}

@media (max-width: 992px) {
    .navbar-custom .navbar-nav {
        margin-left: 0;
        margin-right: 0;
    }
}
.navbar-brand img {
    transition: transform 0.3s ease; /* Añade una transición suave */
}

.navbar-brand img:hover {
    transform: scale(1.2); /* Aumenta el tamaño al 120% cuando el cursor está encima */
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

</head>
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
                    <?php if ($userType == 'Direccion'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="create.php"><i class="bi bi-plus-circle"></i> Crear Alumno</a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Nivel de lectura (visible para todos) -->
                    <li class="nav-item">
                        <a class="nav-link" href="redireccion.php"><i class="bi bi-book"></i> Nivel de lectura</a>
                    </li>

                    <!-- Asistencia (visible para todos) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="asistenciaDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-clipboard-check"></i> Asistencia
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="PasarLista.php"><i class="bi bi-list-check"></i> Pasar Lista</a></li>
                            <li><a class="dropdown-item" href="reporteDetallado.php"><i class="bi bi-clipboard-data"></i> Gestión de asistencia</a></li>
                        </ul>
                    </li>

                    <!-- Reportes (visible para todos) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-exclamation-triangle"></i> Reportes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="ReportarAlumno.php"><i class="bi bi-plus-circle"></i> Reportar alumno</a></li>
                            <li><a class="dropdown-item" href="GestionReporte.php"><i class="bi bi-gear"></i> Gestión de reportes</a></li>
                            <li><a class="dropdown-item" href="suspensiones.php"><i class="bi bi-clock-history"></i> Historial de reportes</a></li>
                        </ul>
                    </li>

                    <!-- Menú Más (con contenido diferente según userType) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="masDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i> Más
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($userType == 'Direccion'): ?>
                                <!-- Opciones solo para Dirección -->
                                <li><a class="dropdown-item" href="editarAlumnos.php"><i class="bi bi-arrow-left-right"></i> Mover alumnos</a></li>
                                <li><a class="dropdown-item" href="Cambio_avanzado.php"><i class="bi bi-arrow-left-right"></i> Mover alumnos (Avanzado)</a></li>
                                <li><a class="dropdown-item" href="baja_alumno.php"><i class="bi bi-person-x"></i> Dar de baja a alumno</a></li>
                                <li><a class="dropdown-item" href="ver_bajas.php"><i class="bi bi-people"></i> Gestión de bajas</a></li>
                                
                            <?php endif; ?>
                            
                            <!-- Opciones para todos los usuarios -->
                            <li><a class="dropdown-item" href="familias.php"><i class="bi bi-people-fill"></i> Familias</a></li>
                            <li><a class="dropdown-item" href="Selector_Grupos_Promedios.php"><i class="bi bi-journal-check"></i> Calificaciones</a></li>
                            <?php if ($userType == 'Direccion'): ?>
                            <!-- Opciones solo para Dirección -->
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="actualizarlosgrados.php"><i class="bi bi-arrow-up-circle"></i> Actualizar grado</a></li>
                            <li><a class="dropdown-item" href="backup_manager.php"><i class="bi bi-database"></i> Backup</a></li>
                            <?php endif; ?>
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

