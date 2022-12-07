<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class PropertyModel extends Model
{
    protected $table = 'property';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','description','price','zipCode','address','city',
                                'country','userId','status', 'limitPeople','startTime','endTime'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}