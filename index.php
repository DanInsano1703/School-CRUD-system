<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_type'])) {
    header('Location: login.php');
    exit();
}

$userType = $_SESSION['user_type'];
$searchTerm = '';
$whereClause = '';

// Verifica si hay un mensaje de éxito o error en la URL
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Procesar la búsqueda si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchTerm = $conn->real_escape_string($searchTerm);

    if (!empty($searchTerm)) {
        $whereClause = "WHERE RICE LIKE '%$searchTerm%' OR CURP LIKE '%$searchTerm%' OR nombre LIKE '%$searchTerm%'";
    }
}

// Procesar el filtro por sección si se ha seleccionado
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['seccion']) && $_GET['seccion'] != '') {
    $seccionFiltro = $_GET['seccion'];
    $seccionFiltro = $conn->real_escape_string($seccionFiltro);

    if (!empty($seccionFiltro)) {
        if (!empty($whereClause)) {
            $whereClause .= " AND seccion = '$seccionFiltro'";
        } else {
            $whereClause = "WHERE seccion = '$seccionFiltro'";
        }
    }
}

// Procesar el filtro por grado si se ha seleccionado
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['grado']) && $_GET['grado'] != '') {
    $gradoFiltro = $_GET['grado'];
    $gradoFiltro = $conn->real_escape_string($gradoFiltro);

    if (!empty($gradoFiltro)) {
        if (!empty($whereClause)) {
            $whereClause .= " AND grado = '$gradoFiltro'";
        } else {
            $whereClause = "WHERE grado = '$gradoFiltro'";
        }
    }
}

// Procesar el filtro por sexo si se ha seleccionado
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['sexo']) && $_GET['sexo'] != '') {
    $sexoFiltro = $_GET['sexo'];
    $sexoFiltro = $conn->real_escape_string($sexoFiltro);

    if (!empty($sexoFiltro)) {
        if (!empty($whereClause)) {
            $whereClause .= " AND sexo = '$sexoFiltro'";
        } else {
            $whereClause = "WHERE sexo = '$sexoFiltro'";
        }
    }
}

// Procesar el filtro por tipo de sangre si se ha seleccionado
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] != '') {
    $tipoSangreFiltro = $_GET['tipo_sangre'];
    $tipoSangreFiltro = $conn->real_escape_string($tipoSangreFiltro);

    if (!empty($tipoSangreFiltro)) {
        if (!empty($whereClause)) {
            $whereClause .= " AND tipo_sangre = '$tipoSangreFiltro'";
        } else {
            $whereClause = "WHERE tipo_sangre = '$tipoSangreFiltro'";
        }
    }
}

// Procesar el filtro por ejido si se ha proporcionado
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['ejido']) && $_GET['ejido'] != '') {
    $ejidoFiltro = $_GET['ejido'];
    $ejidoFiltro = $conn->real_escape_string($ejidoFiltro);

    if (!empty($ejidoFiltro)) {
        if (!empty($whereClause)) {
            $whereClause .= " AND ejido LIKE '%$ejidoFiltro%'";
        } else {
            $whereClause = "WHERE ejido LIKE '%$ejidoFiltro%'";
        }
    }
}

// Procesar el filtro por si tiene hermanos
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['tiene_hermanos']) && $_GET['tiene_hermanos'] != '') {
    $tieneHermanosFiltro = $_GET['tiene_hermanos'];
    $tieneHermanosFiltro = $conn->real_escape_string($tieneHermanosFiltro);

    if (!empty($tieneHermanosFiltro)) {
        if (!empty($whereClause)) {
            $whereClause .= " AND tiene_hermanos = '$tieneHermanosFiltro'";
        } else {
            $whereClause = "WHERE tiene_hermanos = '$tieneHermanosFiltro'";
        }
    }
}

// Consulta SQL final con ordenamiento alfabético
$sql = "SELECT * FROM alumnos $whereClause ORDER BY nombre ASC";
$result = $conn->query($sql);
$num_rows = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <?php include 'funciones/icon.php'; ?>
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 0px;
            
           
        }

        .navbar-custom {
            background-color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .navbar-brand {
            margin-right: 0;
        }

        .navbar-custom .navbar-nav {
            margin-left: auto;
            margin-right: auto;
        }

        .navbar-custom .nav-link {
            color: white;
            padding: 0.5rem 1rem;
        }

        .navbar-custom .dropdown-menu {
            background-color: #2c3e50;
        }

        .navbar-custom .dropdown-item {
            color: white;
        }

        .navbar-custom .dropdown-item:hover {
            background-color: #1a252f;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
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

        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;

        }

        .table-scroll {
            overflow-x: auto;
            width: 100%;
            margin-bottom: 10px;
        }

        .table {
            width: max-content;
            min-width: 100%;
            white-space: nowrap;
        }

        .table th {
            background-color: #2c3e50;
            color: white;
            position: sticky;
            top: 0;
        }

        .filter-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hidden {
            display: none;
        }

        .search-info {
            margin-bottom: 15px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-link {
            margin-right: 8px;
            color: #2c3e50;
        }

        .action-link:hover {
            color: #1a252f;
        }

        .horizontal-scroll {
            overflow-x: auto;
            margin-bottom: 5px;
            padding: 5px 0;
        }

        .scroll-handle {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            cursor: grab;
            position: relative;
        }

        .scroll-handle:hover {
            background-color: #dee2e6;
        }

        @media (max-width: 992px) {
            .navbar-custom .navbar-nav {
                margin-left: 0;
                margin-right: 0;
            }
        }

        .navbar-brand img {
            transition: transform 0.3s ease;
            /* Añade una transición suave */
        }

        .navbar-brand img:hover {
            transform: scale(1.2);
            /* Aumenta el tamaño al 120% cuando el cursor está encima */
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

  

    <div class="container-fluid">

        <!-- Mensajes de éxito o error -->
        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-people-fill"></i> Mis Alumnos</h2>
            </div>
            <div class="card-body">
                <!-- Formulario de filtros compacto y alineado -->
                <div class="filter-form">
                    <h4><i class="bi bi-funnel"></i> Buscar alumno/s</h4>
                    <form method="GET" action="index.php" class="row gx-2 gy-1 align-items-end">
                        <!-- Campos en línea -->
                        <div class="col-auto">
                            <label for="search" class="form-label small mb-0">Nombre/RICE/CURP:</label>
                            <input type="text" class="form-control form-control-sm" name="search"
                                placeholder="Buscar..." value="<?php echo htmlspecialchars($searchTerm); ?>"
                                style="width: 160px">
                        </div>
                        <div class="col-auto">
                            <label for="ejido" class="form-label small mb-0">Ejido:</label>
                            <input type="text" class="form-control form-control-sm" name="ejido" placeholder="Ejido"
                                value="<?php echo htmlspecialchars(isset($_GET['ejido']) ? $_GET['ejido'] : ''); ?>"
                                style="width: 160px">
                        </div>
                        <div class="col-auto">
                            <label for="grado" class="form-label small mb-0">Grado:</label>
                            <select class="form-select form-select-sm" name="grado" id="grado" style="width: 85px">
                                <option value="">Todos</option>
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo (isset($_GET['grado']) && $_GET['grado'] == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>°
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="seccion" class="form-label small mb-0">Sección:</label>
                            <select class="form-select form-select-sm" name="seccion" id="seccion" style="width: 85px">
                                <option value="">Todas</option>
                                <option value="A" <?php echo (isset($_GET['seccion']) && $_GET['seccion'] == 'A') ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo (isset($_GET['seccion']) && $_GET['seccion'] == 'B') ? 'selected' : ''; ?>>B</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="sexo" class="form-label small mb-0">Sexo:</label>
                            <select class="form-select form-select-sm" name="sexo" id="sexo" style="width: 85px">
                                <option value="">Todos</option>
                                <option value="Hombre" <?php echo (isset($_GET['sexo']) && $_GET['sexo'] == 'Hombre') ? 'selected' : ''; ?>>H</option>
                                <option value="Mujer" <?php echo (isset($_GET['sexo']) && $_GET['sexo'] == 'Mujer') ? 'selected' : ''; ?>>M</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="tipo_sangre" class="form-label small mb-0">Grupo sanguíneo:</label>
                            <select class="form-select form-select-sm" name="tipo_sangre" id="tipo_sangre"
                                style="width: 85px">
                                <option value="">Todos</option>
                                <option value="A+" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="AB+" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                <option value="O+" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                <option value="NoConocida" <?php echo (isset($_GET['tipo_sangre']) && $_GET['tipo_sangre'] == 'NoConocida') ? 'selected' : ''; ?>>N/C</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label for="tiene_hermanos" class="form-label small mb-0">Hermanos:</label>
                            <select class="form-select form-select-sm" name="tiene_hermanos" id="tiene_hermanos"
                                style="width: 100px">
                                <option value="">Todos</option>
                                <option value="Si" <?php echo (isset($_GET['tiene_hermanos']) && $_GET['tiene_hermanos'] == 'Si') ? 'selected' : ''; ?>>Sí</option>
                                <option value="No" <?php echo (isset($_GET['tiene_hermanos']) && $_GET['tiene_hermanos'] == 'No') ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm" style="width: 100px">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            /* Estilos para mantener la alineación */
            .filter-form .form-label {
                font-size: 0.8rem;
                margin-bottom: 0.2rem;
            }

            .filter-form .col-auto {
                padding-right: 8px;
                padding-left: 8px;
            }

            @media (max-width: 992px) {
                .filter-form .col-auto {
                    width: 100%;
                    margin-bottom: 8px;
                }

                .filter-form .col-auto input,
                .filter-form .col-auto select {
                    width: 100% !important;
                }
            }

            .action-link {
    color: #6c757d; /* Color gris profesional */
    transition: color 0.3s;
    margin: 0 5px;
    font-size: 1.1rem;
}

.action-link:hover {
    color: #0056b3; /* Color azul al pasar el mouse */
    text-decoration: none;
}
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <!-- Resultados de búsqueda -->
        <div class="search-info">
            <span><i class="bi bi-info-circle"></i> Resultados de búsqueda: <?php echo $num_rows; ?></span>
            <a href="#" id="showAllColumns" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-eye"></i> Mostrar Todas las Columnas
            </a>
        </div>

        <!-- Tabla de resultados con scroll horizontal superior -->
        <div class="table-container">
            <div class="horizontal-scroll">
                <div class="scroll-handle" id="scrollHandle"></div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Acciones</th>
                            <th>RICE</th>
                            <th>CURP</th>
                            <th>Nombre</th>
                            <th>Sexo</th>
                            <th>Grupo sanguíneo</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th>Fecha de nacimiento</th>
                            <th>Teléfono de casa</th>
                            <th>Teléfono de tutor</th>
                            <th>Nombre del tutor</th>
                            <th>Ejido</th>
                            <th>Con Hermanos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                <td>
                                    <a href="show.php?id=<?php echo $row['id']; ?>" class="action-link" title="Ver">
                                    <i class="fas fa-search-plus"></i> <!-- Alternativas: fa-eye, fa-search -->
                                    </a>
                                    <?php if ($userType == 'Direccion'): ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-link" title="Editar">
                                    <i class="fas fa-edit"></i> <!-- Alternativas: fa-pencil-alt, fa-file-edit -->
                                    </a>
                                            <!-- <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo $row['nombre']; ?>')" class="action-link text-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </a> -->
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['RICE']; ?></td>
                                    <td><?php echo $row['CURP']; ?></td>
                                    <td><?php echo $row['nombre']; ?></td>
                                    <td><?php echo $row['sexo']; ?></td>
                                    <td><?php echo $row['tipo_sangre']; ?></td>
                                    <td><?php echo $row['grado']; ?></td>
                                    <td><?php echo $row['seccion']; ?></td>
                                    <td><?php echo $row['fecha_nacimiento']; ?></td>
                                    <td><?php echo $row['telefono_casa']; ?></td>
                                    <td><?php echo $row['telefono_tutor']; ?></td>
                                    <td><?php echo $row['nombre_tutor']; ?></td>
                                    <td><?php echo $row['ejido']; ?></td>
                                    <td><?php echo $row['tiene_hermanos']; ?></td>
                                    <td>
                                    <a href="show.php?id=<?php echo $row['id']; ?>" class="action-link" title="Ver">
                                    <i class="fas fa-search-plus"></i> <!-- Alternativas: fa-eye, fa-search -->
                                    </a>
                                    <?php if ($userType == 'Direccion'): ?>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-link" title="Editar">
                                    <i class="fas fa-edit"></i> <!-- Alternativas: fa-pencil-alt, fa-file-edit -->
                                    </a>
                                            <!-- <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo $row['nombre']; ?>')" class="action-link text-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </a> -->
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="14" class="text-center">No se encontraron resultados</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function confirmDelete(id, nombre) {
            if (confirm("¿Estás seguro de eliminar a " + nombre + "?\nTenga en cuenta que al eliminar a este alumno todo su historial dentro del sistema será eliminado también. Esta acción no se podrá deshacer.")) {
                window.location.href = "delete.php?id=" + id;
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Obtener todas las cabeceras de la tabla
            var headers = document.querySelectorAll('th');

            // Verificar si hay índices de columnas ocultas almacenados en el almacenamiento local
            var hiddenColumns = localStorage.getItem('hiddenColumns');
            if (hiddenColumns) {
                hiddenColumns = JSON.parse(hiddenColumns);
                hiddenColumns.forEach(function (index) {
                    // Ocultar todas las celdas de la columna correspondiente al índice
                    var cellsInColumn = document.querySelectorAll('td:nth-child(' + (index + 1) + '), th:nth-child(' + (index + 1) + ')');
                    cellsInColumn.forEach(function (cell) {
                        cell.classList.add('hidden');
                    });
                });
            }

            // Iterar sobre cada cabecera y agregar un evento de clic
            headers.forEach(function (header, index) {
                header.addEventListener('click', function () {
                    // Obtener el estado actual de ocultamiento de la columna
                    var isHidden = header.classList.contains('hidden');

                    // Cambiar el estado de ocultamiento de la columna
                    if (isHidden) {
                        // Mostrar todas las celdas de la columna correspondiente al índice
                        var cellsInColumn = document.querySelectorAll('td:nth-child(' + (index + 1) + '), th:nth-child(' + (index + 1) + ')');
                        cellsInColumn.forEach(function (cell) {
                            cell.classList.remove('hidden');
                        });
                    } else {
                        // Ocultar todas las celdas de la columna correspondiente al índice
                        var cellsInColumn = document.querySelectorAll('td:nth-child(' + (index + 1) + '), th:nth-child(' + (index + 1) + ')');
                        cellsInColumn.forEach(function (cell) {
                            cell.classList.add('hidden');
                        });
                    }

                    // Actualizar los índices de columnas ocultas en el almacenamiento local
                    var updatedHiddenColumns = [];
                    headers.forEach(function (header, index) {
                        if (header.classList.contains('hidden')) {
                            updatedHiddenColumns.push(index);
                        }
                    });
                    localStorage.setItem('hiddenColumns', JSON.stringify(updatedHiddenColumns));
                });
            });

            // Agregar evento de clic al botón "Mostrar Todas las Columnas"
            document.getElementById('showAllColumns').addEventListener('click', function (e) {
                e.preventDefault();
                // Mostrar todas las celdas ocultas
                var hiddenCells = document.querySelectorAll('.hidden');
                hiddenCells.forEach(function (cell) {
                    cell.classList.remove('hidden');
                });

                // Limpiar el almacenamiento local de las columnas ocultas
                localStorage.removeItem('hiddenColumns');
            });

            // Script para el scroll horizontal superior
            const scrollHandle = document.getElementById('scrollHandle');
            const tableContainer = document.querySelector('.table-responsive');

            if (scrollHandle && tableContainer) {
                scrollHandle.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    const startX = e.pageX;
                    const startScroll = tableContainer.scrollLeft;
                    const width = tableContainer.scrollWidth - tableContainer.clientWidth;

                    function moveHandler(e) {
                        const deltaX = e.pageX - startX;
                        const scroll = startScroll - deltaX;
                        tableContainer.scrollLeft = Math.max(0, Math.min(width, scroll));
                    }

                    function upHandler() {
                        document.removeEventListener('mousemove', moveHandler);
                        document.removeEventListener('mouseup', upHandler);
                    }

                    document.addEventListener('mousemove', moveHandler);
                    document.addEventListener('mouseup', upHandler);
                });

                // Sincronizar el ancho del handle con el scroll
                function updateScrollHandle() {
                    const scrollWidth = tableContainer.scrollWidth;
                    const clientWidth = tableContainer.clientWidth;
                    const scrollRatio = clientWidth / scrollWidth;
                    const handleWidth = scrollRatio * 100;

                    scrollHandle.style.width = `${Math.max(10, handleWidth)}%`;
                    scrollHandle.style.left = `${(tableContainer.scrollLeft / scrollWidth) * 100}%`;
                }

                tableContainer.addEventListener('scroll', updateScrollHandle);
                window.addEventListener('resize', updateScrollHandle);
                updateScrollHandle();
            }
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>