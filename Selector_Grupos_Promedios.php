<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú de Promedios</title>
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
            text-align: center;
            padding: 15px;
        }
        
        .menu-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .menu-option {
            display: block;
            padding: 15px 20px;
            margin: 10px 0;
            text-decoration: none;
            color: #2c3e50;
            border-radius: 6px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            font-size: 18px;
            transition: all 0.3s;
            text-align: center;
        }
        
        .menu-option:hover {
            background-color: #2c3e50;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .menu-option i {
            margin-right: 10px;
        }
        
        .description {
            font-style: italic;
            color: #6c757d;
            margin-bottom: 30px;
            text-align: center;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .title {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .divider {
            border-top: 1px solid #dee2e6;
            margin: 20px 0;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<body>
<?php include 'navbar.php'; ?>

    <div class="container">
        <h1 class="title">Menú de Promedios</h1>
        <div class="divider"></div>
        <p class="description">
            Selecciona una de las opciones en el menú para ver los promedios correspondientes al grado y sección elegidos. 
            Haz clic en el enlace de la sección deseada para acceder a la página de promedios, donde podrás consultar o ingresar las calificaciones.
        </p>
        <div class="divider"></div>
        
        <div class="menu-container">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0"><i class="bi bi-journal-bookmark"></i> Selecciona un Grado y Sección</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="Promedios1A.php" class="menu-option"><i class="bi bi-1-circle"></i>  1°A</a>
                            <a href="Promedios2A.php" class="menu-option"><i class="bi bi-2-circle"></i>  2°A</a>
                            <a href="Promedios3A.php" class="menu-option"><i class="bi bi-3-circle"></i>  3°A</a>
                            <a href="Promedios4A.php" class="menu-option"><i class="bi bi-4-circle"></i>  4°A</a>
                            <a href="Promedios5A.php" class="menu-option"><i class="bi bi-5-circle"></i>  5°A</a>
                            <a href="Promedios6A.php" class="menu-option"><i class="bi bi-6-circle"></i>  6°A</a>
                        </div>
                        <div class="col-md-6">
                            <a href="Promedios1B.php" class="menu-option"><i class="bi bi-1-circle"></i>  1°B</a>
                            <a href="Promedios2B.php" class="menu-option"><i class="bi bi-2-circle"></i>  2°B</a>
                            <a href="Promedios3B.php" class="menu-option"><i class="bi bi-3-circle"></i>  3°B</a>
                            <a href="Promedios4B.php" class="menu-option"><i class="bi bi-4-circle"></i>  4°B</a>
                            <a href="Promedios5B.php" class="menu-option"><i class="bi bi-5-circle"></i>  5°B</a>
                            <a href="Promedios6B.php" class="menu-option"><i class="bi bi-6-circle"></i>  6°B</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>