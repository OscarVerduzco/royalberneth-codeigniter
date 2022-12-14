<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class PaymentModel extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $allowedFields = ['date',
                                'amount',
                                'reservationId',
                                'typePayment', 
                                'transactionId', 
                                'gateway',
                                'receiptUrl',
                                'status'

                            ];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}