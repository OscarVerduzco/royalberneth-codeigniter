<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class CancelationModel extends Model
{
    protected $table = 'cancelation';
    protected $primaryKey = 'id';
    protected $allowedFields = ['reservationId',
                                'cancelationReason',
                                'cancelationDate',
                                'transactionId',
                                'message',
                                'authcode',
                                'merchantDate',
                                'request',
                                'response',
                                'status'];
                                
                            
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}