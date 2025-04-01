<?php namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Login extends Controller
{
    public function __construct(){
        $this->session = \Config\Services::session();   
    }

    public function index()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) { 
            return view('login');
        }

        return redirect()->to('/Carga_archivos');
    }

    public function processLogin()
    {
        $session = session();
        $model = new UserModel();

        $usuario = $this->request->getPost('usuario');
        $password = htmlentities(addslashes($_POST["password"]));

        $user = $model->where('usuario', $usuario)->first();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['activo'] == 1) {
                $session->set([
                    'id_usuario' => $user['id_usuario'],
                    'usuario' => $user['usuario'],
                    'isLoggedIn' => true
                ]);
                return redirect()->to('/Carga_archivos');
            } else {
                $info = ['estatus' => false, 'mensaje' => 'Cuenta inactiva.'];
                return redirect()->to(base_url('index.php/Login/'))->with('info', $info);
            }
        } else {
            $info = ['estatus' => false, 'mensaje' => 'Usuario o contraseña incorrectos.'];
            return redirect()->to(base_url('index.php/Login/'))->with('info', $info);
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/Login');
    }

    public function insertar_user(){
        $model = new UserModel();

        $p = password_hash('123456', PASSWORD_DEFAULT);
        $model->insert([
            'usuario' => 'admin',
            'password' => $p,
            'activo' => 1
        ]);

        echo $p;
    }
}
