<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class PropertyImagesModel extends Model
{
    protected $table = 'propertyImages';
    protected $primaryKey = 'id';
    protected $allowedFields = ['propertyId','urlImg'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}