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
    $routes->get('associer_cle', 'Signup::associer_cle');
    $routes->post('associer_cle', 'Signup::associer_cle');
    $routes->get('logout', 'Signup::logout');
    $routes->get('membres', 'Signup::membres');
    $routes->get('reset_password', 'Signup::reset_password');
    $routes->post('reset_password', 'Signup::reset_password');
});

// Routes Admin (back-office)
$routes->get('admin', 'Admin\Dashboard::index');
$routes->get('admin/dashboard', 'Admin\Dashboard::index');
$routes->get('admin/connected', 'Admin\Connected::index');
$routes->get('admin/login-notices', 'Admin\LoginNotices::index');
$routes->get('admin/login-notices/add', 'Admin\LoginNotices::add');
$routes->get('admin/login-notices/edit/(:num)', 'Admin\LoginNotices::edit/$1');
$routes->post('admin/login-notices/save', 'Admin\LoginNotices::save');
$routes->post('admin/login-notices/save-config', 'Admin\LoginNotices::saveConfig');
$routes->get('admin/login-notices/delete/(:num)', 'Admin\LoginNotices::delete/$1');
$routes->get('admin/deploy', 'Admin\Deploy::index');
$routes->post('admin/deploy/run', 'Admin\Deploy::run');
$routes->post('admin/deploy/file', 'Admin\Deploy::runFile');

// Routes Membres
$routes->get('membres', 'Membres::index');
$routes->get('membres/mon_compte', 'Membres::mon_compte');
$routes->get('membres/carte-ecran2', 'Membres::carteEcran2');

// Routes autres
$routes->get('envoi_password', 'Envoi_password::index');
$routes->post('envoi_password', 'Envoi_password::index');
$routes->get('modification_password', 'Modification_password::index');
$routes->post('modification_password', 'Modification_password::index');
$routes->get('mon_compte', 'Mon_compte::index');
$routes->post('mon_compte', 'Mon_compte::index');
$routes->get('mes_documents', 'Mes_documents::index');
