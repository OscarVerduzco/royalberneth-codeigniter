<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->group('api',['namespace' => 'App\Controllers\API'], function($routes) {
    /*RUTAS DE PROVEEDOR */
    $routes->get('proveedor', 'Proveedor::index');
    $routes->get('proveedor/getAll', 'Proveedor::getAll');
    $routes->post('proveedor/updateProveedor', 'Proveedor::updateProveedor');
    $routes->post('proveedor/create', 'Proveedor::create');
    $routes->post('proveedor/updateEstatusE', 'Proveedor::updateEstatusE');
    $routes->post('proveedor/updateEstatusA', 'Proveedor::updateEstatusA');
    $routes->get('proveedor/getAllDel', 'Proveedor::getAllDel');
    $routes->post('proveedor/searchBy', 'Proveedor::searchBy');
    
    /*RUTAS DE FACTURA COMPRA */
    $routes->get('facturaCompra', 'FacturaCompra::index');
    $routes->get('facturaCompra/getAll', 'FacturaCompra::getAll');
    $routes->post('facturaCompra/updateFacturaCompra', 'FacturaCompra::updateFacturaCompra');
    $routes->post('facturaCompra/updateFoto', 'FacturaCompra::updateFoto');
    $routes->post('facturaCompra/create', 'FacturaCompra::create');
    $routes->post('facturaCompra/updateEstatusE', 'FacturaCompra::updateEstatusE');
    $routes->post('facturaCompra/updateEstatusA', 'FacturaCompra::updateEstatusA');
    $routes->get('facturaCompra/getAllDel', 'FacturaCompra::getAllDel');
    $routes->post('facturaCompra/searchBy', 'FacturaCompra::searchBy');
    
    /*RUTAS DE PRODUCTO */
    $routes->get('producto', 'Producto::index');
    $routes->get('producto/getAll', 'Producto::getAll');
    $routes->post('producto/updateProducto', 'Producto::updateProducto');
    $routes->post('producto/create', 'Producto::create');
    $routes->post('producto/updateEstatusE', 'Producto::updateEstatusE');
    $routes->post('producto/updateEstatusA', 'Producto::updateEstatusA');
    $routes->get('producto/getAllDel', 'Producto::getAllDel');
    $routes->post('producto/updateFoto', 'Producto::updateFoto');
    $routes->post('producto/searchBy', 'Producto::searchBy');
    $routes->post('producto/agregarStock', 'Producto::agregarStock');
    
    /*Rutas Login*/
    $routes->post('user/login', 'User::login');

    $routes->get('user', 'User::index');
    
});


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 * */
 
if(file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}