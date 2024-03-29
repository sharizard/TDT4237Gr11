<?php
 require_once __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\Slim([
    'templates.path' => __DIR__.'/webapp/templates/',
    'debug' => false,
    'view' => new \Slim\Views\Twig()
]);

$view = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

    // Creates an instance to send emails
//    $app->mail = new PHPMailer;

try {
    // Create (connect to) SQLite database in file
    $app->db = new PDO('sqlite:app.db');
    // Set errormode to exceptions
    $app->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo $e->getMessage();
    exit();
}

chmod(__DIR__ . '/../web/images/avatars', 0700);

$ns ='tdt4237\\webapp\\controllers\\'; 

// Home page at http://localhost/
$app->get('/', $ns . 'IndexController:index');

// Login form
$app->get('/login', $ns . 'LoginController:index');
$app->post('/login', $ns . 'LoginController:login');

// Recover password form
$app->get('/login/recover', $ns . 'LoginController:recover')->name('recover');
$app->post('/login/recover', $ns . 'LoginController:recover');

// Set password
$app->get('/login/setpass', $ns . 'LoginController:setpassword')->name('setpassword');

// New user
$app->get('/user/new', $ns . 'UserController:index')->name('newuser');
$app->post('/user/new', $ns . 'UserController:create');

// Edit logged in user
$app->get('/user/edit', $ns . 'UserController:edit')->name('editprofile');
$app->post('/user/edit', $ns . 'UserController:edit');

// Show a user by name
$app->get('/user/:username', $ns . 'UserController:show')->name('showuser');

// Show all users
$app->get('/users', $ns . 'UserController:all');

// Log out
$app->post('/', $ns . 'UserController:logout');

// Admin restricted area
$app->get('/admin', $ns . 'AdminController:index')->name('admin');
$app->post('/admin/delete/:username', $ns . 'AdminController:delete');

// Movies
$app->get('/movies', $ns . 'MovieController:index')->name('movies');
$app->get('/movies/:movieid', $ns . 'MovieController:show');
$app->post('/movies/:movieid', $ns . 'MovieController:addReview');

return $app;
