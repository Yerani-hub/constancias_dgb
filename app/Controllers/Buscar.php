<?php namespace App\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;

class Buscar extends BaseController
{
    public function __construct(){
        $this->session = \Config\Services::session();   
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

    /** Busqueda de archivos */

    public function index()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) { 
            return redirect()->to('/Login');
        }

        return view('archivos_view');
    }

    public function view_files()
    {
        // Verifica si se envió el formulario con el campo de fechas
        $rangoFechas = $this->request->getPost('date_range');

        // Si no se envió el rango de fechas, mostramos un mensaje de error
        if (!$rangoFechas) {
            $info = ['estatus' => false, 'mensaje' => 'Por favor, selecciona un rango de fechas.'];
            return redirect()->to(base_url('index.php/Buscar/'))->with('info', $info);
        }

        // Si se enviaron fechas, llama a files_list para obtener los archivos filtrados
        $files_data = $this->files_list($rangoFechas);

        // Muestra los archivos filtrados
        $info = ['estatus' => true, 'files' => $files_data['files'], 'directory' => $files_data['directory']];
        return redirect()->to(base_url('index.php/Buscar/'))->with('info', $info);
    }

    private function files_list($rangoFechas)
    {
        $directory = '/Users/yerani/Downloads/proyectos/constancias_dgb/public/constancias/'; // Ruta de los archivos

        if (!is_dir($directory)) {
            return view('archivos_view', ['files' => [], 'directory' => $directory, 'error' => 'Directorio no encontrado.']);
        }

        $files = scandir($directory); // Obtiene todos los archivos
        $filtered_files = [];

        // Si el usuario seleccionó un rango de fechas
        if ($rangoFechas) {
            $fechas = explode(" - ", $rangoFechas);
            $startDate = strtotime($fechas[0] . ' 00:00:00'); // Fecha de inicio en timestamp
            $endDate = strtotime($fechas[1] . ' 23:59:59'); // Fecha de fin en timestamp

            foreach ($files as $file) {
                $file_path = $directory . '/' . $file;

                // Verifica que sea un archivo válido (ZIP o PDF)
                if (is_file($file_path) && in_array(pathinfo($file, PATHINFO_EXTENSION), ['zip'])) {
                    $fileDate = filemtime($file_path); // Última modificación
                    $fileSize = filesize($file_path); // Tamaño del archivo en bytes
                    $fileCreated = filectime($file_path); // Fecha de creación (en sistemas que lo soportan)

                    // Filtra archivos dentro del rango de fechas
                    if ($fileDate >= $startDate && $fileDate <= $endDate) {
                        $filtered_files[] = [
                            'name' => $file,
                            'size' => $this->formatSize($fileSize), // Formatea el tamaño en KB o MB
                            'modified' => date('Y-m-d H:i:s', $fileDate),
                            'created' => date('Y-m-d H:i:s', $fileCreated),
                        ];
                    }
                }
            }
        } else {
            // Si no hay filtro, mostrar todos los archivos ZIP y PDF
            foreach ($files as $file) {
                $file_path = $directory . '/' . $file;

                if (is_file($file_path) && in_array(pathinfo($file, PATHINFO_EXTENSION), ['zip', 'pdf'])) {
                    $filtered_files[] = [
                        'name' => $file,
                        'size' => $this->formatSize(filesize($file_path)),
                        'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
                        'created' => date('Y-m-d H:i:s', filectime($file_path)),
                    ];
                }
            }
        }

        return ['files' => $filtered_files, 'directory' => $directory];
    }

    // Función para formatear el tamaño de archivo en KB o MB
    private function formatSize($bytes)
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } else {
            return number_format($bytes / 1024, 2) . ' KB';
        }
    }
}