<?php namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;

class Carga_archivos extends BaseController
{
    public function __construct(){
        $this->session = \Config\Services::session();   
    }

    public function index()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) { 
            return redirect()->to('/Login');
        }

        return view('cargarCSV');
    }

    public function subirCSV()
    {
        $archivo = $_FILES['archivo_csv'];
        $respuesta = $this->validar_csv($archivo);
    
        $errores = []; // Aquí se guardarán todos los errores encontrados
        $filas_validas = []; // Guardaremos solo las filas correctas
        $contador_pdf = 0;
    
        if ($respuesta["estatus"]) {
            if (($handle = fopen($archivo['tmp_name'], 'r')) !== FALSE) {
                $cabeceras = fgetcsv($handle, 1000, ',');
                $columnas_necesarias = [
                    'nombre', 'primer apellido', 'segundo apellido',
                    'fecha inicio curso', 'fecha fin curso', 
                    'calificación', 'autoridad firmante', 'folio'
                ];
    
                $fila_numero = 2;
                while (($datos = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    if (count($datos) !== count($columnas_necesarias)) {
                        $errores[] = [
                            'fila' => $fila_numero,
                            'mensaje' => 'Faltan columnas en la fila ' . $fila_numero
                        ];
                    } else {
                        $obs = '';
                        foreach ($columnas_necesarias as $index => $columna) {
                            if (empty(trim($datos[$index]))) {
                                $obs .= '* La columna ' . $columna . ' está vacía<br>';
                            }
                        }
    
                        if ($obs != '') {
                            $errores[] = [
                                'fila' => $fila_numero,
                                'mensaje' => $obs
                            ];
                        } else {
                            $filas_validas[] = [
                                'folio' => trim($datos[7]),
                                'nombre' => trim($datos[0]) . ' ' . trim($datos[1]) . ' ' . trim($datos[2]),
                                'id_tipo_documento' => 1,  // Asumiendo un valor por defecto
                                'fechasCurso' => trim($datos[3]) . ' - ' . trim($datos[4]),
                                'estatus' => 1,
                                'folio_figura' => '',
                                'tipo_figura' => '',
                                'datos' => json_encode(['calificacion' => trim($datos[5])]),
                                'area_solicita' => 'DGB',
                                'firma' => json_encode(['autoridad' => trim($datos[6])]),
                                'cadena_original' => '',
                                'nombre_individual' => trim($datos[0]),
                                'apellidos' => trim($datos[1]) . ' ' . trim($datos[2])
                            ];
                        }
                    }
                    $fila_numero++;
                }
                fclose($handle);
            }
        }
    
        // Guardar en la base de datos si hay filas válidas
        $model = new \App\Models\DescargaDocumentosModel();
        foreach ($filas_validas as $fila) {
            try {
                $model->insert($fila);
            } catch (\Exception $e) {
                $errores[] = [
                    'folio' => $fila['folio'],
                    'mensaje' => 'Error al guardar en BD: ' . $e->getMessage()
                ];
            }
        }

        // Crear la carpeta con la fecha y hora actual (una sola vez)
        $nombre_carpeta = date('Y-m-d_H-i-s');
        $carpeta_destino = "/Users/yerani/Downloads/proyectos/constancias_dgb/public/constancias/" . $nombre_carpeta;
        if (!is_dir($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
    
        // Dividir las filas en lotes de 10
        $lotes = array_chunk($filas_validas, 100);  // 10 elementos por lote
    
        $resultados_pdf = ['errores' => []];
        foreach ($lotes as $lote) {
            $resultados_pdf_lote = $this->generar_pdf_lote($lote, $carpeta_destino);
            $resultados_pdf['errores'] = array_merge($resultados_pdf['errores'], $resultados_pdf_lote['errores']);

            $contador_pdf += count($resultados_pdf_lote['pdfs_generados']);
        }

        // Comprimir la carpeta
        $archivo_zip = $this->comprimirCarpeta($carpeta_destino);

        $this->eliminarCarpeta($carpeta_destino);
    
        // Determinar estatus final
        $respuesta = ["estatus" => true, "mensaje" => "Proceso completado.", "errores" => $errores, "archivo_zip" => $nombre_carpeta . ".zip", "pdfs_generados" => $contador_pdf];
    
        return redirect()->to(base_url("/index.php/Carga_archivos"))->with('respuesta', $respuesta);
    }        
    
    private function validar_csv($archivo) {
        $respuesta = [];
    
        // Verificar si se subió un archivo correctamente
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            $respuesta = ["estatus" => false, "mensaje" => "Error: No se ha subido un archivo válido."];
        } else {
            // Verificar la extensión del archivo
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            if (strtolower($extension) !== 'csv') {
                $respuesta = ["estatus" => false, "mensaje" => "Error: El archivo no es un CSV."];
            }
    
            // Verificar el MIME type
            $mime = mime_content_type($archivo['tmp_name']);
            $mimes_permitidos = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
            if (!in_array($mime, $mimes_permitidos)) {
                $respuesta = ["estatus" => false, "mensaje" => "Error: El archivo no tiene un formato CSV válido."];
            }
    
            // Leer el contenido del CSV
            $csvData = [];
            if (($handle = fopen($archivo['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Eliminar espacios en blanco y verificar si la fila está vacía
                    $data = array_map('trim', $data);
                    if (array_filter($data)) { // Si la fila no está vacía, la guardamos
                        $csvData[] = $data;
                    }
                }
                fclose($handle);
    
                $respuesta = ["estatus" => true, "mensaje" => ""];
            } else {
                $respuesta = ["estatus" => false, "mensaje" => "Error: No se pudo leer el archivo."];
            }
    
            // Verificar que el CSV no esté vacío después de filtrar filas vacías
            if (empty($csvData)) {
                $respuesta = ["estatus" => false, "mensaje" => "Error: El archivo CSV está vacío o solo contiene filas en blanco."];
            }
        }
        return $respuesta;
    }    

    private function fechas_esp(){
        $meses = [
            'January' => 'ENERO',
            'February' => 'FEBRERO',
            'March' => 'MARZO',
            'April' => 'ABRIL',
            'May' => 'MAYO',
            'June' => 'JUNIO',
            'July' => 'JULIO',
            'August' => 'AGOSTO',
            'September' => 'SEPTIEMBRE',
            'October' => 'OCTUBRE',
            'November' => 'NOVIEMBRE',
            'December' => 'DICIEMBRE'
        ];
    
        $fecha = date('d') . ' ' . $meses[date('F')] . ' ' . date('Y');
        return $fecha;
    }

    private function comprimirCarpeta($carpeta_origen)
    {
        $zip = new \ZipArchive();
        $archivo_zip = $carpeta_origen . '.zip';

        if ($zip->open($archivo_zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $archivos = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($carpeta_origen),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($archivos as $archivo) {
                if (!$archivo->isDir()) {
                    $ruta_relativa = substr($archivo, strlen($carpeta_origen) + 1);
                    $zip->addFile($archivo, $ruta_relativa);
                }
            }

            $zip->close();
        }

        return $archivo_zip; 
    }

    private function eliminarCarpeta($carpeta)
    {
        $files = array_diff(scandir($carpeta), ['.', '..']);

        foreach ($files as $file) {
            $ruta = $carpeta . DIRECTORY_SEPARATOR . $file;
            is_dir($ruta) ? $this->eliminarCarpeta($ruta) : unlink($ruta);
        }

        return rmdir($carpeta);
    }

    private function generar_pdf_lote($lote, $carpeta_destino) {
        $resultados = [
            'pdfs_generados' => [],
            'errores' => []
        ];
    
        foreach ($lote as $fila) {
            try {
                $nombre = htmlspecialchars($fila['nombre_individual']);
                $apellidos = htmlspecialchars($fila['apellidos']);
                $autoridad = json_decode($fila['firma'], true)['autoridad'];
                $fecha = $this->fechas_esp();

                // Crear instancia de DOMPDF
                $dompdf = new Dompdf();
                $options = new Options();
                $options->set('isRemoteEnabled', true); // Para permitir imágenes remotas
                $dompdf = new Dompdf($options);
                
                $html = '<html>
                            <head>
                                <style>
                                    @page {
                                        margin: 0px;
                                    }

                                    body { 
                                        margin-top: 0px;
                                        margin-left: 0px;
                                        margin-right: 0px;
                                        margin-bottom: 0px;
                                    }

                                    header {
                                        position: fixed;
                                        top: 0px;
                                        left: 0px;
                                        right: 0px;
                                        height: 100px;
                                        text-align: center;
                                        font-size: 20px;
                                        font-weight: bold;
                                        padding: 20px 0;
                                    }

                                    .encabezado{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 16pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .constancia{
                                        font-family: notosans;
                                        font-style: bold;
                                        font-size: 48pt;
                                        line-height: 1.0;
                                        color: #b89c27;
                                    }

                                    .body1{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 18pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body2{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 24pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body3{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 12pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body4{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 14pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body5{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 14pt;
                                        line-height: 1.0;
                                        color: #b89c27;
                                    }

                                    table, tr, td, th{
                                    border: solid white 1px;
                                    }
                                </style>
                            </head>
                            <body>
                                <img class="img-fluid" style="position: absolute; top: -15px; left: 0px; width: 820px; height: 1085px;" src="http://localhost:85/constancias_dgb/public/images/imagenes_sec_mujeres_2025/fondo_mujeres.jpg">
                                
                                <table style="position: absolute;  top: 50px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"><img style="width: 100%" src= "http://localhost:85/constancias_dgb/public/images/imagenes_sec_mujeres_2025/logos_mujeres.jpg"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table style="position: absolute;  top: 180px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center" class="encabezado">
                                                LA DIRECCIÓN GENERAL DEL BACHILLERATO Y<br>EL INSTITUTO NACIONAL DE LAS MUJERES<br>OTORGAN LA PRESENTE
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="constancia" style="padding: 20px;">
                                                <b>CONSTANCIA</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body1" style="padding-bottom: 20px;">
                                                A
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body2" style="padding-bottom: 20px;">
                                                ' . $nombre .'<br>' . $apellidos . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body3" style="padding: 5px;">
                                                Por su participación en el curso<br>“¡Sumate al protocolo!”
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="padding-top: 100px;">
                                                <hr style="border: 0.5px solid #b89c27; width: 45%; margin: 20px auto;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body4" style="padding-bottom: 20px;">
                                                <b>' . $autoridad . '</b><br>Director/ Maestro/Responsable
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body5">
                                                <b>CIUDAD DE MÉXICO, ' . $fecha . '</b>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </body>
                        </html>';

                // Cargar y renderizar el PDF
                $dompdf->loadHtml($html);
                $dompdf->setPaper('letter', 'portrait');
                $dompdf->render();
    
                $output = $dompdf->output();
                $filename = "constancia_" . $fila['folio'] . ".pdf";
                
                file_put_contents($carpeta_destino . "/" . $filename, $output);
                $resultados['pdfs_generados'][] = $filename;

            } catch (\Exception $e) {
                $resultados['errores'][] = [
                    'folio' => $fila['folio'],
                    'mensaje' => 'Error al generar el PDF: ' . $e->getMessage()
                ];
            }
        }
    
        return $resultados;
    }    

    public function download()
    {
        $archivo = $_GET['file_name'];
        $file_path = '/Users/yerani/Downloads/proyectos/constancias_dgb/public/constancias/' . $archivo;

        // Verifica si el archivo existe
        if (file_exists($file_path)) {
            // Forzar la descarga del archivo
            return $this->response->download($file_path, null)->setFileName($archivo);
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("El archivo no existe.");
        }
    }
    
    public function verpdf()
    {
        $nombre = "Nombre nombre";
        $apellidos = "Apellidos apellidos";
        $autoridad = "firma";
        $fecha = $this->fechas_esp();

        set_time_limit(120);
        // Inicializar DOMPDF
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Para permitir imágenes remotas
        $dompdf = new Dompdf($options);

        $html = '<html>
                            <head>
                                <style>
                                    @page {
                                        margin: 0px;
                                    }

                                    body { 
                                        margin-top: 0px;
                                        margin-left: 0px;
                                        margin-right: 0px;
                                        margin-bottom: 0px;
                                    }

                                    header {
                                        position: fixed;
                                        top: 0px;
                                        left: 0px;
                                        right: 0px;
                                        height: 100px;
                                        text-align: center;
                                        font-size: 20px;
                                        font-weight: bold;
                                        padding: 20px 0;
                                    }

                                    .encabezado{
                                        font-family: patria;
                                        font-style: light;
                                        font-size: 16pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .constancia{
                                        font-family: patria;
                                        font-style: bold;
                                        font-size: 48pt;
                                        line-height: 1.0;
                                        color: #b89c27;
                                    }

                                    .body1{
                                        font-family: patria;
                                        font-style: light;
                                        font-size: 18pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body2{
                                        font-family: patria;
                                        font-style: light;
                                        font-size: 24pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body3{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 12pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body4{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 14pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }

                                    .body5{
                                        font-family: patria;
                                        font-style: normal;
                                        font-size: 14pt;
                                        line-height: 1.0;
                                        color: #b89c27;
                                    }

                                    .body6{
                                        font-family: notosans;
                                        font-style: normal;
                                        font-size: 9pt;
                                        line-height: 1.0;
                                        color: #6a6a6a;
                                    }
                                </style>
                            </head>
                            <body>
                                <img style="position: absolute; top: -15px; left: 0px; width: 820px; height: 1085px;" src="http://localhost:85/constancias_dgb/public/images/imagenes_sec_mujeres_2025/fondo_mujeres.jpg">
                                
                                <table style="position: absolute;  top: 50px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"><img style="width: 100%" src= "http://localhost:85/constancias_dgb/public/images/imagenes_sec_mujeres_2025/logos_mujeres.jpg"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table style="position: absolute;  top: 180px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center" class="encabezado">
                                                LA DIRECCIÓN GENERAL DEL BACHILLERATO Y<br>EL INSTITUTO NACIONAL DE LAS MUJERES<br>OTORGAN LA PRESENTE
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="constancia" style="padding: 20px;">
                                                <b>CONSTANCIA</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body1" style="padding-bottom: 20px;">
                                                A
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body2" style="padding-bottom: 20px;">
                                                ' . $nombre .'<br>' . $apellidos . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body3" style="padding: 5px;">
                                                Por su participación en el curso<br>“¡Sumate al protocolo!”
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="padding-top: 100px;">
                                                <hr style="border: 0.5px solid #b89c27; width: 45%; margin: 20px auto;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body4" style="padding-bottom: 20px;">
                                                <b>' . $autoridad . '</b><br>Director/ Maestro/Responsable
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="body5">
                                                <b>CIUDAD DE MÉXICO, ' . $fecha . '</b>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div style="page-break-after:always;"></div>

                                <img style="position: absolute; top: -15px; left: 0px; width: 820px; height: 1085px;" src="http://localhost:85/constancias_dgb/public/images/imagenes_sec_mujeres_2025/fondo_mujeres.jpg">

                                <table style="position: absolute;  top: 750px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"><img style="width: 90%" src= "http://localhost:85/constancias_dgb/public/images/imagenes_sec_mujeres_2025/folio2.png"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table style="position: absolute; top: 769px; left: 480px; width: 120px;">
                                    <tbody>
                                        <tr>
                                            <td align="center" style="width:120px; height: 30px;" class="body6">
                                                I/2025
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="width:120px; height: 35px;" class="body6">
                                                1000
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="width:120px; height: 33px;" class="body6">
                                                26/03/2025
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="width:120px; height: 32px;" class="body6">
                                                JARS
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                               
                            </body>
                        </html>';

        $dompdf->loadHtml($html);

        $dompdf->setPaper('letter', 'portrait'); // 'portrait' (vertical) o 'landscape' (horizontal)

        $dompdf->render();

        $dompdf->stream('archivo.pdf', ['Attachment' => 0]); // 0 = Ver en navegador, 1 = Descargar
    }
}