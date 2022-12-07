<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class DetailPropertyTypeModel extends Model
{
    protected $table = 'detailPropertyType';
    protected $primaryKey = 'id';
    protected $allowedFields = ['propertyId','propertyTypeId'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}