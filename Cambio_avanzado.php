
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mover alumnos (Avanzado)</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
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

        .btn-back {
            background-color: #6c757d;
            border: none;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 10px 15px;
        }

        .instruction-box {
            background-color: #e9ecef;
            border-left: 4px solid #2c3e50;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }

        textarea.form-control {
            min-height: 200px;
            font-family: monospace;
        }

        .info-text {
            color: gray;
            font-style: italic;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>

<body>
<?php include 'navbar.php'; ?>
    <div class="container">
     

        <!-- Tarjeta principal -->
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-arrow-left-right"></i> Mover alumnos (Avanzado)</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones -->
                <div class="instruction-box">
                    <p class="mb-0"><i class="bi bi-info-circle"></i> Por favor, ingresa los RICEs de los alumnos uno
                        por línea en el campo de texto. Luego selecciona el grado y la sección a los que deseas asignar
                        los alumnos. Haz clic en "Asignar Grado y Sección" para realizar los cambios. Si un RICE no
                        existe en la base de datos, el sistema lo registrará y te mostrará una lista con los RICEs que
                        no pudieron ser actualizados.</p>
                </div>

                <!-- Formulario -->
                <form id="riceForm" method="POST" action="procesar_grado_seccion.php" class="row g-3">
                    <div class="col-12">
                        <label for="rice" class="form-label">RICEs (uno por línea):</label>
                        <textarea class="form-control" id="rice" name="rice" rows="10"
                            placeholder="Ejemplo:&#10;153232&#10;155164&#10;454546"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="grado" class="form-label">Nuevo Grado:</label>
                        <select class="form-select" id="grado" name="grado" required>
                            <option value="">Selecciona un grado</option>
                            <option value="1">1° Grado</option>
                            <option value="2">2° Grado</option>
                            <option value="3">3° Grado</option>
                            <option value="4">4° Grado</option>
                            <option value="5">5° Grado</option>
                            <option value="6">6° Grado</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="seccion" class="form-label">Nueva Sección:</label>
                        <select class="form-select" id="seccion" name="seccion" required>
                            <option value="">Selecciona una sección</option>
                            <option value="A">Sección A</option>
                            <option value="B">Sección B</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Asignar Grado y Sección
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Validación básica del formulario
        document.getElementById('riceForm').addEventListener('submit', function (e) {
            const riceInput = document.getElementById('rice');
            const gradoInput = document.getElementById('grado');
            const seccionInput = document.getElementById('seccion');

            if (riceInput.value.trim() === '') {
                alert('Por favor ingresa al menos un RICE');
                e.preventDefault();
                return;
            }

            if (gradoInput.value === '') {
                alert('Por favor selecciona un grado');
                e.preventDefault();
                return;
            }

            if (seccionInput.value === '') {
                alert('Por favor selecciona una sección');
                e.preventDefault();
                return;
            }

            // Opcional: Validar formato de RICEs
            const rices = riceInput.value.split('\n').filter(r => r.trim() !== '');
            const invalidRices = rices.filter(r => !/^\d+$/.test(r.trim()));

            if (invalidRices.length > 0) {
                if (!confirm(`Algunos RICEs no tienen formato válido (solo números):\n${invalidRices.join(', ')}\n¿Deseas continuar de todos modos?`)) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>

</html>