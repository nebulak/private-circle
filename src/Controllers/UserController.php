<?php
require_once './classes/ErrorMessage.php';
require_once 'config.php';

class UserController
{
  public function register($username, $password, $invite_code, $salt, $public_key, $private_key)
  {
    $username = strtolower($username);
    //TODO: check username characters

    //Check if username already exists
    $user = R::findOne('user', 'username = :username', array(
     ':username' => $username
    ));

    if(! empty($user) )
		{
			$error = new ErrorMessage("The username already exists!");
			return json_encode($error);
		}

    //check if first user is registered
    if( count(R::findAll( 'user' )) != 0 )
    {
      //check if given invite-code exists
      $invitor = R::findOne( 'user', '  invite_code = ? ', [ strtolower($invite_code)]);
      if( $invitor == NULL )
      {
        $error = new ErrorMessage("The invite-code is wrong!");
        return json_encode($error);
      }
    }

    $user = R::dispense('user');
    $user->username = $registration_data['username'];
    $user->salt = $registration_data['salt'];
    $user->verifier = $registration_data['verifier'];
    $user->public_key = $registration_data['public_key'];
    $user->enc_private_key = $registration_data['enc_private_key'];
    $id = R::store($user);


}
