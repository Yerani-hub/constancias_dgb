<?php namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;

class Carga_archivos extends BaseController
{
    const URL_ARCHIVOS = "/Users/yerani/Downloads/proyectos/constancias_dgb/public/constancias/";
    const URL_IMAGES = "http://localhost/constancias_dgb/public/images/imagenes_sec_mujeres_2025/";
    

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

    $errores = []; 
    $creados = []; 
    $filas_validas = []; 

    if ($respuesta["estatus"]) {
        $proceso = $this->insert_proceso();

        if ($proceso["estatus"]) {
            try {
                if (($handle = fopen($archivo['tmp_name'], 'r')) === FALSE) {
                    throw new \Exception("No se pudo abrir el archivo CSV.");
                }

                $cabeceras = fgetcsv($handle, 1000, ',');
                $columnas_necesarias = [
                    'nombre', 'primer apellido', 'segundo apellido',
                    'fecha inicio curso', 'fecha fin curso', 
                    'calificación', 'autoridad firmante', 'folio'
                ];

                $fila_numero = 2;
                while (($linea = fgets($handle)) !== false) {
                    // Convertir la línea a UTF-8
                    $linea =mb_convert_encoding($linea, 'UTF-8', 'Windows-1252');
                    $datos = str_getcsv($linea, ',');

                    $log_data = $this->get_data_proceso_json($datos, $fila_numero);

                    if (count($datos) !== count($columnas_necesarias)) {
                        $errores[] = [
                            'info_csv' => json_encode($log_data),
                            'estatus' => 'error',
                            'mensaje' => 'Faltan columnas en la fila ' . $fila_numero . ' del archivo CSV.',
                            'id_proceso' => $proceso['id_insertado'],
                            'folio' => trim($datos[7] ?? ''),
                            'fila' => $fila_numero
                        ];
                    } else {
                        $obs = '';
                        foreach ($columnas_necesarias as $index => $columna) {
                            if (empty(trim($datos[$index]))) {
                                $obs .= '* La columna ' . $columna . ' está vacía ';
                            }
                        }

                        if ($obs != '') {
                            $errores[] = [
                                'info_csv' => json_encode($log_data),
                                'estatus' => 'error',
                                'mensaje' => $obs,
                                'id_proceso' => $proceso['id_insertado'],
                                'folio' => trim($datos[7]),
                                'fila' => $fila_numero
                            ];
                        } else {
                            $filas_validas[] = [
                                'fila' => $fila_numero,
                                'folio' => trim($datos[7]),
                                'nombre' => trim($datos[0]) . ' ' . trim($datos[1]) . ' ' . trim($datos[2]),
                                'id_tipo_documento' => 1,
                                'fechasCurso' => trim($datos[3]) . ' - ' . trim($datos[4]),
                                'estatus' => 1,
                                'folio_figura' => '',
                                'tipo_figura' => '',
                                'datos' => json_encode(['calificacion' => trim($datos[5])]),
                                'area_solicita' => 'DGB',
                                'firma' => json_encode(['autoridad' => trim($datos[6])]),
                                'cadena_original' => '',
                                'nombre_individual' => trim($datos[0]),
                                'apellidos' => trim($datos[1]) . ' ' . trim($datos[2]),
                                'info_csv' => json_encode($log_data)
                            ];
                        }
                    }
                    $fila_numero++;
                }
                fclose($handle);
            } catch (\Throwable $e) {
                $respuesta = [
                    "estatus" => false,
                    "mensaje" => "Error al procesar el archivo CSV: " . $e->getMessage()
                ];
                return redirect()->to(base_url("/index.php/Carga_archivos"))->with('info', $respuesta);
            }

            $nombre_carpeta = date('Y-m-d_H-i-s');
            $carpeta_destino = self::URL_ARCHIVOS . $nombre_carpeta;
            if (!is_dir($carpeta_destino)) {
                mkdir($carpeta_destino, 0777, true);
            }

            $lotes = array_chunk($filas_validas, 100);
            $db = \Config\Database::connect();
            $model = new \App\Models\DescargaDocumentosModel();

            foreach ($lotes as $lote) {
                foreach ($lote as $fila) {
                    try {
                        $existe = $this->get_folio($fila['folio']);

                        if (!$existe) {
                            $pdf = $this->generar_pdf($fila, $carpeta_destino);

                            if ($pdf['estatus'] == 'creado') {
                                $model->insert($fila); 

                                $creados[] = [
                                    'info_csv' => $fila['info_csv'],
                                    'estatus' => $pdf['estatus'],
                                    'mensaje' => $pdf['mensaje'],
                                    'id_proceso' => $proceso['id_insertado'],
                                    'folio' => $pdf['folio'],
                                    'fila' => $fila['fila']
                                ];
                            } else {
                                $errores[] = [
                                    'info_csv' => $fila['info_csv'],
                                    'estatus' => $pdf['estatus'],
                                    'mensaje' => $pdf['mensaje'],
                                    'id_proceso' => $proceso['id_insertado'],
                                    'folio' => $pdf['folio'],
                                    'fila' => $fila['fila']
                                ];
                            }
                        } else {
                            $errores[] = [
                                'info_csv' => $fila['info_csv'],
                                'estatus' => 'error',
                                'mensaje' => 'El folio ya se encuentra registrado.',
                                'id_proceso' => $proceso['id_insertado'],
                                'folio' => $fila['folio'],
                                'fila' => $fila['fila']
                            ];
                        }
                    } catch (\Exception $e) {
                        $errores[] = [
                            'info_csv' => $fila['info_csv'],
                            'estatus' => 'error',
                            'mensaje' => 'Error al guardar en BD: ' . $e->getMessage(),
                            'id_proceso' => $proceso['id_insertado'],
                            'folio' => $fila['folio'],
                            'fila' => $fila['fila']
                        ];
                    }
                }
            }

            if(count($creados) > 0){
                $archivo_zip = $this->comprimirCarpeta($carpeta_destino);
                $this->update_proceso($proceso['id_insertado'], count($creados), self::URL_ARCHIVOS, $nombre_carpeta . '.zip');

            }
            
            $this->eliminarCarpeta($carpeta_destino);
            $logs = array_merge($errores, $creados);
            $this->insert_logs($logs);
            
            $respuesta = [
                "estatus" => true, 
                "mensaje" => "Proceso completado.", 
                "errores" => $errores, 
                "archivo_zip" => $nombre_carpeta . ".zip", 
                "pdfs_generados" => count($creados)
            ];
        
            return redirect()->to(base_url("/index.php/Carga_archivos/l_process?i=" . $proceso['id_insertado']))->with('info', $respuesta);
        } else {
            return redirect()->to(base_url("/index.php/Carga_archivos"))->with('info', $proceso);
        }
    } else {
        return redirect()->to(base_url("/index.php/Carga_archivos"))->with('info', $respuesta);
    }
}
   
    
    private function validar_csv($archivo) {
        $respuesta = [];
    
        // Columnas requeridas en el archivo CSV
        $columnas_necesarias = [
            'nombre', 'primer apellido', 'segundo apellido',
            'fecha inicio curso', 'fecha fin curso', 
            'calificación', 'autoridad firmante', 'folio'
        ];
    
        // Verificar si se subió un archivo correctamente
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return ["estatus" => false, "mensaje" => "Error: No se ha subido un archivo válido."];
        }
    
        // Verificar la extensión del archivo
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'csv') {
            return ["estatus" => false, "mensaje" => "Error: El archivo no es un CSV."];
        }
    
        // Verificar el MIME type
        $mime = mime_content_type($archivo['tmp_name']);
        $mimes_permitidos = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
        if (!in_array($mime, $mimes_permitidos)) {
            return ["estatus" => false, "mensaje" => "Error: El archivo no tiene un formato CSV válido."];
        }
    
        // Leer el contenido del CSV
        if (($handle = fopen($archivo['tmp_name'], "r")) !== FALSE) {
            // Leer la primera fila (encabezados)
            $primera_fila = fgetcsv($handle, 1000, ",");
    
            if ($primera_fila === FALSE) {
                return ["estatus" => false, "mensaje" => "Error: No se pudo leer el archivo."];
            }
    
            // Verificar si el número de columnas es correcto
            $num_columnas = count($primera_fila);
            $num_columnas_necesarias = count($columnas_necesarias);
    
            // Si el número de columnas no coincide, retornar el error
            if ($num_columnas !== $num_columnas_necesarias) {
                return ["estatus" => false, "mensaje" => "Error: El número de columnas no coincide. El archivo debe tener " . $num_columnas_necesarias . " columnas. Las columnas necesarias son: " . implode(", ", $columnas_necesarias) . "."];
            }
    
            fclose($handle);
        } else {
            return ["estatus" => false, "mensaje" => "Error: No se pudo leer el archivo."];
        }
    
        return ["estatus" => true, "mensaje" => "Archivo válido."];
    }    

    private function get_folio($folio){
        $db = \Config\Database::connect();
        $model = new \App\Models\DescargaDocumentosModel();

        $constancia = $model->getByFolio($folio);

        if($constancia){
            return true;
        }else{
            return false;
        }
    }

    private function insert_proceso(){
        $session = session();

        // Guardar en la base de datos si hay filas válidas
        $model = new \App\Models\ProcesoModel();

        $data = [
            'id_usuario' => $session->get('id_usuario'),
            'afectados' => 0,
            'zip' => '',
            'version_constancia' => '1',
        ];

        try {
            $model->insert($data);
            $insertedId = $model->insertID();

            return ["estatus" => true, "mensaje" => "Registro insertado con éxito.", "id_insertado" => $insertedId];
        } catch (\Exception $e) {

            return ["estatus" => false, "mensaje" => $e->getMessage()];
        }
    }

    private function update_proceso($proceso, $afectados, $url, $zip){
        $model = new \App\Models\ProcesoModel();

        $data = [
            'afectados' => $afectados,
            'url' => $url,
            'zip' => $zip
        ];

        $model->update($proceso, $data);
    }

    private function insert_logs($logs){
        $session = session();

        // Guardar en la base de datos si hay filas válidas
        $model = new \App\Models\LogDetalleProcesoModel();

        foreach($logs as $log){
            $model->insert($log);
        }
    }

    private function get_data_proceso_json($datos, $fila){
        $data = [
            'fila' => trim($fila),
            'folio' => trim($datos[7]),
            'nombre' => trim($datos[0]),
            'primerApellido' => trim($datos[1]), 
            'segundoApellido' => trim($datos[2]),
            'fechasInicio' => trim($datos[3]),
            'fechasFin' => trim($datos[4]),
            'autoridad' => trim($datos[6])
        ];

        return $data;
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

    private function generar_pdf($fila, $carpeta_destino) {
        try {
            $nombre = htmlspecialchars($fila['nombre_individual']);
            $apellidos = htmlspecialchars($fila['apellidos']);
            $folio = htmlspecialchars($fila['folio']);
            $autoridad = json_decode($fila['firma'], true)['autoridad'];
            $fecha = $this->fechas_esp();
            $vol = "I/" . date("Y");
            $fecha = date("d/m/Y");

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
                                <img style="position: absolute; top: -15px; left: 0px; width: 820px; height: 1085px;" src="' . self::URL_IMAGES . 'fondo_mujeres.jpg">
                                
                                <table style="position: absolute;  top: 50px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"><img style="width: 100%" src= "' . self::URL_IMAGES . 'logos_mujeres.jpg"></td>
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

                                <img style="position: absolute; top: -15px; left: 0px; width: 820px; height: 1085px;" src="' . self::URL_IMAGES . 'fondo_mujeres.jpg">

                                <table style="position: absolute;  top: 750px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"><img style="width: 90%" src= "' . self::URL_IMAGES . 'folio2.png"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table style="position: absolute; top: 769px; left: 480px; width: 120px;">
                                    <tbody>
                                        <tr>
                                            <td align="center" style="width:120px; height: 30px;" class="body6">
                                                ' . $vol . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="width:120px; height: 35px;" class="body6">
                                                ' . $folio . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="width:120px; height: 33px;" class="body6">
                                                ' . $fecha . '
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

            // Cargar y renderizar el PDF
            $dompdf->loadHtml($html);
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();

            $output = $dompdf->output();
            $filename = "constancia_" . $fila['folio'] . ".pdf";
            
            file_put_contents($carpeta_destino . "/" . $filename, $output);
            $resultado = ['folio' => $fila['folio'], 'mensaje' => '', 'estatus' => 'creado'];

        } catch (\Exception $e) {
            $resultado = ['folio' => $fila['folio'], 'mensaje' => $e->getMessage(), 'estatus' => 'error'];
        }    
    
        return $resultado;
    }    

    public function download()
    {
        $archivo = $_GET['file_name'];
        $file_path = self::URL_ARCHIVOS . $archivo;

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
                                <img style="position: absolute; top: -15px; left: 0px; width: 820px; height: 1085px;" src="' . self::URL_IMAGES . 'fondo_mujeres.jpg">
                                
                                <table style="position: absolute;  top: 50px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"><img style="width: 100%" src= "' . self::URL_IMAGES . 'logos_mujeres.jpg"></td>
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

                                <img style="position: absolute; top: -15px; left: 0px; width: 820px; height: 1085px;" src="' . self::URL_IMAGES . 'fondo_mujeres.jpg">

                                <table style="position: absolute;  top: 750px; left: 40px; width: 680px;">
                                    <tbody>
                                        <tr>
                                            <td align="center"><img style="width: 90%" src= "' . self::URL_IMAGES . 'folio2.png"></td>
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

    public function g_process()
    {
        // Busqueda de procesos registrados en bd
        $model = new \App\Models\ProcesoModel();
        $procesos = $model->getAll();

        $data = array(
            'procesos' => $procesos
        );
        return view('procesos', $data);
    }

    public function l_process()
    {
        $id_proceso = $_GET['i'];
        $c = 0;

        // Busqueda de logs registrados en bd
        $model = new \App\Models\LogDetalleProcesoModel();
        $logs = $model->getByProceso($id_proceso);

        $url_zip = $model->get_url($id_proceso);

        foreach($logs as $log){
            if($log['estatus'] == 'creado'){
                $c ++;
            }
        }

        $data = array(
            'logs' => $logs,
            'url' => $url_zip['url'],
            'zip' => $url_zip['zip'],
            'contador' => $c
        );

        return view('log_proceso', $data);
    }
}