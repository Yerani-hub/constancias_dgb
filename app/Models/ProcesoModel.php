<?php namespace App\Models;

use CodeIgniter\Model;

class ProcesoModel extends Model
{
    protected $table      = 'tbl_ent_procesos'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Clave primaria

    protected $allowedFields = ['id_usuario', 'afectados', 'zip', 'version_constancia', 'fecha_created', 'fecha_update'];

    /**
     * Obtiene todos los procesos
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Obtiene un proceso por ID
     */
    public function getById($id)
    {
        return $this->find($id);
    }

    /**
     * Inserta un nuevo proceso
     */
    public function insertProceso($data)
    {
        return $this->insert($data);
    }

    /**
     * Actualiza un proceso por ID
     */
    public function updateProceso($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Elimina un proceso por ID
     */
    public function deleteProceso($id)
    {
        return $this->delete($id);
    }

    /**
     * Obtiene los procesos de un usuario específico
     */
    public function getByUsuario($id_usuario)
    {
        return $this->where('id_usuario', $id_usuario)->findAll();
    }
}
