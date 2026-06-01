<?php

require_once "../config/config.php";

require_once "../app/core/Database.php";
require_once "../app/core/Router.php";

require_once "../app/helpers/Response.php";
require_once "../app/helpers/JWT.php";
require_once "../app/helpers/Encryption.php";

require_once "../app/middleware/JsonMiddleware.php";
require_once "../app/middleware/AuthMiddleware.php";
require_once "../app/middleware/CsrfMiddleware.php";

require_once "../app/models/User.php";
require_once "../app/models/Patient.php";

require_once "../app/controllers/AuthController.php";
require_once "../app/controllers/PatientController.php";

JsonMiddleware::handle();

$router = new Router();
// method path action
$router->add('POST', '/project/api/register', 'AuthController@register');

$router->add('POST', '/project/api/login', 'AuthController@login');
$router->add('POST', '/project/api/token/refresh', 'AuthController@refresh');
$router->add('POST', '/project/api/logout', 'AuthController@logout');

$router->add('GET', '/project/api/patients', 'PatientController@index');

$router->add('GET', '/project/api/patients/{id}', 'PatientController@show');

$router->add('POST', '/project/api/patients', 'PatientController@store');

$router->add('PUT', '/project/api/patients/{id}', 'PatientController@update');

$router->add('DELETE', '/project/api/patients/{id}', 'PatientController@delete');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '/project/api/patients') !== false) {
    AuthMiddleware::handle();
    CsrfMiddleware::handle();
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);