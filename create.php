<?php
include 'db.php';

// Inicialización de variables
$alumnoData = [
    'RICE' => '',
    'CURP' => '',
    'nombre' => '',
    'apellidos' => '',
    'sexo' => 'Hombre',
    'tipo_sangre' => 'A+',
    'grado' => '',
    'seccion' => '',
    'fecha_nacimiento' => '',
    'telefono_casa' => '',
    'telefono_tutor' => '',
    'nombre_tutor' => '',
    'ejido' => '',
    'tiene_hermanos' => 'Sí'
];

$mensaje = '';
$esError = false;
$erroresValidacion = [];

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger datos del formulario
    foreach ($alumnoData as $key => $value) {
        $alumnoData[$key] = $_POST[$key] ?? $value;
    }

    // Validaciones personalizadas
    // Validar RICE (solo números)
    if (!preg_match('/^[0-9]+$/', $alumnoData['RICE'])) {
        $erroresValidacion['RICE'] = 'El RICE solo puede contener números';
    }

    // Validar CURP (18 caracteres alfanuméricos)
    $alumnoData['CURP'] = strtoupper($alumnoData['CURP']);
    if (!preg_match('/^[A-Z0-9]{18}$/', $alumnoData['CURP'])) {
        $erroresValidacion['CURP'] = 'La CURP debe tener exactamente 18 caracteres alfanuméricos';
    }

    // Validar fecha de nacimiento (no puede ser hoy o futuro)
    $fechaNacimiento = new DateTime($alumnoData['fecha_nacimiento']);
    $hoy = new DateTime();
    if ($fechaNacimiento >= $hoy) {
        $erroresValidacion['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser hoy o en el futuro';
    }

    // Validar teléfonos (solo números)
    if (!preg_match('/^[0-9]+$/', $alumnoData['telefono_casa'])) {
        $erroresValidacion['telefono_casa'] = 'El teléfono solo puede contener números';
    }
    if (!preg_match('/^[0-9]+$/', $alumnoData['telefono_tutor'])) {
        $erroresValidacion['telefono_tutor'] = 'El teléfono solo puede contener números';
    }

    // Validar nombre del tutor (solo letras y espacios)
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $alumnoData['nombre_tutor'])) {
        $erroresValidacion['nombre_tutor'] = 'El nombre solo puede contener letras y espacios';
    }

    // Si no hay errores de validación, proceder con la verificación en BD
    if (empty($erroresValidacion)) {
        // Validar si el RICE ya existe
        $queryRice = "SELECT * FROM alumnos WHERE RICE = ?";
        $stmtRice = $conn->prepare($queryRice);
        $stmtRice->bind_param('s', $alumnoData['RICE']);
        $stmtRice->execute();
        $resultRice = $stmtRice->get_result();

        // Validar si el CURP ya existe
        $queryCurp = "SELECT * FROM alumnos WHERE CURP = ?";
        $stmtCurp = $conn->prepare($queryCurp);
        $stmtCurp->bind_param('s', $alumnoData['CURP']);
        $stmtCurp->execute();
        $resultCurp = $stmtCurp->get_result();

        if ($resultRice->num_rows > 0 || $resultCurp->num_rows > 0) {
            $mensaje = 'Este RICE y/o CURP ya existen. Por favor verifica la información (No se ha hecho un registro).';
            $esError = true;
        } else {
            // Insertar nuevo alumno
            $query = "INSERT INTO alumnos (RICE, CURP, nombre, apellidos, sexo, tipo_sangre, grado, seccion, fecha_nacimiento, telefono_casa, telefono_tutor, nombre_tutor, ejido, tiene_hermanos) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ssssssssssssss', 
                $alumnoData['RICE'], 
                $alumnoData['CURP'], 
                $alumnoData['nombre'], 
                $alumnoData['apellidos'], 
                $alumnoData['sexo'], 
                $alumnoData['tipo_sangre'], 
                $alumnoData['grado'], 
                $alumnoData['seccion'], 
                $alumnoData['fecha_nacimiento'], 
                $alumnoData['telefono_casa'], 
                $alumnoData['telefono_tutor'], 
                $alumnoData['nombre_tutor'], 
                $alumnoData['ejido'], 
                $alumnoData['tiene_hermanos']
            );

            if ($stmt->execute()) {
                $mensaje = 'Alumno agregado exitosamente.';
                $esError = false;
                // Limpiar datos después de inserción exitosa
                $alumnoData = array_map(function() { return ''; }, $alumnoData);
            } else {
                $mensaje = 'Error al agregar el alumno. Por favor, intente nuevamente.';
                $esError = true;
            }
        }
    } else {
        $mensaje = 'Por favor corrija los siguientes errores:';
        $esError = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Alumno</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
    :root {
    --primary-color: #2c3e50;
    --secondary-color: #1a252f;
    --light-gray: rgb(243, 243, 243);   
    --dark-gray: rgb(100, 100, 100);
    --white: #ffffff;
    --danger-color: #dc3545;
    --border-radius: 10px;
    --box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

body {
    background-color: var(--light-gray);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding-top: 0;
    color: #333;
}

/* Form Container */
.form-container {
    max-width: 900px;
    margin: 30px auto;
    background-color: var(--white);
    border-radius: var(--border-radius);
    padding: 2.5rem;
    box-shadow: var(--box-shadow);
}

/* Form Header */
.form-header {
    text-align: center;
    margin-bottom: 2rem;
}

.form-header .icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.form-header h1 {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-note {
    color: var(--dark-gray);
    font-style: italic;
    text-align: center;
    margin-bottom: 0;
    font-size: 0.95rem;
}

/* Form Sections */
.form-section {
    background-color: var(--white);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid var(--primary-color);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.form-section-title {
    color: var(--primary-color);
    font-weight: 500;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Form Elements */
.form-label {
    font-weight: 500;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.form-control, 
.form-select {
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    border: 1px solid #ced4da;
    transition: var(--transition);
    background-color: var(--white);
}

.form-control:focus, 
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.15);
    outline: none;
}

/* Buttons */
.form-footer {
    margin-top: 2rem;
    text-align: center;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
    padding: 0.75rem 2rem;
    font-weight: 500;
    transition: var(--transition);
    border-radius: var(--border-radius);
    color: white;
    font-size: 1rem;
}

.btn-primary:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    color: white;
}

/* Validation Styles */
.is-invalid {
    border-color: var(--danger-color);
}

.invalid-feedback {
    color: var(--danger-color);
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
}

/* Form Text */
.form-text {
    font-size: 0.875rem;
    color: var(--dark-gray);
    margin-top: 0.25rem;
}

/* Alert Styles */
.alert {
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .form-container {
        padding: 1.5rem;
        margin-top: 1rem;
    }
    
    .form-header .icon {
        font-size: 2rem;
    }
    
    .form-header h1 {
        font-size: 1.5rem;
    }
    
    .form-section {
        padding: 1rem;
    }

    
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="form-container">
            <!-- Mensajes de estado -->
            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $esError ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?= $esError ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i>
                    <?= $mensaje ?>
                    <?php if ($esError && !empty($erroresValidacion)): ?>
                        <ul class="mb-0">
                            <?php foreach ($erroresValidacion as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Encabezado del formulario -->
            <div class="form-header">
                <div class="icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h1>Crear nuevo alumno</h1>
                <p class="form-note">Complete todos los campos requeridos para registrar un nuevo alumno en el sistema.</p>
            </div>

            <!-- Formulario -->
            <form method="POST" action="" id="formularioAlumno">
                <!-- Sección 1: Información Básica -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-person-vcard"></i> Información Básica</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="RICE" class="form-label">RICE</label>
                            <input type="text" class="form-control <?= isset($erroresValidacion['RICE']) ? 'is-invalid' : '' ?>" id="RICE" name="RICE" value="<?= htmlspecialchars($alumnoData['RICE']) ?>" required>
                            <?php if (isset($erroresValidacion['RICE'])): ?>
                                <div class="invalid-feedback"><?= $erroresValidacion['RICE'] ?></div>
                            <?php endif; ?>
                            <small class="form-text">Identificador único del alumno (solo números)</small>
                        </div>
                        <div class="col-md-6">
                            <label for="CURP" class="form-label">CURP</label>
                            <input type="text" class="form-control <?= isset($erroresValidacion['CURP']) ? 'is-invalid' : '' ?>" id="CURP" name="CURP" value="<?= htmlspecialchars($alumnoData['CURP']) ?>" required>
                            <?php if (isset($erroresValidacion['CURP'])): ?>
                                <div class="invalid-feedback"><?= $erroresValidacion['CURP'] ?></div>
                            <?php endif; ?>
                            <small class="form-text">18 caracteres alfanuméricos</small>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Datos Personales -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-person-lines-fill"></i> Datos Personales</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre y Apellidos</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($alumnoData['nombre']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Solo Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?= htmlspecialchars($alumnoData['apellidos']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="sexo" class="form-label">Sexo</label>
                            <select class="form-select" id="sexo" name="sexo" required>
                                <option value="Hombre" <?= $alumnoData['sexo'] == 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                                <option value="Mujer" <?= $alumnoData['sexo'] == 'Mujer' ? 'selected' : '' ?>>Mujer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_sangre" class="form-label">Tipo de Sangre</label>
                            <select class="form-select" id="tipo_sangre" name="tipo_sangre" required>
                                <option value="A+" <?= $alumnoData['tipo_sangre'] == 'A+' ? 'selected' : '' ?>>A+</option>
                                <option value="A-" <?= $alumnoData['tipo_sangre'] == 'A-' ? 'selected' : '' ?>>A-</option>
                                <option value="B+" <?= $alumnoData['tipo_sangre'] == 'B+' ? 'selected' : '' ?>>B+</option>
                                <option value="B-" <?= $alumnoData['tipo_sangre'] == 'B-' ? 'selected' : '' ?>>B-</option>
                                <option value="AB+" <?= $alumnoData['tipo_sangre'] == 'AB+' ? 'selected' : '' ?>>AB+</option>
                                <option value="AB-" <?= $alumnoData['tipo_sangre'] == 'AB-' ? 'selected' : '' ?>>AB-</option>
                                <option value="O+" <?= $alumnoData['tipo_sangre'] == 'O+' ? 'selected' : '' ?>>O+</option>
                                <option value="O-" <?= $alumnoData['tipo_sangre'] == 'O-' ? 'selected' : '' ?>>O-</option>
                                <option value="NoConocida" <?= $alumnoData['tipo_sangre'] == 'NoConocida' ? 'selected' : '' ?>>No Conocida</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control <?= isset($erroresValidacion['fecha_nacimiento']) ? 'is-invalid' : '' ?>" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($alumnoData['fecha_nacimiento']) ?>" required>
                            <?php if (isset($erroresValidacion['fecha_nacimiento'])): ?>
                                <div class="invalid-feedback"><?= $erroresValidacion['fecha_nacimiento'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Información Escolar -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-book"></i> Información Escolar</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="grado" class="form-label">Grado</label>
                            <input type="number" class="form-control" id="grado" name="grado" min="1" max="6" value="<?= htmlspecialchars($alumnoData['grado']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="seccion" class="form-label">Sección</label>
                            <select class="form-select" id="seccion" name="seccion" required>
                                <option value="A" <?= $alumnoData['seccion'] == 'A' ? 'selected' : '' ?>>A</option>
                                <option value="B" <?= $alumnoData['seccion'] == 'B' ? 'selected' : '' ?>>B</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tiene_hermanos" class="form-label">¿Tiene Hermanos en el Instituto?</label>
                            <select class="form-select" id="tiene_hermanos" name="tiene_hermanos" required>
                                <option value="Sí" <?= $alumnoData['tiene_hermanos'] == 'Sí' ? 'selected' : '' ?>>Sí</option>
                                <option value="No" <?= $alumnoData['tiene_hermanos'] == 'No' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Información de Contacto -->
                <div class="form-section">
                    <h3 class="form-section-title"><i class="bi bi-telephone"></i> Información de Contacto</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="telefono_casa" class="form-label">Teléfono de Casa</label>
                            <input type="tel" class="form-control <?= isset($erroresValidacion['telefono_casa']) ? 'is-invalid' : '' ?>" id="telefono_casa" name="telefono_casa" value="<?= htmlspecialchars($alumnoData['telefono_casa']) ?>" required>
                            <?php if (isset($erroresValidacion['telefono_casa'])): ?>
                                <div class="invalid-feedback"><?= $erroresValidacion['telefono_casa'] ?></div>
                            <?php endif; ?>
                            <small class="form-text">Solo números</small>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono_tutor" class="form-label">Teléfono del Tutor</label>
                            <input type="tel" class="form-control <?= isset($erroresValidacion['telefono_tutor']) ? 'is-invalid' : '' ?>" id="telefono_tutor" name="telefono_tutor" value="<?= htmlspecialchars($alumnoData['telefono_tutor']) ?>" required>
                            <?php if (isset($erroresValidacion['telefono_tutor'])): ?>
                                <div class="invalid-feedback"><?= $erroresValidacion['telefono_tutor'] ?></div>
                            <?php endif; ?>
                            <small class="form-text">Solo números</small>
                        </div>
                        <div class="col-md-6">
                            <label for="nombre_tutor" class="form-label">Nombre del Tutor</label>
                            <input type="text" class="form-control <?= isset($erroresValidacion['nombre_tutor']) ? 'is-invalid' : '' ?>" id="nombre_tutor" name="nombre_tutor" value="<?= htmlspecialchars($alumnoData['nombre_tutor']) ?>" required>
                            <?php if (isset($erroresValidacion['nombre_tutor'])): ?>
                                <div class="invalid-feedback"><?= $erroresValidacion['nombre_tutor'] ?></div>
                            <?php endif; ?>
                            <small class="form-text">Solo letras y espacios</small>
                        </div>
                        <div class="col-md-6">
                            <label for="ejido" class="form-label">Ejido</label>
                            <input type="text" class="form-control" id="ejido" name="ejido" value="<?= htmlspecialchars($alumnoData['ejido']) ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Registrar Alumno
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        document.getElementById('formularioAlumno').addEventListener('submit', function(e) {
            if (!confirm('¿Está seguro de que desea registrar este alumno? Revise que todos los datos sean correctos.')) {
                e.preventDefault();
            }
        });

        // Validación en tiempo real para el grado (1-6)
        document.getElementById('grado').addEventListener('change', function() {
            if (this.value < 1 || this.value > 6) {
                alert('El grado debe estar entre 1 y 6');
                this.value = '';
                this.focus();
            }
        });

        // Validación RICE (solo números)
        document.getElementById('RICE').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Validación CURP (18 caracteres, mayúsculas, alfanumérico)
        document.getElementById('CURP').addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (this.value.length > 18) {
                this.value = this.value.substring(0, 18);
            }
        });

        // Validación fecha de nacimiento (no puede ser hoy o futuro)
        document.getElementById('fecha_nacimiento').addEventListener('change', function() {
            const hoy = new Date().toISOString().split('T')[0];
            if (this.value >= hoy) {
                alert('La fecha de nacimiento no puede ser hoy o en el futuro');
                this.value = '';
            }
        });

        // Validación teléfonos (solo números)
        document.getElementById('telefono_casa').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        document.getElementById('telefono_tutor').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Validación nombre tutor (solo letras y espacios)
        document.getElementById('nombre_tutor').addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    </script>
</body>
</html>