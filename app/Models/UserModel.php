<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'tbl_ent_usuario_dgb';
    protected $primaryKey = 'id_usuario';
    protected $allowedFields = ['usuario', 'password', 'activo', 'created_at', 'updated_at'];
}
