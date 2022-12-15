<?php

namespace App\Libraries;

class StripeLib
{
    private $stripe_id = null;
	private $public_key = null; //placeholders
	private $private_key = null; //placeholders
	private $mode = null;
    
    public function __construct()
    {
        helper(['url']);
        $this->stripe_account = 'acct_1Hq2ZpJZ2Z2Z2Z2Z';
        $this->private_key = 'sk_test_51MBrncI2BmcpI70CyM3m0pgSt8CdDFuNaHI22Gjiyv7nulWNJ7CYlNeEgRLeuqwyV67uVQAoDjGzxF0YJrTwTT4E00uApMiII0';
        $this->public_key = 'pk_test_51MBrncI2BmcpI70CPTPDYTxRjZh1CYwbcy8TSWJi6b7qFwlBHLvD8x4zYSVYoxve5U3yjBPqbn46qFyrp0MRL38700TgYVSszc';
    }
    // Token stripe
    public function create_token($data)
	{
		try {
		

			$stripe = new \Stripe\StripeClient(
				$this->private_key
			);

			$card_data =   [
                        'number' => $data['number'],
                        'exp_month' => $data['month'],
                        'exp_year' => $data['year'],
                        'cvc' => $data['cvc'],
                        'name' => $data['name']
                ];

			$data_string = json_encode($card_data);


			$data = $stripe->tokens->create([
				'card' => $card_data,
			]);

			if(isset($data['id']))
			{
				return $data['id']; // token
			}
		} catch (Exception $e) {
			log_message('DEBUG', 'Stripe: ' . $e->getMessage());
			return null;
		}

		return null;
	}
	//Stripe charge 
    public function charge($data)
    {
        
		$result = array();
		$result['status_code'] = -1;
		$result['auth_code'] = null;
		$result['message'] = 'Error en procesamiento';
		$result['merchant_date'] = null;
		$result['mode'] = $this->mode;
		$result['merchant'] = 'Stripe-Card';

		// Parse data to array
		$data = (array) $data;


        if(!array_key_exists('phone', $data)) { $data['phone'] = '+525551111111'; }
		if(!array_key_exists('street1', $data)) { $data['street1'] = 'No aplicable'; }
		if(!array_key_exists('city', $data)) { $data['city'] = 'NA'; }
		if(!array_key_exists('state', $data)) { $data['state'] = 'NA'; }
		if(!array_key_exists('country', $data)) { $data['country'] = 'MX'; }
		if(!array_key_exists('zip', $data)) { $data['zip'] = '01000'; }
		if(!array_key_exists('description', $data)) { $data['description'] = 'No aplicable'; }

        try
        {
			$token_id = $this->create_token($data);
			$metadata['gateway'] = 'stripe';
            
            if($token_id == null)
			{
				$result['message'] = "Error al validar datos de tarjeta.";
				return $result;
			}

            $amount = number_format($data['total'],2,'.','') * 100;

			$shipping = [
                'address' => [
                    'line1' => 	$data['street1'],
                    'city' => 	$data['city'],
                    'country' => 	$data['country'],
                    'postal_code' => 	$data['zip'],
                    'state' => 	$data['state']
                ],
                'name' => $data['name'],
                'phone' => $data['phone']
            ];

            $chargeRequest = 	[
                'amount' => $amount,
                'currency' => 'mxn',
                'source' => $token_id,
                'description' => $data['description'],
                'shipping' => $shipping,
            ];

            $idempotencyRequest = [];
			// Create a idempotency key to avoid duplicate charges
			$data['idempotency'] = uniqid();			

			if(isset($data['idempotency']))
				$idempotencyRequest = ['idempotency_key' => $data['idempotency']];

			$stripe = new \Stripe\StripeClient(
				$this->private_key
			);

			$order = $stripe->charges->create($chargeRequest, $idempotencyRequest);

			if($order->id)
			{
				//authorización selectiva (3d secure)
				if($order->status == 'succeeded')
				{
					$result['transaction_id'] = $order->id;
					$result['status_code'] = 1;
					$result['message'] = "Pago completado con éxito";
					$result['metadata'] = json_encode($metadata);
					//$result['merchant_date'] = $charge->???;

					if(isset($order->transfer) && !empty($order->transfer))
						$result['transfer'] = $order->transfer;
					if(isset($order->transfer_group) && !empty($order->transfer_group))
						$result['transfer_group'] = $order->transfer_group;
				}

				$result['auth_code'] = $order->id;
				$result['merchant_date'] = date('Y-m-d H:i:s', $order->created);//date('Y-m-d H:i:s');
				$result['body_sent'] = $chargeRequest;
				$result['body_result'] = $order;

			}         


            

        }catch(Exception $e)
        {
			$result = array();
			$result['status_code'] = -1;
			$result['auth_code'] = null;
			$result['message'] = 'Error en procesamiento';
			$result['merchant_date'] = null;
			$result['mode'] = $this->mode;
			$result['merchant'] = 'Stripe-Card';
			$result['body_sent'] = $chargeRequest;
			$result['body_result'] = $e->getMessage();
			
        }

		return $result;

    }

	public function refund($data)
	{
		$result = array();
		$result['status_code'] = -1;
		$result['auth_code'] = null;
		$result['message'] = 'Error en procesamiento';
		$result['merchant_date'] = null;
		$result['mode'] = $this->mode;
		$result['merchant'] = 'Stripe-Card';

		// Parse data to array
		$data = (array) $data;

		try
		{
			$stripe = new \Stripe\StripeClient(
				$this->private_key
			);

			$refund = $stripe->refunds->create([
				'charge' => $data['transaction_id'],
				'reason' => 'requested_by_customer',
				'metadata' => ['gateway' => 'stripe'],
			]);

			if($refund->id)
			{
				$result['transaction_id'] = $refund->id;
				$result['status_code'] = 1;
				$result['message'] = "Reembolso completado con éxito";
				$result['auth_code'] = $refund->id;
				$result['merchant_date'] = date('Y-m-d H:i:s', $refund->created);//date('Y-m-d H:i:s');
				$result['body_sent'] = $data;
				$result['body_result'] = $refund;
			}
		} catch (Exception $e) {
			log_message('DEBUG', 'Stripe: ' . $e->getMessage());
			$result['body_result'] = $e->getMessage();
		}

		return $result;
	}

	

}