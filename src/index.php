<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ .'/vendor/autoload.php';
require_once __DIR__ . '/Controllers/UserController.php';

$app = new \Slim\App;
$container = $app->getContainer();
// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ .'/templates', [
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container->get('router'), $basePath));

    return $view;
};

// Render Twig template in route
$app->get('/', function ($request, $response) {
    return $this->view->render($response, 'index.html', []);
})->setName('index');


$app->get('/challenge', function (Request $request, Response $response) {
  $data = $request->getParsedBody();
  if (empty($data['username'])) {
    $result = array('error' => 'Not all fields are filled. ');
    $response->getBody()->write(json_encode($result));
    return;
  }

  $username = filter_var($data['username'], FILTER_SANITIZE_STRING);

  $userController = new UserController();
  $result = $userController->createChallenge($username);
  $response->getBody()->write($result);
  return $response;
});

$app->post('/register', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    if (empty($data['username']) || empty($data['salt']) || empty($data['verifier']) ||
        empty($data['invite_code']) || empty($data['public_key']) || empty($data['enc_private_key'])) {
      $result = array('error' => 'Not all fields are filled. ');
      $response->getBody()->write(json_encode($result));
      return;
    }

    $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
    $salt = filter_var($data['salt'], FILTER_SANITIZE_STRING);
    $verifier = filter_var($data['verifier'], FILTER_SANITIZE_STRING);
    $invite_code = filter_var($data['invite_code'], FILTER_SANITIZE_STRING);
    $public_key = filter_var($data['public_key'], FILTER_SANITIZE_STRING);
    $enc_private_key = filter_var($data['enc_private_key'], FILTER_SANITIZE_STRING);

    $userController = new UserController();
    $resp_data = $userController->register($username, $salt, $verifier, $invite_code, $public_key, $enc_private_key);

    $response->getBody()->write(json_encode($resp_data));
    return $response;
});

$app->post('/login', function (Request $request, Response $response) {
  $data = $request->getParsedBody();
  if (empty($data['username']) || empty($data['password'])) {
    $result = array('error' => 'Not all fields are filled. ');
    $response->getBody()->write(json_encode($result));
    return $response;
  }

  $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
  $password = filter_var($data['password'], FILTER_SANITIZE_STRING);

  $userController = new UserController();
  $result = $userController->login($username, $password);
  $response->getBody()->write($result);
  return $response;
});

$app->run();
