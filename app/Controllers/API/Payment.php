<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PaymentModel;
use App\Models\ReservationModel;
use App\Models\CancelationModel;
use App\Models\UserModel;
use App\Models\PropertyModel;


class Payment extends ResourceController
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

    public function index()
    {
        $data = $this->model->findAll();
        return $this->genericResponse($data, "ok", 200);
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

    // function to get earnings of a property owner
    public function getOwnerEarnings()
    {
        $userId = $this->request->getPOST('userId');

        // GET PROPERTIES OF OWNER
        $propertyModel = new PropertyModel();
        $properties = $propertyModel->where('userId', $userId)->findAll();

       

        // GET RESERVATIONS OF PROPERTIES
        $reservationModel = new ReservationModel();
        $reservations =[];
        foreach ($properties as $property) {
            $reservations = $reservationModel->where('propertyId', $property['id'])->where('status',1)->findAll();
        }

        // GET PAYMENTS OF RESERVATIONS
        $paymentModel = new PaymentModel();
        $payments = [];
        foreach ($reservations as $reservation) {
            $payments = $paymentModel->where('reservationId', $reservation['id'])->findAll();
        }
        
        
        // GET TOTAL EARNINGS OF PAYMENTS THAT DATE IS ALREADY PASSED
        $earningsPaid = 0;
        foreach ($payments as $payment) {
            if (strtotime($payment['date']) < strtotime(date('Y-m-d'))) {
                $earningsPaid += $payment['amount'];
            }
        }

        // echo json_encode($payments);
        // die;

        // GET TOTAL EARNINGS OF PAYMENTS THAT DATE IS NOT PASSED
        $earningsNotPaid = 0;
        foreach ($payments as $payment) {
            if (strtotime($reservation['dateStart']) > strtotime(date('Y-m-d'))) {
                $earningsNotPaid += $payment['amount'];
            }
        }

        $data = [
            // Discount 15% of the total earnings
            'earningsPaid' => $earningsPaid * 0.85,
            'earningsNotPaid' => $earningsNotPaid * 0.85
        ];

        return $this->genericResponse($data, "ok", 200);

    }

    // Function to get total earnings of all payments of reservations
    public function getTotalEarnings()
    {
        $paymentModel = new PaymentModel();
        $reservationModel = new ReservationModel();
        $cancelationModel = new CancelationModel();

        $reservations = $reservationModel->where('status', 1)->findAll();
        $cancelations = $cancelationModel->findAll();
        
        $payments = [];
        $countReservations = 0;
        foreach ($reservations as $reservation) {
            
            $payment = $paymentModel->where('reservationId', $reservation['id'])->first();
            if ($payment) {
                $payment['date'] = $reservation['dateStart'];
                array_push($payments, $payment);
                $payment = [];
                $countReservations++;
            }
            
        }



        

        $earnings = 0;
        $earningsStandBy = 0;
        
        foreach ($payments as $payment) {
            // Check if the payment date is already passed
            if (strtotime($payment['date']) < strtotime(date('Y-m-d'))) {
                $earnings += $payment['amount'];
                
                
            }else{
                
                
                $earningsStandBy += $payment['amount'];
            }
        }

        $data = [
            'earningsUsers' => $earnings * 0.85,
            'earningsAdmin' => $earnings * 0.15,
            'total' => $earnings + $earningsStandBy,
            'earningsStandBy' => $earningsStandBy * 0.85,
            'earningsStandByAdmin' => $earningsStandBy * 0.15,
            'countReservations' => $countReservations,
            'countCancelations' => count($cancelations) 
        ];

        return $this->genericResponse($data, "ok", 200);
    }

}
