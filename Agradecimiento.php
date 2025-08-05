<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constancia</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Cormorant+Garamond:wght@500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color:rgb(242, 65, 62);
            color: #333;
            font-family: 'Cormorant Garamond', serif;
            font-weight: 800;
            line-height: 1.8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
            background-image: url('https://transparenttextures.com/patterns/cream-paper.png');
        }
        .container {
            max-width: 800px;
            background-color: rgb(255, 255, 255);
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.24);
            padding: 3rem;
            margin: 2rem auto;
            border: 1px solid #eaeaea;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            color:rgb(26, 26, 26);
            text-align: center;
            margin-bottom: 2.5rem;
            font-weight: 700;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 1rem;
            letter-spacing: 0.5px;
        }
        p {
            margin-bottom: 1.8rem;
            text-align: justify;
            font-size: 1.15rem;
        }
        .signature {
            margin-top: 4rem;
            text-align: right;
        }
        .signature p {
            margin-bottom: 0.3rem;
            text-align: right;
        }
        .signature strong {
            font-weight: 600;
            color: #2c3e50;
        }
        .logos {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 3.5rem;
            gap: 3rem;
            flex-wrap: wrap;
        }
        .logos img {
            max-height: 90px;
            width: auto;
            opacity: 0.9;
            transition: all 0.3s ease;
        }
        .logos img:hover {
            opacity: 1;
            transform: scale(1.05);
        }
        .date {
            color: #666;
            font-style: italic;
        }
        
       
        
        @media (max-width: 768px) {
            .container {
                padding: 2rem;
            }
            h1 {
                font-size: 1.8rem;
                margin-bottom: 1.8rem;
            }
            p {
                font-size: 1.05rem;
            }
            .logos {
                gap: 1.5rem;
            }
            .logos img {
                max-height: 70px;
            }
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<body>
<div class="container position-relative">
    <h1>Constancia y Agradecimiento</h1>
    <p>
        Por medio del presente, yo, Daniel Favela Montoya, hago constar que el programa desarrollado por mí en el marco de mis estadías en el Instituto Eduardo Tricio Gómez no es de mi propiedad.
    </p>
    <p>
        Dicho programa fue creado como parte de mi colaboración con el mencionado instituto, en agradecimiento por la oportunidad brindada para realizar mis prácticas profesionales.
    </p>
    <p>
        Cedo expresamente todos los derechos de uso, modificación, y distribución del programa al Instituto Eduardo Tricio Gómez, quedando este como el legítimo propietario del mismo.
    </p>
    
    <div class="signature">
        <p>Atentamente,</p>
        <p><strong>Daniel Favela Montoya</strong></p>
        <p>Estudiante de la Universidad Tecnológica de Torreón</p>
        <p class="date">Fecha: 14 de agosto de 2024</p>
    </div>

    <div class="logos">
        <img src="media/uxt.jpg" alt="Logo Universidad Tecnológica de Torreón" class="img-fluid">
    </div>
</div>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>