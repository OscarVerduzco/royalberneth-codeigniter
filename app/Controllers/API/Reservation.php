<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ReservationModel;
use App\Libraries\StripeLib;
Use App\Models\PaymentModel;
// import property controller
use App\Controllers\API\Property;
use App\Models\PropertyModel;
use App\Models\PropertyTypeModel;
use App\Models\DetailPropertyTypeModel;
use App\Models\PropertyImagesModel;
use App\Models\UserModel;


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
                "msj" => $msj,
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

    // index
    public function index()
    {
        return $this->genericResponse($this->model->findAll(), NULL, 200);
    }

    //Get all reservation
    public function getall()
    {
        try {
            $reservations = $this->model->findAll();
            // Get name of user
            $userModel = new UserModel();
            $propertyModel = new PropertyModel();
            
            foreach ($reservations as $key => $value) {
                $user = $userModel->where('id', $value['userId'])->first();
                $property = $propertyModel->where('id', $value['propertyId'])->first();
                $reservations[$key]['userName'] = $user['name'];
                $reservations[$key]['propertyName'] = $property['name'];
            }
            return $this->genericResponse($reservations, NULL, 200);
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error getting reservations", 500);
        }
    }


    // Function to create a reservation
    public function create()
    {
        try {
            // Get the data from the request
            $data = $this->request->getJSON();
            
            // Check if exists pre-reservation
            $r = $this->model->where('id', $data->reservationId)->first();
            ($r == null) ? $this->genericResponse(null, "pre reservation not found", 404) : null;

            // check reservation status
            if($r['status'] == 1){
                return $this->genericResponse(null, "Esta reservacion ya ha sido pagada", 500);
            }


            $stripe = new StripeLib();
            $token = $stripe->charge($data);  
           

            if ($token != null) {
                $data->token = $token;
                $data->status = 1;                

                $data->dateReservation = date('Y-m-d H:i:s');
                $data->status = 1;

                // Update pre-reservation
                $this->model->update($data->reservationId, $data);
                // Register the payment
                $payment = [
                    'reservationId' => $data->reservationId,
                    'date' => date('Y-m-d H:i:s'),
                    'amount' => $data->total,
                    'transactionId' => $token['transaction_id'],
                    'typePayment' => "card",
                    'gateway' => "stripe",
                    'receiptUrl' => $token['body_result']['receipt_url'],                    
                    'status' => 1
                ];

                $paymentModel = new PaymentModel();
                $paymentModel->insert($payment);              
                return $this->genericResponse(null, $token['message'] . ", Se a ha guardado la reservacion!", 200);
            } else {
                
                return $this->genericResponse(null, "Error", 500);
            }
            

            
            
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error creating reservation", 500);           
            
        }

    }

    // Check availability of a property
    public function checkavailability()
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
                'userId' => $data->userId,
                'dateReservation' => date('Y-m-d H:i:s'),
                'totalPrice' => 0,
                'status' => 3 // 3 = pre-reservation
            ];
            
            // Save the reservation and return the id to pay
            $id = $this->model->insert($reservation);
            // Return the id of the pre-reservation


        return $this->genericResponse($id, "Pre-reservation created", 200);

        }catch(Exception $e){
            return $this->genericResponse(null, "Error creating pre-reservation", 500);
        }      


    }

    // Function to get the reservations of a user
    public function getReservationsByUser()
    {
        try {
            $propertyController = new Property();
            
            $id = $this->request->getPOST('userId');
            $reservations = $this->model->where('userId', $id)->findAll();
            // add property info
            

            foreach ($reservations as $key => $value) {
               
                $property = $this->getProperty($value['propertyId']);
                
                $reservations[$key]['property'] = $property;
            }

            return $this->genericResponse($reservations, "Reservations found", 200);
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error getting reservations", 500);
        }
    }

 




    // Function to get dates reserved of a property
    public function getReservationsByProperty()
    {
        try {
            $id = $this->request->getPOST('propertyId');
            $reservations = $this->model->where('propertyId', $id)->findAll();
            return $this->genericResponse($reservations, "Dates reserved found", 200);
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error getting dates reserved", 500);
        }
    }

    public function getProperty($propertyId)
    {
        
        try{
            
            $pro = new PropertyModel();
            $images = new PropertyImagesModel();
            $detailProperty = new DetailPropertyTypeModel();
            $propertyType = new PropertyTypeModel();
            $property = $pro->find(intval($propertyId));
            $property['images'] = $images->where('propertyId',$propertyId)->findAll();
            $types = $detailProperty->where('propertyId',$propertyId)->findAll();
            
            $property['types'] = array();
            foreach($types as $type){
                $property['types'][] = $propertyType->find($type['propertyTypeId'])['type'];
            }
                    
            if($property){
                
                return $property;
            }else{
                return NULL;
            }
        }catch(Exception $e){
            $message=$e->getMessage();
            return $e.getMessage();
        }
    }

    // Function to get the reservations of a property
    public function getReservationsByPropertyId()
    {
        try {
            $id = $this->request->getPOST('propertyId');
            $reservations = $this->model->where('propertyId', $id)->findAll();
            return $this->genericResponse($reservations, "Reservations found", 200);
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error getting reservations", 500);
        }
    }

    // Function to get the reservations by user owner of a property
    public function getReservationsByUserOwner()
    {
        try {
            $ownerId = $this->request->getPOST('userId');
            $propertyModel = new PropertyModel();
            $user  = new UserModel();
            $payment = new PaymentModel();
            $reservations = [];
            $properties = $propertyModel->where('userId', $ownerId)->findAll();

            // Create query that joins the reservations with the properties of the user
            foreach ($properties as $key => $value) {
                $reservations = array_merge($reservations, $this->model->where('propertyId', $value['id'])->findAll());
                
            }

            foreach ($reservations as $key => $value) {
                $u = $user->find($value['userId']);
                $reservations[$key]['user']['name'] = $u['name']." ".$u['lastname'];
                $reservations[$key]['user']['email'] = $u['email'];
                $reservations[$key]['user']['phone'] = $u['phone'];

                $reservations[$key]['property'] = $propertyModel->find($value['propertyId'])['name'];
                $reservations[$key]['payment'] = $payment->where('reservationId', $value['id'])->first();
            }

            return $this->genericResponse($reservations, "Reservations found", 200);
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error getting reservations", 500);
        }
    }


    // Function to get reservation by id
    public function getReservationById()
    {
        try {
            $propertyModel = new PropertyModel();
            $user  = new UserModel();
            $payment = new PaymentModel();
            $id = $this->request->getPOST('reservationId');
            $reservation = $this->model->find($id);
            if(!$reservation){
                return $this->genericResponse(null, "Reservation not found", 404);
            }
            $u = $user->find($reservation['userId']);
            $reservation['user']['name'] = $u['name']." ".$u['lastname'];
            $reservation['user']['email'] = $u['email'];
            $reservation['user']['phone'] = $u['phone'];

            $reservation['property'] = $this->getProperty($reservation['propertyId']);

            $reservation['payment'] = $payment->where('reservationId', $reservation['id'])->first();
            
            return $this->genericResponse($reservation, "Reservation found", 200);
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error getting reservation", 500);
        }
    }

    // Cancel a reservation
    public function cancel_reservation()
    {
        try {
            $id = $this->request->getPOST('reservationId');
            $payment = new PaymentModel();
            $reservation = $this->model->find($id);
            if(!$reservation){
                return $this->genericResponse(null, "Reservation not found", 404);
            }
        } catch (Exception $th) {
            return $this->genericResponse(null, "Error getting reservation", 500);
        }

            




    }


}

