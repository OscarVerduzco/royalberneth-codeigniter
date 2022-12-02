<?php
namespace App\Models;
 
use CodeIgniter\Model;
 
class ProductoModel extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'idProducto';
    protected $allowedFields = ['nombreProducto', 'numeroParte','marca','foto','descripcion','costoCompra','codigo'
                                ,'precioVenta','posicion','unidad','stock','estatusProducto','idFacturaCompra',
                                'idProveedor'];
    
    protected $useTimeStamps = true;
    
    protected $createdFields = 'created_at';
    protected $updateFields = 'updated_at';
}