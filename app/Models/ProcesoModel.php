<?php namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class ProcesoModel extends Model
{
    protected $table      = 'tbl_ent_procesos'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Clave primaria

    protected $allowedFields = ['id_usuario', 'afectados', 'url', 'zip', 'version_constancia', 'fecha_created', 'fecha_update'];

    // Definir las fechas automáticas para creación y actualización
    protected $useTimestamps = true;
    protected $createdField  = 'fecha_created';
    protected $updatedField  = 'fecha_update';

    /**
     * Obtiene todos los procesos
     */
    public function getAll()
    {
        $db = Database::connect('default');
        $table = $db->table('tbl_ent_procesos p');
        $query = $table->select("p.*, u.usuario");
        $query = $table->join("tbl_ent_usuario_dgb u", "p.id_usuario=u.id_usuario", "inner");
        $query = $table->get();
        return $query->getResultArray();
    }

    /**
     * Obtiene un proceso por ID
     */
    public function getById($id)
    {
        return $this->find($id);
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
