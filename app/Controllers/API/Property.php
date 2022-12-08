<?php

namespace App\Controllers\API;
//import user
use App\Models\PropertyModel;
use App\Models\PropertyTypeModel;
use App\Models\DetailPropertyTypeModel;
use App\Models\PropertyImagesModel;
use App\Models\UserModel;


use CodeIgniter\RESTful\ResourceController;


class Property extends ResourceController
{
    protected $modelName = 'App\Models\PropertyModel';
    protected $format = 'json';

    private function genericResponse($data, $msj, $code)
    {
 
        if ($code == 200) {
            return $this->respond(array(
                'status' => 'ok',
                "data" => $data,
                "code" => $code
            )); //, 404, "No hay nada"
        } else {
            return $this->respond(array(
                'status' => 'error',
                "msj" => $msj,
                "code" => $code
            ));
 		}
	}

    public function index()
    {
        return $this->genericResponse($this->model->findAll(), NULL, 200);

    }

    // Function to insert a new property
    public function create()
    {
        try{
            // Load model PropertyTypeModel
            $propertyTypeModel = new PropertyTypeModel();
            $user = new UserModel();
            $detailProperty = new DetailPropertyTypeModel();
            $propertyImages = new PropertyImagesModel();
            

            // get data from request
            $property = $this->request->getPOST();

            
            // Validate userId exists
            $user = $user->where('id', $property['userId'])->first();
            if(!$user){
                return $this->genericResponse(NULL, "El usuario no existe", 400);
            }

            // Validate if PropertyType  

            if(!empty($property->type)){
                return $this->genericResponse(null,"Se debe incluir al menos un tipo de propiedad",400);
            }
            
            // TODO HERE IS NEED TO VALIDATE zipCode only 5 digits and only zipCode in León, Guanajuato, México
            if(!empty($property['zipCode'] && strlen($property['zipCode'] ) != 5)){
                return $this->genericResponse(null,"El código postal debe ser de 5 dígitos",400);
                
            } 

            
            // rules for images
            $rules = [
                'images' => 'uploaded[images]|max_size[images,1024]|is_image[images]|mime_in[images,image/jpg,image/jpeg,image/png]'
            ];
            
            if($this->validate($rules)){
                $files = $this->request->getFiles();
                
                foreach($files['images'] as $file){
                    // change name of file
                    $name = $file->getRandomName();
                    if($file->isValid() && !$file->hasMoved()){
                        if($file->move(ROOTPATH. '/public/uploads/properties', $name)){
                            $urls[] = base_url() . '/public/uploads/properties/' . $name;
                        }else{
                            return $this->genericResponse(null,"Error al subir la imagen",400);
                        }
                    }
                }
            }else{
                return $this->genericResponse(null,"Archivos incorrectos",400);
            }       
            
            // init db transaction
            $this->model->transStart();           

            // Insert property           

            $types = explode('|',$property['type']);
            unset($property['type']);
            $propertyId = $this->model->insert($property);
            // Insert property type
            foreach($types as $type){
                $propertyTypeModel->insert([
                    'type' => $type
                ]);
                $detailProperty->insert([
                    'propertyId' => $propertyId,
                    'propertyTypeId' => $propertyTypeModel->insertID()
                ]);                                              
            }                   
            // Insert property images
            foreach($urls as $url){
                $propertyImages->insert([
                    'urlImg' => $url,
                    'propertyId' => $propertyId
                ]);
            }            

            // commit transaction
            $this->model->transComplete();
            return $this->genericResponse($propertyId, NULL, 200);
        }catch(Exception $e){
            // rollback transaction
            $this->model->transRollback();
            

          

            $message=$e->getMessage();
            return $this->genericResponse(NULL, $message, 400);
        }
    }


    // Function to consult a property by id
    public function getid()
    {
        try{
            $propertyId = $this->request->getPOST('id');
            $images = new PropertyImagesModel();
            $detailProperty = new DetailPropertyTypeModel();
            $propertyType = new PropertyTypeModel();
            $property = $this->model->find($propertyId);
            $property['images'] = $images->where('propertyId',$propertyId)->findAll();
            $types = $detailProperty->where('propertyId',$propertyId)->findAll();
            
            $property['types'] = array();
            foreach($types as $type){
                $property['types'][] = $propertyType->find($type['propertyTypeId'])['type'];
            }
                    
            if($property){
                return $this->genericResponse($property, NULL, 200);
            }else{
                return $this->genericResponse(NULL, "No existe la propiedad", 400);
            }
        }catch(Exception $e){
            $message=$e->getMessage();
            return $this->genericResponse(NULL, $message, 400);
        }
    }

    // Function to get all properties
    public function getall()
    {
        try{
            $properties = $this->model->findAll();
            $images = new PropertyImagesModel();
            $detailProperty = new DetailPropertyTypeModel();
            $propertyType = new PropertyTypeModel();
            foreach($properties as &$property){
                $property['images'] = $images->where('propertyId',$property['id'])->findAll();
                $types = $detailProperty->where('propertyId',$property['id'])->findAll();
                
                $property['types'] = array();
                foreach($types as $type){
                    $property['types'][] = $propertyType->find($type['propertyTypeId'])['type'];
                }
            }
            return $this->genericResponse($properties, NULL, 200);
        }catch(Exception $e){
            $message=$e->getMessage();
            return $this->genericResponse(NULL, $message, 400);
        }
    }

    // Function to update data of a property
    public function updatedata()
    {
        
        try 
        {
            $detailProperty = new DetailPropertyTypeModel();
            $propertyTypeModel = new PropertyTypeModel();
            $property = $this->request->getJSON();
            
            $propertyId = $property->id;

            $p = $this->model->find($propertyId);


            if($p){

                // Validate if json request has type
                if(empty($property->types)){
                    $this->model->update($propertyId, $property);
                    return $this->genericResponse($property, NULL, 200);                    
                }
                // init transaction
                $this->model->transStart();           


                // Get types of property
                $types = explode('|',$property->types);
                unset($property->types);
                // Get propertyTypes ids
                $propertyTypes = $detailProperty->where('propertyId',$propertyId)->findAll();
                $propertyTypesIds = array();

                $i = 0;
                foreach($propertyTypes as $propertyType){
                    $propertyTypeModel->update($propertyType['propertyTypeId'], ['type' => $types[$i]]);
                    $i++;
                }

                $this->model->update($propertyId, $property);
                $property->types = $types;
                
                // commit transaction
                $this->model->transComplete();

                return $this->genericResponse($property, NULL, 200);      
                
            }else{
                return $this->genericResponse(NULL, "No existe la propiedad", 400);
            }

    
        }catch(Exception $e){
            // rollback transaction
            $this->model->transRollback();
            $message=$e->getMessage();
            return $this->genericResponse(NULL, $message, 400);
        }
    }
    
    // function to update files of a property
    public function updatefiles()
    {
        try{
            $propertyImages = new PropertyImagesModel();
            $propertyId = $this->request->getPOST('id');
            $urlList = $this->request->getPOST('urlList');
            $p = $this->model->find($propertyId);
            $urls = array();
            if($p){
                // rules for images
                $rules = [
                    'images' => 'uploaded[images]|max_size[images,1024]|is_image[images]|mime_in[images,image/jpg,image/jpeg,image/png]'
                ];
                
                if($this->validate($rules)){
                    $files = $this->request->getFiles();
                    
                    foreach($files['images'] as $file){
                        // change name of file
                        $name = $file->getRandomName();
                        if($file->isValid() && !$file->hasMoved()){
                            if($file->move(ROOTPATH. '/public/uploads/properties', $name)){
                                $urls[] = base_url() . '/public/uploads/properties/' . $name;
                            }else{
                                return $this->genericResponse(null,"Error al subir la imagen",400);
                            }
                        }
                    }
                }else{
                    return $this->genericResponse(null,"Archivos incorrectos",400);
                }       
                
                // init db transaction
                $this->model->transStart();

                $urlList = explode('|',$urlList);

            

                // commit transaction
                $this->model->transComplete();
                return $this->genericResponse($propertyId, NULL, 200);
            }else{
                return $this->genericResponse(NULL, "No existe la propiedad", 400);
            }
        }catch(Exception $e){
            // rollback transaction
            $this->model->transRollback();
            

          

            $message=$e->getMessage();
            return $this->genericResponse(NULL, $message, 400);
        }
    }


    // Function to get properties by user
    public function getbyuser()
    {
        try{
            $userId = $this->request->getPOST('userId');
            $properties = $this->model->where('userId',$userId)->findAll();
            $images = new PropertyImagesModel();
            $detailProperty = new DetailPropertyTypeModel();
            $propertyType = new PropertyTypeModel();
            foreach($properties as &$property){
                $property['images'] = $images->where('propertyId',$property['id'])->findAll();
                $types = $detailProperty->where('propertyId',$property['id'])->findAll();
                
                $property['types'] = array();
                foreach($types as $type){
                    $property['types'][] = $propertyType->find($type['propertyTypeId'])['type'];
                }
            }
            return $this->genericResponse($properties, NULL, 200);
        }catch(Exception $e){
            $message=$e->getMessage();
            return $this->genericResponse(NULL, $message, 400);
        }
    }


    

    


}

