<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ReservationModel;


class Reservation extends ResourceController
{
    protected $modelName = 'App\Models\ReservationModel';
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


    // Function to create a reservation
    public function create()
    {
        try {
            $data = $this->request->getJSON();
            

            
            
        } catch (Exception $th) {
            
        }

    }
}
