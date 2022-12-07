<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PaymentModel;


class Property extends ResourceController
{
    protected $modelName = 'App\Models\PaymentModel';
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

    // Create a function to register a payment
    // Uses the api from stripe
    // https://stripe.com/docs/api/charges/create
    // 

    public function create()
    {
        $data = $this->request->getJSON();
        //  load Stripe library
        $this->load->library('stripe');

        //  create token
        $result = $this->stripe->charge($data, $params);

        


    }

}
