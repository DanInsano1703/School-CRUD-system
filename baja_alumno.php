<?php
// Configuración de la aplicación
require_once 'db.php';

class Database
{
    private $conn;

    public function __construct($servername, $username, $password, $dbname)
    {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            throw new Exception("Conexión fallida: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function close()
    {
        $this->conn->close();
    }
}

class AcademicWithdrawal
{
    private $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function processWithdrawals($rices, $motivo_baja)
    {
        $rice_array = $this->sanitizeRiceInput($rices);
        $results = [];

        foreach ($rice_array as $rice) {
            try {
                $results[] = $this->processSingleWithdrawal($rice, $motivo_baja);
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'rice' => $rice,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    private function processSingleWithdrawal($rice, $motivo_baja)
    {
        $conn = $this->db->getConnection();

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Obtener datos del alumno
            $student = $this->getStudentData($rice);

            if (!$student) {
                throw new Exception("No se encontró un alumno con el RICE $rice");
            }

            // Eliminar reportes asociados
            $this->deleteStudentReports($rice);

            // Registrar baja académica
            $this->registerAcademicWithdrawal($student, $motivo_baja);

            // Eliminar al alumno
            $this->deleteStudent($rice);

            // Confirmar transacción
            $conn->commit();

            return [
                'success' => true,
                'rice' => $rice,
                'message' => "El alumno con RICE $rice ha sido dado de baja correctamente."
            ];

        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }

    private function getStudentData($rice)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT id, RICE, CURP, nombre, grado, seccion FROM alumnos WHERE RICE = ?");
        $stmt->bind_param("s", $rice);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    private function deleteStudentReports($rice)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("DELETE FROM reportes_2024 WHERE alumno_id = (SELECT id FROM alumnos WHERE RICE = ?)");
        $stmt->bind_param("s", $rice);

        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar los reportes del alumno: " . $stmt->error);
        }
    }

    private function registerAcademicWithdrawal($student, $motivo_baja)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("INSERT INTO bajasacademicas 
                               (antiguo_RICE, CURP, nombre, ultimo_curso, seccion, fecha_baja, motivo_baja)
                               VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param(
            "ssssss",
            $student['RICE'],
            $student['CURP'],
            $student['nombre'],
            $student['grado'],
            $student['seccion'],
            $motivo_baja
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar la baja académica: " . $stmt->error);
        }
    }

    private function deleteStudent($rice)
    {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("DELETE FROM alumnos WHERE RICE = ?");
        $stmt->bind_param("s", $rice);

        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar al alumno: " . $stmt->error);
        }
    }

    private function sanitizeRiceInput($rices)
    {
        $rice_array = explode(",", $rices);
        return array_map('trim', array_filter($rice_array));
    }
}

// Procesamiento del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db = new Database(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        $withdrawalSystem = new AcademicWithdrawal($db);

        $results = $withdrawalSystem->processWithdrawals($_POST['rices'], $_POST['motivo_baja']);

        // Mostrar resultados
        foreach ($results as $result) {
            $alertType = $result['success'] ? 'success' : 'danger';
            $alerts[] = [
                'type' => $alertType,
                'message' => $result['message']
            ];
        }

        $db->close();
    } catch (Exception $e) {
        $alerts[] = [
            'type' => 'danger',
            'message' => "Error en el sistema: " . $e->getMessage()
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dar de baja a alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: rgb(237, 237, 237);
            color: var(--dark-color);
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
        }

        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }

        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .rice-example {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
    <?php include 'funciones/icon.php'; ?>
</head>
<?php include 'navbar.php'; ?>

<body>
    <br>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="text-center">
                    <h1><i class="bi bi-person-x-fill"></i> Dar de baja a alumno</h1>
                    <p class="lead">Asignar baja</p>

                </div>

            </div>
            <div class="col-md-4 text-end">

            </div>
        </div>

    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-clipboard2-minus"></i> Registrar nueva baja</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" id="withdrawalForm">
                            <div class="mb-3">
                                <label for="rices" class="form-label">RICE/S </label>
                                <input type="text" class="form-control" id="rices" name="rices" required
                                    placeholder="Ejemplo: 12345, 54321, 67890">
                                <div class="rice-example mt-1">Separe múltiples RICES con comas</div>
                            </div>

                            <div class="mb-4">
                                <label for="motivo_baja" class="form-label">Motivo de baja</label>
                                <textarea class="form-control" id="motivo_baja" name="motivo_baja" rows="4"
                                    required></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-send-check"></i> Dar de baja
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php if (!empty($alerts)): ?>
        <div class="alert-container">
            <?php foreach ($alerts as $alert): ?>
                <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show" role="alert">
                    <?= $alert['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario
        document.getElementById('withdrawalForm').addEventListener('submit', function (e) {
            const rices = document.getElementById('rices').value.trim();

            if (!rices) {
                e.preventDefault();
                alert('Por favor ingrese al menos un RICE');
                return false;
            }

            // Confirmación antes de enviar
            if (!confirm('¿Está seguro de procesar estas bajas académicas? Esta acción no se puede deshacer.')) {
                e.preventDefault();
                return false;
            }

            return true;
        });

        // Auto-ocultar alertas después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>

</html>