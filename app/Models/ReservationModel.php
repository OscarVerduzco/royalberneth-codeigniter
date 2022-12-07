<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class ReservationModel extends Model
{
    protected $table = 'reservation';
    protected $primaryKey = 'id';
    protected $allowedFields = ['propertyId',
                                'userId',
                                'dateStart',
                                'dateEnd',
                                'dateReservation',
                                'totalPrice',
                                'status'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}