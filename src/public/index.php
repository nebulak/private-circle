<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;
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

    $userController = new UserController():
    return $userController->register($username, $salt, $verifier, $invite_code, $public_key, $enc_private_key);

    //TODO: check username characters

    //check if username is already registered
    $user = R::dispense('user');
    $user->username = $registration_data['username'];
    $user->salt = $registration_data['salt'];
    $user->verifier = $registration_data['verifier'];
    $user->public_key = $registration_data['public_key'];
    $user->enc_private_key = $registration_data['enc_private_key'];
    $id = R::store($user);


    $result = array('rc' => '0');
    $response->getBody()->write(json_encode($result));
    return $response;
});
$app->run();
