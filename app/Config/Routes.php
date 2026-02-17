<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Route par défaut - redirige vers login si non connecté
$routes->get('/', 'Home::index');

// Routes Signup
$routes->group('signup', function($routes) {
    $routes->get('/', 'Signup::index');
    $routes->post('/', 'Signup::index');
    $routes->get('login', 'Signup::login');
    $routes->post('login', 'Signup::login');
    $routes->get('logout', 'Signup::logout');
    $routes->get('membres', 'Signup::membres');
    $routes->get('reset_password', 'Signup::reset_password');
    $routes->post('reset_password', 'Signup::reset_password');
});

// Routes Admin (back-office)
$routes->get('admin', 'Admin\Dashboard::index');
$routes->get('admin/dashboard', 'Admin\Dashboard::index');
$routes->get('admin/connected', 'Admin\Connected::index');

// Routes Membres
$routes->get('membres', 'Membres::index');
$routes->get('membres/mon_compte', 'Membres::mon_compte');

// Routes autres
$routes->get('envoi_password', 'Envoi_password::index');
$routes->post('envoi_password', 'Envoi_password::index');
$routes->get('modification_password', 'Modification_password::index');
$routes->post('modification_password', 'Modification_password::index');
$routes->get('mon_compte', 'Mon_compte::index');
$routes->post('mon_compte', 'Mon_compte::index');
$routes->get('mes_documents', 'Mes_documents::index');
