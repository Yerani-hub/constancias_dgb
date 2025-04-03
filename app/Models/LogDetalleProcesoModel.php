<?php namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class LogDetalleProcesoModel extends Model
{
    protected $table      = 'tbl_log_detalles_procesos_dgb'; // Nombre de la tabla
    protected $primaryKey = 'id'; // Clave primaria

    protected $allowedFields = ['info_csv', 'estatus', 'mensaje', 'id_proceso', 'fecha_created', 'fecha_update'];

    /**
     * Obtiene todos los registros de log
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Obtiene un log por ID
     */
    public function getById($id)
    {
        return $this->find($id);
    }

    /**
     * Inserta un nuevo log de proceso
     */
    public function insertLog($data)
    {
        return $this->insert($data);
    }

    /**
     * Actualiza un log de proceso por ID
     */
    public function updateLog($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Elimina un log de proceso por ID
     */
    public function deleteLog($id)
    {
        return $this->delete($id);
    }

    /**
     * Obtiene los logs de un proceso específico
     */
    public function getByProceso($id_proceso)
    {
        return $this->where('id_proceso', $id_proceso)->findAll();
    }

    public function get_url($id_proceso)
    {
        $db = Database::connect('default');
        $table = $db->table('tbl_log_detalles_procesos_dgb l');
        $query = $table->select("p.url, p.zip");
        $query = $table->join("tbl_ent_procesos p", "p.id=l.id_proceso", "inner");
        $query = $table->where("p.id", $id_proceso);
        $query = $table->get();
        return $query->getRowArray();
    }
}
