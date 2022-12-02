<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;


class Producto extends ResourceController
{
    protected $modelName = 'App\Models\ProductoModel';
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
        $builder = $db->table('vista_producto_fCompra_proveedor');
        $builder->where('estatusProducto', 1);
        $productosACT = $builder->get();
        return $this->genericResponse($productosACT->getResult(), NULL, 200);
    }
    
    public function agregarStock()
    {
        
        try {
            $idProducto = $this->request->getPOST("idProducto");
            $cantidad = $this->request->getPOST("cantidad");
            if($idProducto == 0 || is_null($idProducto) || $cantidad < 1){
                return $this->genericResponse('No mames no mandaste id pto', 'Datos erroneos' ,500);
                
            }else{
                $db      = \Config\Database::connect();
                $builder = $db->table('producto');
                $builder->where('idProducto', $idProducto);
                $builder->increment('stock', $cantidad);
                $productosACT = $builder->get();
                return $this->genericResponse($productosACT->getResult(), NULL, 200);
            }

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
    
    public function getAllDel()
    {
        $db      = \Config\Database::connect();
        $builder = $db->table('vista_producto_fCompra_proveedor');
        $builder->where('estatusProducto', 0);
        $productosACT = $builder->get();
        return $this->genericResponse($productosACT->getResult(), NULL, 200);
    }

    public function create(){
        try{
            $foto = $this->request->getFile('fotoProducto'); 
            $newName = $foto->getRandomName();

            $foto->move(ROOTPATH.'public/uploads/productos', $newName);

            $uri = 'https://www.refaccionariacinthya.com/servicios/refaccionaria/public/uploads/productos/'.$newName;
            
            
            $producto = 
            [
                "codigo"=>$this->request->getPOST("codigo"),
                "nombreProducto"=>$this->request->getPOST("nombreProducto"),
                "numeroParte"=>$this->request->getPOST("numeroParte"),
                "foto"=>$this->request->getPOST("foto"),
                "descripcion"=>$this->request->getPOST("descripcion"),
                "costoCompra"=>$this->request->getPOST("costoCompra"),
                "precioVenta"=>$this->request->getPOST("precioVenta"),
                "posicion"=>$this->request->getPOST("posicion"),
                "marca"=>$this->request->getPOST("marca"),
                "unidad"=>$this->request->getPOST("unidad"),
                "stock"=>$this->request->getPOST("stock"),
                "idProveedor"=>$this->request->getPOST("idProveedor"),
                "idFacturaCompra"=>$this->request->getPOST("idFacturaCompra"),
                "foto"=>$uri
            ];
            if($this->model->insert($producto)){
                return $this->genericResponse('ok',null,200);
            }else{
                return $this->failValidationErrors($this->model->validation->listErrors());
            }
        }catch(\Exception $e){
            return $this->failServerError($e);
        }
    }

    public function updateProducto()
    {
        try {
            $idProducto = $this->request->getPOST("idProducto");
            if($idProducto == 0 || is_null($idProducto)){
                return $this->genericResponse('No mames no mandaste id pto', 'No mames no mandaste id pto' ,500);
            }
            else{
                
                $producto =[
                    "codigo"=>$this->request->getPOST("codigo"),
                    "nombreProducto"=>$this->request->getPOST("nombreProducto"),
                    "numeroParte"=>$this->request->getPOST("numeroParte"),
                    "descripcion"=>$this->request->getPOST("descripcion"),
                    "costoCompra"=>$this->request->getPOST("costoCompra"),
                    "precioVenta"=>$this->request->getPOST("precioVenta"),
                    "posicion"=>$this->request->getPOST("posicion"),
                    "marca"=>$this->request->getPOST("marca"),
                    "unidad"=>$this->request->getPOST("unidad"),
                    "stock"=>$this->request->getPOST("stock"),
                    "idProveedor"=>$this->request->getPOST("idProveedor"),
                    "idFacturaCompra"=>$this->request->getPOST("idFacturaCompra"),
                ];
    
               
                if($this->model->update($idProducto,$producto)){
                    return $this->genericResponse('ok',null, 200);
                }
                else{
                    return $this -> failValidationErrors($this->model->validation->listErrors());
                }
            }

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
    
    public function updateFoto(){
        try{
            $idProducto = $this->request->getPOST("idProducto");
            if($idProducto == 0 || is_null($idProducto)){
                return $this->genericResponse('No mames no mandaste id pto', 'No mames no mandaste id pto' ,500);
            }
            else{
                $foto = $this->request->getFile('fotoProducto'); 
                $newName = $foto->getRandomName();
    
                $foto->move(ROOTPATH.'public/uploads/productos', $newName);
    
                $uri = 'https://www.refaccionariacinthya.com/servicios/refaccionaria/public/uploads/productos/'.$newName;
                
                $producto = [                
                    "foto"=>$uri                
                ];
                
                if($this->model->update($idProducto, $producto)){
                    return $this->genericResponse('ok',null,200);
                }else{
                   
                }
            }
        }catch(\Exception $e){
            return $this->failServerError($e);
        }
        
    }
    
    public function updateEstatusA()
    {
        try {
            $idProducto = $this->request->getPOST("idProducto");
            if($this->model->update($idProducto,['estatusProducto'=>1])):
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
        $builder = $db->table('vista_producto_fCompra_proveedor');
        $builder->like('codigo', $search);
        $builder->orLike('nombreProducto', $search);
        $builder->orLike('marca', $search);
        $builder->orLike('descripcion', $search);
        $builder->where('estatusProducto', 1);
        $productosACT = $builder->get();
        return $this->genericResponse($productosACT->getResult(), NULL, 200);
    }
    
    public function updateEstatusE()
    {
        try {
            $idProducto = $this->request->getPOST("idProducto");
            if($this->model->update($idProducto,['estatusProducto'=>0])):
                return $this->genericResponse('ok',null, 200);
            else:
                return $this -> failValidationError($this->model->validation->listErrors());
            endif;

        } catch (\Exception $e) {
            return $this->failServerError($e);
        } 
        
    }
}
