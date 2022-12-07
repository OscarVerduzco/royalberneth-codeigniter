<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class PropertyTypeModel extends Model
{
    protected $table = 'propertyType';
    protected $primaryKey = 'id';
    protected $allowedFields = ['type'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}