<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Alumno</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        body {
            background-color: #f8f9fa;
            padding-top: 20px;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
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
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .form-label {
            font-weight: 500;
            margin-top: 10px;
        }
        .info-text {
            color: gray;
            font-style: italic;
            text-align: center;
            margin-bottom: 20px;
        }
        #mensaje {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #28a745;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
        }
        .error-message {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container">
        <?php
        include 'db.php';

        $id = $_GET['id'];
        $sql = "SELECT * FROM alumnos WHERE id = $id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $alumno = $result->fetch_assoc();
        } else {
            echo '<div class="alert alert-danger">No se encontró el alumno.</div>';
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $RICE = $_POST['RICE'];
            $CURP = $_POST['CURP'];
            $nombre = $_POST['nombre'];
            $apellidos = $_POST['apellidos'];
            $sexo = $_POST['sexo'];
            $tipo_sangre = $_POST['tipo_sangre'];
            $grado = $_POST['grado'];
            $seccion = $_POST['seccion'];
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $telefono_casa = $_POST['telefono_casa'];
            $telefono_tutor = $_POST['telefono_tutor'];
            $nombre_tutor = $_POST['nombre_tutor'];
            $ejido = $_POST['ejido'];
            $tiene_hermanos = $_POST['tiene_hermanos'];

            $sql_update = "UPDATE alumnos SET 
                RICE='$RICE', CURP='$CURP', nombre='$nombre', apellidos='$apellidos', sexo='$sexo', tipo_sangre='$tipo_sangre', 
                grado='$grado', seccion='$seccion', fecha_nacimiento='$fecha_nacimiento', telefono_casa='$telefono_casa', 
                telefono_tutor='$telefono_tutor', nombre_tutor='$nombre_tutor', ejido='$ejido', tiene_hermanos='$tiene_hermanos'
                WHERE id=$id";

            if ($conn->query($sql_update) === TRUE) {
                echo "<script>document.addEventListener('DOMContentLoaded', function() {
                    mostrarMensaje('Datos actualizados correctamente', 'success');
                });</script>";
            } else {
                echo "<script>document.addEventListener('DOMContentLoaded', function() {
                    mostrarMensaje('Error al actualizar los datos: " . addslashes($conn->error) . "', 'error');
                });</script>";
            }
            $alumno = array_merge($alumno, $_POST);
        }
        ?>

        <div id="mensaje"></div>
        
        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-person-lines-fill"></i> Editar Alumno</h2>
            </div>
            <div class="card-body">
                <p class="info-text"><i class="bi bi-exclamation-circle"></i> Asegúrese de no ingresar un RICE o CURP ya existentes.</p>

                <form method="POST" action="" class="row g-3" onsubmit="return validarFormulario()">
                    <input type="hidden" name="id" value="<?php echo $alumno['id']; ?>">

                    <div class="col-md-6">
                        <label for="RICE" class="form-label">RICE:</label>
                        <input type="text" class="form-control" id="RICE" name="RICE" value="<?php echo $alumno['RICE']; ?>" pattern="[0-9]+" title="Solo se permiten números" required>
                        <div class="invalid-feedback">Solo se permiten números</div>
                    </div>

                    <div class="col-md-6">
                        <label for="CURP" class="form-label">CURP:</label>
                        <input type="text" class="form-control" id="CURP" name="CURP" value="<?php echo $alumno['CURP']; ?>" pattern="[A-Z0-9]{18}" title="Debe contener exactamente 18 caracteres alfanuméricos" maxlength="18" required>
                        <div class="invalid-feedback">Debe contener exactamente 18 caracteres alfanuméricos</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre y apellidos:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $alumno['nombre']; ?>" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ ]+" title="Solo se permiten letras y espacios" required>
                        <div class="invalid-feedback">Solo se permiten letras y espacios</div>
                    </div>

                    <div class="col-md-6">
                        <label for="apellidos" class="form-label">Solo apellidos:</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo $alumno['apellidos']; ?>" pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ ]+" title="Solo se permiten letras y espacios" required>
                        <div class="invalid-feedback">Solo se permiten letras y espacios</div>
                    </div>

                    <div class="col-md-6">
                        <label for="sexo" class="form-label">Sexo:</label>
                        <select class="form-select" id="sexo" name="sexo" required>
                            <option value="Hombre" <?php if($alumno['sexo'] == 'Hombre') echo 'selected'; ?>>Hombre</option>
                            <option value="Mujer" <?php if($alumno['sexo'] == 'Mujer') echo 'selected'; ?>>Mujer</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tipo_sangre" class="form-label">Tipo de Sangre:</label>
                        <select class="form-select" id="tipo_sangre" name="tipo_sangre" required>
                            <option value="A+" <?php if($alumno['tipo_sangre'] == 'A+') echo 'selected'; ?>>A+</option>
                            <option value="A-" <?php if($alumno['tipo_sangre'] == 'A-') echo 'selected'; ?>>A-</option>
                            <option value="B+" <?php if($alumno['tipo_sangre'] == 'B+') echo 'selected'; ?>>B+</option>
                            <option value="B-" <?php if($alumno['tipo_sangre'] == 'B-') echo 'selected'; ?>>B-</option>
                            <option value="AB+" <?php if($alumno['tipo_sangre'] == 'AB+') echo 'selected'; ?>>AB+</option>
                            <option value="AB-" <?php if($alumno['tipo_sangre'] == 'AB-') echo 'selected'; ?>>AB-</option>
                            <option value="O+" <?php if($alumno['tipo_sangre'] == 'O+') echo 'selected'; ?>>O+</option>
                            <option value="O-" <?php if($alumno['tipo_sangre'] == 'O-') echo 'selected'; ?>>O-</option>
                            <option value="NoConocida" <?php if($alumno['tipo_sangre'] == 'NoConocida') echo 'selected'; ?>>No Conocida</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="grado" class="form-label">Grado:</label>
                        <input type="number" class="form-control" id="grado" name="grado" value="<?php echo $alumno['grado']; ?>" min="1" max="6" required>
                    </div>

                    <div class="col-md-6">
                        <label for="seccion" class="form-label">Sección:</label>
                        <select class="form-select" id="seccion" name="seccion" required>
                            <option value="A" <?php if($alumno['seccion'] == 'A') echo 'selected'; ?>>A</option>
                            <option value="B" <?php if($alumno['seccion'] == 'B') echo 'selected'; ?>>B</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $alumno['fecha_nacimiento']; ?>" max="<?php echo date('Y-m-d', strtotime('-1 day')); ?>" required>
                        <div class="invalid-feedback">La fecha no puede ser hoy o posterior</div>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono_casa" class="form-label">Teléfono de Casa:</label>
                        <input type="tel" class="form-control" id="telefono_casa" name="telefono_casa" value="<?php echo $alumno['telefono_casa']; ?>" pattern="[0-9]+" title="Solo se permiten números" required>
                        <div class="invalid-feedback">Solo se permiten números</div>
                    </div>

                    <div class="col-md-6">
                        <label for="telefono_tutor" class="form-label">Teléfono de Tutor:</label>
                        <input type="tel" class="form-control" id="telefono_tutor" name="telefono_tutor" value="<?php echo $alumno['telefono_tutor']; ?>" pattern="[0-9]+" title="Solo se permiten números" required>
                        <div class="invalid-feedback">Solo se permiten números</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nombre_tutor" class="form-label">Nombre del Tutor:</label>
                        <input type="text" class="form-control" id="nombre_tutor" name="nombre_tutor" value="<?php echo $alumno['nombre_tutor']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="ejido" class="form-label">Ejido:</label>
                        <input type="text" class="form-control" id="ejido" name="ejido" value="<?php echo $alumno['ejido']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="tiene_hermanos" class="form-label">¿Tiene hermanos en el colegio?</label>
                        <select class="form-select" id="tiene_hermanos" name="tiene_hermanos" required>
                            <option value="SI" <?php if($alumno['tiene_hermanos'] == 'SI') echo 'selected'; ?>>SI</option>
                            <option value="NO" <?php if($alumno['tiene_hermanos'] == 'NO') echo 'selected'; ?>>NO</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-save"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function mostrarMensaje(mensaje, tipo) {
        const mensajeDiv = document.getElementById('mensaje');
        mensajeDiv.innerHTML = mensaje;
        mensajeDiv.style.display = 'block';
        
        if(tipo === 'error') {
            mensajeDiv.classList.add('error-message');
        } else {
            mensajeDiv.classList.remove('error-message');
        }
        
        setTimeout(function() {
            mensajeDiv.style.display = 'none';
        }, 5000);
    }

    // Validación del formulario
    function validarFormulario() {
        // Convertir CURP a mayúsculas antes de enviar
        const curpInput = document.getElementById('CURP');
        curpInput.value = curpInput.value.toUpperCase();
        
        // Validación adicional del CURP (18 caracteres)
        if (curpInput.value.length !== 18) {
            mostrarMensaje('El CURP debe tener exactamente 18 caracteres', 'error');
            curpInput.focus();
            return false;
        }
        
        // Validación de la fecha de nacimiento
        const fechaNacimiento = new Date(document.getElementById('fecha_nacimiento').value);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        if (fechaNacimiento >= hoy) {
            mostrarMensaje('La fecha de nacimiento no puede ser hoy o posterior', 'error');
            return false;
        }
        
        return true;
    }

    // Validación en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        // Validar solo números en RICE y teléfonos
        document.getElementById('RICE').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        document.getElementById('telefono_casa').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        document.getElementById('telefono_tutor').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Validar solo letras y espacios en nombre y apellidos
        document.getElementById('nombre').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúñÑ ]/g, '');
        });
        
        document.getElementById('apellidos').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúñÑ ]/g, '');
        });
        
        // Convertir CURP a mayúsculas automáticamente
        document.getElementById('CURP').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    });
    </script>
</body>
</html>