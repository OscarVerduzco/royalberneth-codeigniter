<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password','name','lastname', 'email', 'phone', 'address', 'city','state','zipCode','country','status', 'apiToken', 'type'];
    
}