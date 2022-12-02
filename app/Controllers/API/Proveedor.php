<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;


class Proveedor extends ResourceController
{
    protected $modelName = 'App\Models\ProveedorModel';
    protected $format = 'json';


    public function index()
    {
        return $this->genericResponse($this->model->findAll(), NULL, 200);
    }

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

    public function getAll()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('proveedor');
        $builder->where('estatusProveedor', 1);
        $proveedoresACT = $builder->get();
        return $this->genericResponse($proveedoresACT->getResult(), NULL, 200);
    }
    
     public function searchBy()
    {
        $search=$this->request->getPOST('search');  
        $db      = \Config\Database::connect();
        $builder = $db->table('proveedor');
        $builder->like('nombreProveedor', $search);
        $builder->orLike('nombreContacto', $search);
        $builder->where('estatusProveedor', 1);
        $proveedoresACT = $builder->get();
        return $this->genericResponse($proveedoresACT->getResult(), NULL, 200);
    }

    public function create(){
        try{
            $proveedor = $this->request->getJSON();
            if($this->model->insert($proveedor)){
                return $this->genericResponse('ok',null,200);
            }else{
                return $this->failValidationErrors($this->model->validation->listErrors());
            }
        }catch(\Exception $e){
            return $this->failServerError($e);
        }
    }

    public function updateProveedor()
    {
        try {
            $proveedor =[
                "nombreProveedor"=>$this->request->getPOST("nombreProveedor"),
                "nombreContacto"=>$this->request->getPOST("nombreContacto"),
                "telefonoProveedor"=>$this->request->getPOST("telefonoProveedor"),
                "telefonoContacto"=>$this->request->getPOST("telefonoContacto"),
                "correoProveedor"=>$this->request->getPOST("correoProveedor"),
                "correoContacto"=>$this->request->getPOST("correoContacto")
            ];

            $idProveedor = $this->request->getPOST("idProveedor");
            if($this->model->update($idProveedor,$proveedor)){
                return $this->genericResponse('ok',null, 200);
            }
            else{
                return $this -> failValidationErrors($this->model->validation->listErrors());
            }

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
    
    public function updateEstatusE()
    {
        try {
            $idProveedor = $this->request->getPOST("idProveedor");
            if($this->model->update($idProveedor,['estatusProveedor'=>0])):
                return $this->genericResponse('ok',null, 200);
            else:
                return $this -> failValidationError($this->model->validation->listErrors());
            endif;

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
    
    public function getAllDel()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('proveedor');
        $builder->where('estatusProveedor', 0);
        $proveedoresACT = $builder->get();
        return $this->genericResponse($proveedoresACT->getResult(), NULL, 200);
    }
    
    public function updateEstatusA()
    {
        try {
            $idProveedor = $this->request->getPOST("idProveedor");
            if($this->model->update($idProveedor,['estatusProveedor'=>1])):
                return $this->genericResponse('ok',null, 200);
            else:
                return $this -> failValidationError($this->model->validation->listErrors());
            endif;

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
    
}
