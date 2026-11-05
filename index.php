<?php
require __DIR__ . '/includes/bootstrap.php';

$routes = [
    'login'            => 'pages/login.php',
    'logout'           => 'pages/logout.php',
    'dashboard'        => 'pages/dashboard.php',
    'masuk'            => 'pages/masuk/index.php',
    'masuk.form'       => 'pages/masuk/form.php',
    'masuk.detail'     => 'pages/masuk/detail.php',
    'masuk.hapus'      => 'pages/masuk/hapus.php',
    'masuk.lembar'     => 'pages/masuk/lembar.php',
    'keluar'           => 'pages/keluar/index.php',
    'keluar.form'      => 'pages/keluar/form.php',
    'keluar.hapus'     => 'pages/keluar/hapus.php',
    'disposisi'        => 'pages/disposisi/index.php',
    'disposisi.simpan' => 'pages/disposisi/simpan.php',
    'disposisi.respon' => 'pages/disposisi/respon.php',
    'agenda'           => 'pages/agenda.php',
    'master'           => 'pages/master/index.php',
    'berkas'           => 'pages/berkas.php',
];

$page = $_GET['page'] ?? 'dashboard';
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'dashboard';
}
if ($page !== 'login') require_login();
verify_csrf();

require __DIR__ . '/' . $routes[$page];
