<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class FacturaCompraModel extends Model
{
    protected $table = 'facturaCompra';
    protected $primaryKey = 'idFacturaCompra';
    protected $allowedFields = ['numeroFactura', 'fotoFactura','fechaFactura','idProveedor', 'estatusFacturaCompra'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}