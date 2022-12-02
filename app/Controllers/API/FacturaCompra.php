<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;


class FacturaCompra extends ResourceController
{
    protected $modelName = 'App\Models\FacturaCompraModel';
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
        $builder = $db->table('vista_facturaCompra_proveedor');
        $builder->where('estatusFacturaCompra', 1);
        $facturasACT = $builder->get();
        return $this->genericResponse($facturasACT->getResult(), NULL, 200);
    }
    
    public function getAllDel()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('vista_facturaCompra_proveedor');
        $builder->where('estatusFacturaCompra', 0);
        $facturasACT = $builder->get();
        return $this->genericResponse($facturasACT->getResult(), NULL, 200);
    }
    
    


    public function create(){
        try{
            $fotoFactura = $this->request->getFile('fotoFactura'); 
            $newName = $fotoFactura->getRandomName();

            $fotoFactura->move(ROOTPATH.'public/uploads/facturasCompra', $newName);

            $uri = 'https://www.refaccionariacinthya.com/servicios/refaccionaria/public/uploads/facturasCompra/'.$newName;
            
            $facturaCompra = [
                "numeroFactura"=>$this->request->getPOST('numeroFactura'),
                "fotoFactura"=>$uri,
                "fechaFactura"=>$this->request->getPOST('fechaFactura'),
                "idProveedor"=>$this->request->getPOST('idProveedor'),
            ];
            
            if($this->model->insert($facturaCompra)){
                return $this->genericResponse('ok',null,200);
            }else{
                return $this->failValidationErrors($this->model->validation->listErrors());
            }
        }catch(\Exception $e){
            return $this->failServerError($e);
        }
    }

    public function updateFacturaCompra()
    {
        try{
            
            $idFacturaCompra = $this->request->getPOST('idFacturaCompra');
            if($idFacturaCompra == 0 || is_null($idFacturaCompra)){
                return $this->genericResponse('No mames no mandaste id pto', $facturaCompra ,500);
            }else{
                $facturaCompra = [
                    "numeroFactura"=>$this->request->getPOST('numeroFactura'),                
                    "fechaFactura"=>$this->request->getPOST('fechaFactura'),
                    "idProveedor"=>$this->request->getPOST('idProveedor'),
                ];
                
                if($this->model->update($idFacturaCompra, $facturaCompra)){
                    return $this->genericResponse('ok',null,200);
                }else{
                   
                }
            
            
            }
        }catch(\Exception $e){
            return $this->failServerError($e);
        } 
        
    }
    
    public function updateFoto(){
        try{
            $idFacturaCompra = $this->request->getPOST('idFacturaCompra');
            if($idFacturaCompra == 0 || is_null($idFacturaCompra)){
                return $this->genericResponse('No mames no mandaste id pto', 'No mames no mandaste id pto' ,500);
            }else{
                $foto = $this->request->getFile('fotoFacturaCompra'); 
                $newName = $foto->getRandomName();
    
                $foto->move(ROOTPATH.'public/uploads/facturasCompra', $newName);
    
                $uri = 'https://www.refaccionariacinthya.com/servicios/refaccionaria/public/uploads/facturasCompra/'.$newName;
                
                $facturaCompra = [                
                    "fotoFactura"=>$uri                
                ];
                
                if($this->model->update($idFacturaCompra, $facturaCompra)){
                    return $this->genericResponse('ok',null,200);
                }else{
                   
                }
            }
        }catch(\Exception $e){
            return $this->failServerError($e);
        }
        
    }
    
    public function updateEstatusE()
    {
        try {
            $idFacturaCompra = $this->request->getPOST("idFacturaCompra");
            if($this->model->update($idFacturaCompra,['estatusFacturaCompra'=>0])):
                return $this->genericResponse('ok',null, 200);
            else:
                return $this -> failValidationError($this->model->validation->listErrors());
            endif;

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
    
    public function updateEstatusA()
    {
        try {
            $idFacturaCompra = $this->request->getPOST("idFacturaCompra");
            if($this->model->update($idFacturaCompra,['estatusFacturaCompra'=>1])):
                return $this->genericResponse('ok',null, 200);
            else:
                return $this -> failValidationError($this->model->validation->listErrors());
            endif;

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
    
    public function searchBy()
    {
        $search=$this->request->getPOST('search');  
        $db      = \Config\Database::connect();
        $builder = $db->table('vista_facturaCompra_proveedor');
        $builder->like('numeroFactura', $search);
        $builder->orLike('nombreProveedor', $search);
        $builder->where('estatusFacturaCompra', 1);
        $fact = $builder->get();
        return $this->genericResponse($fact->getResult(), NULL, 200);
    }
    
    
}
