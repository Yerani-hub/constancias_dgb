<?php namespace App\Models;

use CodeIgniter\Model;

class DescargaDocumentosModel extends Model
{
    // Definir el nombre de la tabla
    protected $table = 'tbl_ent_descarga_documentos_figuras_dgb_data';

    // Definir la clave primaria
    protected $primaryKey = 'id';

    // Definir los campos que pueden ser insertados o actualizados
    protected $allowedFields = [
        'folio', 
        'nombre_completo', 
        'id_tipo_documento', 
        'fechasCurso', 
        'estatus', 
        'folio_figura', 
        'tipo_figura', 
        'datos', 
        'area_solicita', 
        'firma', 
        'cadena_original', 
        'fecha_created', 
        'fecha_update'
    ];

    // Definir los tipos de campos para JSON
    protected $type = [
        'datos' => 'json',
        'firma' => 'json',
    ];

    // Definir las fechas automáticas para creación y actualización
    protected $useTimestamps = true;
    protected $createdField  = 'fecha_created';
    protected $updatedField  = 'fecha_update';

    protected $validationMessages = [
        'folio' => [
            'required' => 'El campo folio es obligatorio.',
            'max_length' => 'El campo folio no puede exceder los 50 caracteres.'
        ],
        // Puedes agregar más mensajes personalizados aquí si lo necesitas
    ];

    // Definir métodos personalizados si los necesitas
    public function getByFolio($folio)
    {
        return $this->where('folio', $folio)->first();
    }
}
