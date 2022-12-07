<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class DetailOpinionPropertyModel extends Model
{
    protected $table = 'detailOpinionProperty';
    protected $primaryKey = 'id';
    protected $allowedFields = ['reservationId','opinion','grade','dateOpinion'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}