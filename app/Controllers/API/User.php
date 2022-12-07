<?php

namespace App\Controllers\API;
//import user
use App\Models\UserModel;

use CodeIgniter\RESTful\ResourceController;


class User extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
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
    
    //Function to login creates a token for the user and returns the complete user data 
    public function login()
    {
        try{

            $data = $this->request->getJSON();
            
            $user = $this->model->where('username', $data->username)->first();
            
            if ($user) {
                $verify_pass = password_verify($data->password, $user['password']);

                if ($user['password'] == $verify_pass) {
                    $token = bin2hex(random_bytes(64));
                    $this->model->set('apiToken', $token)->where('id', $user['id'])->update();
                    $user['apiToken'] = $token;
                    return $this->genericResponse($user, NULL, 200);
                } else {
                    return $this->genericResponse(NULL, "Wrong password", 400);
                }
            } else {
                return $this->genericResponse(NULL, "User not found", 400);
            }
        }catch(Exception $e){
            $message=$e->getMessage();
            return $this->genericResponse($message, "Error", 400);
        }
    }

    // Function to register a new user
    public function singin(){
        $data = $this->request->getJSON();
        $user = $this->model->where('username', $data->username)->first();
        if ($user) {
            return $this->genericResponse(NULL, "User already exists", 400);
        } else {
            // Encrypt password
            $data->password = password_hash($data->password, PASSWORD_DEFAULT);
            // Validate type 
            if(empty($data->type)){
                return $this->genericResponse(NULL, "User type is required", 400);
            }
            // Validate email
            if(empty($data->email)){
                return $this->genericResponse(NULL, "User email is required", 400);
            }
            // Check tipe of user
            if($data->type == "admin"){
                $data->type = 1;
            }
            else if($data->type == "client"){
                $data->type = 2;
            }
            else if($data->type == "owner"){
                $data->type = 3;
            }
            else{
                return $this->genericResponse(NULL, "User type is not valid", 400);
            }
            // TODO HERE IS NEED TO VALIDATE ZIP CODE ONLY ACCETED 5 DIGITS AND ONLY ZIP CODE FROM lEON GTO
            // Validate zip code
            if(empty($data->zipCode)){
                return $this->genericResponse(NULL, "User zip code is required", 400);
            }            
            $data->status = 1;
            
            $this->model->save($data);
            return $this->genericResponse($this->model->where('username', $data->username)->first(), NULL, 200);
        }
    }

    // Function to update user data
    public function updateuser()
    {
        $data = $this->request->getJSON();
        $user = $this->model->where('id', $data->id)->first();
        if ($user) {
            // validate user data 
            
            if(isset($data->type)){
                unset($data->type);
            }
            if(isset($data->status)){
                unset($data->status);
            }
            $data->password = password_hash($data->password, PASSWORD_DEFAULT);
            $data->type = $user['type'];

            // TODO HERE IS NEED TO VALIDATE ZIP CODE ONLY ACCETED 5 DIGITS AND ONLY ZIP CODE FROM lEON GTO           
              
            $this->model->update($data->id, $data);
            return $this->genericResponse($this->model->where('id', $data->id)->first(), NULL, 200);
        } else {
            return $this->genericResponse(NULL, "User not found", 400);
        }
    }

    // Function to delete user
    public function deleteuser()
    {
        $data = $this->request->getJSON();
        $user = $this->model->where('id', $data->id)->first();
        if ($user) {
            $this->model->update($data->id, array('status' => 0));
            return $this->genericResponse(NULL, "User deleted", 200);
        } else {
            return $this->genericResponse(NULL, "User not found", 400);
        }
    }

   

}