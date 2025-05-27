<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

// Tambahkan routing khusus untuk folder uploads
$routes->get('uploads/(:any)', function ($filename) {
    // Tidak perlu menjalankan kode PHP disini, karena ini hanya untuk menghindari error routing
    // Gambar akan tetap diakses langsung dari folder public/uploads
    return false;
});

$routes->get('/', 'Berita::index');
$routes->get('/berita/preview/(:any)', 'Berita::preview/$1');

// API Routes
$routes->group('api', function ($routes) {
    // Berita routes
    $routes->get('berita', 'Api::getBerita');
    $routes->get('berita/(:num)', 'Api::getBeritaById/$1');
    $routes->post('berita', 'Api::createBerita');
    $routes->put('berita/(:num)', 'Api::updateBerita/$1');
    $routes->delete('berita/(:num)', 'Api::deleteBerita/$1');
    $routes->post('upload', 'Api::uploadGambar');

    // Notification routes
    $routes->get('notifications', 'Api::getNotifications');
    $routes->get('notifications/unread', 'Api::getUnreadNotifications');
    $routes->put('notifications/(:num)/read', 'Api::markNotificationAsRead/$1');
    $routes->put('notifications/read-all', 'Api::markAllNotificationsAsRead');
    $routes->post('device/register', 'Api::registerDeviceToken');
});

// Tambahkan header CORS untuk semua request API
$routes->options('(:any)', function () {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Content-Length: 0");
    header("Content-Type: text/plain");
    exit(0);
});
