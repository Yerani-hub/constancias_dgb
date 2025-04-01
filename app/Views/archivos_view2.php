<!-- app/Views/archivos_view.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivos Disponibles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome -->
    <style>
        .file-list {
        display: flex;
        flex-wrap: wrap;
        gap: 40px; /* Aumenta el espacio entre los elementos */
        }
        .file-item {
            text-align: center;
            width: 100px;
            margin-bottom: 20px; /* Añadir separación inferior entre cada archivo */
        }
        .file-item i {
            font-size: 50px;
            color: #4CAF50;
            margin-bottom: 10px; /* Añadir margen inferior para separar el ícono del enlace */
        }
        .file-item a {
            text-decoration: none;
            color: #000;
            display: block;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <h1>Archivos Disponibles</h1>
    <div class="file-list">
        <?php foreach ($files as $file): ?>
            <div class="file-item">
                <?php 
                $file_path = $directory . '/' . $file;
                if (pathinfo($file, PATHINFO_EXTENSION) == 'zip'): ?>
                    <i class="fas fa-file-archive"></i> <!-- Icono para ZIP -->
                <?php elseif (pathinfo($file, PATHINFO_EXTENSION) == 'pdf'): ?>
                    <i class="fas fa-file-pdf"></i> <!-- Icono para PDF -->
                <?php endif; ?>
                <a href="<?= base_url('index.php/Carga_archivos/download?file_name=' . urlencode($file)) ?>">Descargar <?= htmlspecialchars($file) ?></a>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
