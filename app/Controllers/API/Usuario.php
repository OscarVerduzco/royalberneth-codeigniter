<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;


class Usuario extends ResourceController
{
    protected $modelName = 'App\Models\UsuarioModel';
    protected $format = 'json';

    
	

	private function genericResponse($data, $msj, $code)
    {
 
        if ($code == 200) {
            return $this->respond(array(
                "data" => $data,
                "code" => $code
            )); //, 404, "No hay nada"
        } else {
            return $this->respond(array(
                "msj" => $msj,
                "code" => $code
            ));
 		}
	}

    public function index()
    {
        return $this->genericResponse($this->model->findAll(), NULL, 200);
    }
    
    
    
    public function validarLogin(){
        $db      = \Config\Database::connect();
        $builder = $db->table('usuario');
        
        $builder->where('usuario', $this->request->getPost("user"));
        $builder->where('pass', $this->request->getPost("pass"));
        $idUsuario = $builder->get();
        if($idUsuario->getResult()!=null){
            $id=$idUsuario->getResult();
            return $this->genericResponse('ok', 'ok', 200);
        }else{
            $falso[]=array(
                "idUsuario"=>0,
                "usuario"=>"",
                "pass"=>"",
                "nombreUsuario"=>""
                );
            return $this->genericResponse('error', 'error', 200);
        }
    }
    
    
    
    
    

        

}
