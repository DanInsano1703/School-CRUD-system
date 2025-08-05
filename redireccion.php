<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar año</title>
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
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
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
        .form-control {
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
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-calendar-check"></i> Seleccione el Año</h2>
            </div>
            <div class="card-body">
                <!-- Instrucciones -->
                <div class="instruction-box">
                    <p class="mb-0"><i class="bi bi-info-circle"></i> Utilice este panel para seleccionar la información de niveles de lectura del año que desea visualizar.</p>
                </div>

                <!-- Formulario -->
                <form method="get" action="Lectura.php" class="row g-3">
                    <div class="col-md-8">
                        <label for="año" class="form-label">Ingrese el año:</label>
                        <input type="text" class="form-control" id="año" name="año" pattern="\d{4}" placeholder="Ejemplo: 2024" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-arrow-right-circle"></i> Ir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para establecer el año actual en el campo de texto
        function setCurrentYear() {
            const now = new Date();
            const currentYear = now.getFullYear();
            document.getElementById('año').value = currentYear;
        }

        // Llamar a la función cuando la página cargue
        window.onload = setCurrentYear;
    </script>
</body>
</html>