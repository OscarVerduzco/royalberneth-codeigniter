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
                if ($user['password'] == $data->password) {
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
    public function register(){
        $data = $this->request->getJSON();
        $user = $this->model->where('username', $data['username'])->first();
        if ($user) {
            return $this->genericResponse(NULL, "User already exists", 400);
        } else {
            $this->model->save($data);
            return $this->genericResponse($this->model->where('username', $data['username'])->first(), NULL, 200);
        }
    }

    // Function to update user data
    public function update($id = null)
    {
        $data = $this->request->getJSON();
        $user = $this->model->where('id', $id)->first();
        if ($user) {
            $this->model->set($data)->where('id', $id)->update();
            return $this->genericResponse($this->model->where('id', $id)->first(), NULL, 200);
        } else {
            return $this->genericResponse(NULL, "User not found", 400);
        }
    }

   

}