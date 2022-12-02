<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class ProveedorModel extends Model
{
    protected $table = 'proveedor';
    protected $primaryKey = 'idProveedor';
    protected $allowedFields = ['nombreProveedor', 'nombreContacto','telefonoProveedor','telefonoContacto','correoProveedor'
                                ,'correoContacto','estatusProveedor'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}