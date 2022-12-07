<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ReservationModel;
use App\Libraries\StripeLib;
Use App\Models\PaymentModel;


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
            // Get the data from the request
            $data = $this->request->getJSON();
            
            // Check if exists pre-reservation
            // ($this->model->where('id', $data->id)->first() == null) ? $this->genericResponse(null, "pre reservation not found", 404) : null;


            $stripe = new StripeLib();
            $token = $stripe->charge($data);

            echo json_encode($token);
            die();

            if ($token != null) {
                $data->token = $token;
                $data->status = 1;

                if (!$this->validateAviability($data)) {
                    return $this->genericResponse(null, "Reservation already exists", 500);
                }

                $data->dateReservation = date('Y-m-d H:i:s');
                $data->status = 1;

                // Update pre-reservation
                $this->model->update($data->id, $data);


                // Register the payment
                $payment = [
                    'reservationId' => $data->id,
                    'date' => date('Y-m-d H:i:s'),
                    'amount' => $data->totalPrice,
                    'transactionId' => $token->transaction->id,
                    'typePayment' => "card",
                    'gateway' => "stripe",
                    'receiptUrl' => $token->transaction->receipt_url,                    
                    'status' => 1
                ];

                $paymentModel = new PaymentModel();
                $paymentModel->save($payment);              
                return $this->genericResponse($data, $token->message . ", Se a ha guardado la reservacion! congratulations, gg diff profes qlos", 200);
            } else {
                
                return $this->genericResponse(null, "Error", 500);
            }
            

            
            
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error creating reservation", 500);           
            
        }

    }

    // Check availability of a property
    public function checkAvailability()
    {
        try {
            // Get the data from the request 
            $data = $this->request->getJSON();              

            if (!$this->validateAviability($data)) {
                return $this->genericResponse(null, "Reservation already exists", 500);
            }
            

            return $this->genericResponse(null, "Date avialable", 200);

        } catch (Exception $th) {
            return $this->genericResponse(null, "Error creating reservation", 500);
        }
    }

    private function validateAviability($data)
    {
        $reservation = $this->model->where('propertyId', $data->propertyId)
            ->where('dateStart', $data->dateStart)            
            ->first();

        if($reservation != null){
            return false;
        }

        return true;

        
    }
    // Function to create a pre-reservation
    public function createPreReservation(){

        try{
            // Get the data from the request
            $data = $this->request->getJSON();
            // Check availability
            if (!$this->validateAviability($data)) {
                return $this->genericResponse(null, "Reservation already exists", 500);
            }

            // Create the reservation
            $reservation = [
                'propertyId' => $data->propertyId,
                'dateStart' => $data->dateStart,
                'dateEnd' => $data->dateEnd,
                'dateReservation' => date('Y-m-d H:i:s'),
                'totalPrice' => 0,
                'status' => 3 // 3 = pre-reservation
            ];
            
            // Save the reservation and return the id to pay
            $id = $this->model->save($reservation);

        return $this->genericResponse($id, "Pre-reservation created", 200);

        }catch(Exception $e){
            return $this->genericResponse(null, "Error creating pre-reservation", 500);
        }      


    }
}

