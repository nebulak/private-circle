<?php
require_once __DIR__ . '/ErrorMessage.php';
require_once __DIR__ . '/../config.php';
/**
 * These are the dependencies of Thinbus which are installed with `composer update`.
*/
require_once __DIR__ . '/../vendor/pear/math_biginteger/Math/BigInteger.php';
require_once __DIR__ . '/../vendor/paragonie/random_compat/lib/random.php';
/**
 * These two imports are the specfic config paramters and the Thinbus library.
 * The are installed into the `vendor` folder when you run `composer update` to
 * downlaod all the dependencies named in the `composer.json` file.
 */
require __DIR__ . '/../vendor/simon_massey/thinbus-php-srp/thinbus/thinbus-srp-config.php';
require __DIR__ . '/../vendor/simon_massey/thinbus-php-srp/thinbus/thinbus-srp.php';

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

    $data = array("rc" => 0);
    return json_encode($data);
  }

  public function login($username, $password)
  {
    //srp6a
    $M1A = explode(':', $password);
    $M1 = $M1A[0];
    $A = $M1A[1];

    $authentication = R::findOne('auth', 'username = :username', array(
        ':username' => $username
    ));

    if (empty($authentication)) {
      $error = new ErrorMessage("Unable to authenticate!");
      return json_encode($error);
    }

    //Authentication block
    $srp = unserialize($authentication->srp);
    try {
      $srp->step2($A, $M1);
    } catch (\Exception $e) {
      $error = new ErrorMessage("Wrong password!");
      return json_encode($error);
    }

    $data = array("rc" => 0);
    return json_encode($data);
  }

  public function createChallenge($username)
  {
    $user = R::findOne('user', 'username = :username', array(
      ':username' => $username
    ));

    if (empty($user)) {
      $result = array(
        'error' => 'No user with this username'
      );
    }

    $srp = new ThinbusSrp($SRP6CryptoParams["N_base10"], $SRP6CryptoParams["g_base10"], $SRP6CryptoParams["k_base16"], $SRP6CryptoParams["H"]);
    $B = $srp->step1($username, $user->password_salt, $user->password_verifier);
    $serialized_srp = serialize($srp);

    $auth = R::findOne('auth', 'username = :username', array(
            ':email' => $user->username
    ));

    if (empty($auth))
    {
      $auth = R::dispense('auth');
    }
    $auth->username = $user->username;
    $auth->srp = serialize($srp);
    $dbid = R::store($auth);

    $result = array(
        'salt' => $user->password_salt,
        'b' => $B
    );
  }
}
