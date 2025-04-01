<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Archivos</title>
</head>
<body>
    <h2>Subida y Descarga de Archivos</h2>
    
    <form action="<?= base_url('index.php/') ?>Carga_archivos/subirCSV" method="post" enctype="multipart/form-data">
        <input type="file" name="archivo_csv">
        <button name="uploadBtn" type="submit">Cargar Archivo</button>
    </form>
    <?php 
        // Verificar si hay una respuesta flash y si contiene errores
        if ($respuesta = session()->getFlashdata('respuesta')) {
            if (isset($respuesta['pdfs_generados']) && $respuesta['pdfs_generados'] > 0) {
            ?>
            <a href="<?= base_url("constancias/" . $respuesta['archivo_zip']) ?>" class="btn btn-success" download>
                Descargar PDF's Comprimidos 📥
            </a>
            <?php
            }
        }
    ?>
    <br><br>
    <table border="1">
        <thead>
            <tr>
                <th>Folio Constancia</th>
                <th>Fila excel</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody id="tablaArchivos">
            <?php 
            // Verificar si hay una respuesta flash y si contiene errores
            if ($respuesta = session()->getFlashdata('respuesta')) {
                if (isset($respuesta['errores']) && count($respuesta['errores']) > 0) {
                    // Recorrer los errores y mostrarlos en la tabla
                    foreach ($respuesta['errores'] as $error) {
                        // Extraer datos del error
                        $folio = $error['folio'];
                        $fila = $error['fila'];
                        ?>
                        <tr>
                            <td><?php echo $folio; ?></td>
                            <td><?php echo "Error en fila " . $fila; ?></td>
                            <td><?php echo $error['mensaje']; ?></td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
        </tbody>
    </table>

    <script type="text/javascript">
        <?php if (session()->getFlashdata('respuesta')): ?>
            let respuesta = <?= json_encode(session()->getFlashdata('respuesta')) ?>;
            if (!respuesta.estatus) {
                alert(respuesta.mensaje);
            }
        <?php endif; ?>
    </script>
</body>
</html>

