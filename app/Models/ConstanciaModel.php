<?php namespace App\Models;

use CodeIgniter\Model;

class ConstanciaModel extends Model
{
    protected $table      = 'tbl_cat_constancias_dgb'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Clave primaria

    protected $allowedFields = ['nombre']; // Campos que se pueden modificar

    protected $useTimestamps = false; // No se usarán timestamps automáticos

    /**
     * Obtiene todas las constancias
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Obtiene una constancia por ID
     */
    public function getById($id)
    {
        return $this->find($id);
    }

    /**
     * Inserta una nueva constancia
     */
    public function insertConstancia($data)
    {
        return $this->insert($data);
    }

    /**
     * Actualiza una constancia por ID
     */
    public function updateConstancia($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Elimina una constancia por ID
     */
    public function deleteConstancia($id)
    {
        return $this->delete($id);
    }
}
