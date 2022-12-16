<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;

class Cancelation extends ResourceController
{

    protected $modelName = 'App\Models\CancelationModel';
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
        $data = $this->model->findAll();
        return $this->genericResponse($data, "ok", 200);
    }

    

}