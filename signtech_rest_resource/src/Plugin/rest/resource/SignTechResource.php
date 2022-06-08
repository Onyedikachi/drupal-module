<?php

namespace Drupal\signtech_rest_resource\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Request;

use Firebase\JWT\JWT;


define('JWT_SECRET', 'PLutoniumReaction_48692'); // JWT secret key.
define('SIGNTECH_API_1_1_SUCCESS', 1);
define('SIGNTECH_API_1_1_UNDEFINIED_ERROR', 0);
define('SIGNTECH_API_1_1_PARAMETER_FAILED', -1);
define('SIGNTECH_API_1_1_USER_EXISTS', -2);
define('SIGNTECH_API_1_1_USER_NOT_EXISTS', -3);
define('SIGNTECH_API_1_1_NEW_PASSWORD_REQUEST_FAILED', -4);
define('SIGNTECH_API_1_1_ONE_TIME_LINK_EXPIRED', -5);
define('SIGNTECH_API_1_1_USER_PASSWORD_MODIFY_FAILED', -6);
define('SIGNTECH_API_1_1_ACCESS_DENIED', -7);
define('SIGNTECH_API_1_1_USER_INACTIVE', -8);
define('SIGNTECH_API_1_1_USER_AUTHENTICATION_FAILED', -9);
define('SIGNTECH_API_1_1_NO_COMPANY_EXISTS', -10);
define('SIGNTECH_API_1_1_USER_STATUS_UNMODIFIED', -11);
define('SIGNTECH_API_1_1_MAIL_SENDING_FAILED', -12);
define('SIGNTECH_API_1_1_USER_BLOCKED', -13);
define('SIGNTECH_API_1_1_IP_BLOCKED', -14);


/**
 * Provides a Signtech Resource
 *
 * @RestResource(
 *   id = "signtech_resource",
 *   label = @Translation("SignTech Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/1.1",
 *     "create" = "/api/1.1"
 *   }
 * )
 */
class SigntechResource extends ResourceBase {

   /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    return $this->all_users();
  }

  /**
   * Responds to entity POST requests.
   * @return \Drupal\rest\ResourceResponse
   */
  public function post(Request $request) {
    $query = \Drupal::request()->query;
    $response = [];
    $params = Json::decode($request->getContent());
    \Drupal::logger('signtech_rest_resource')->notice(json_encode($params));

    $function = $params['function'];

    switch ($function) {
      case 'create_user':
        return $this->create_user($params);
      case 'login':
        return $this->login($params);
      default:
        return $this->default_Message();
    }
  }
  public function all_users (){
    $ids = \Drupal::entityQuery('user')
    // ->condition('status', 1)
    // ->condition('roles', 'administrator')
    ->execute();

    $users = User::loadMultiple($ids);
    $response = array();

    foreach($users as $user){
        $username = $user->get('name')->getString();
        $mail =  $user->get('mail')->getString();

        $user_data = array();
        $user_data['email'] = $mail;
        $user_data['name'] = $username;

        $response[] = $user_data;
    }

    return $this->send_response($response);
 }
 public function user_by_id ($id){
   $user = User::load($id);
   $data = array();

   if ($user){
     $data['name'] = $user->get('name')->value;
     $data['email'] = $user->get('mail')->value;
     $data['uid'] = $user->get('uid')->value;
     $data['first_name'] = $user->get('field_first_name')->value;
     $data['last_name'] = $user->get('field_last_name')->value;
     $data['company'] = $user->get('field_cid')->value;
     $data['admin'] = $user->get('field_phoenix')->value;
     $data['expiration'] = strtotime('+1 day');
     $data['company_name'] = '';
     $data['reseller'] = '';
     $data['company_api'] = '';
     $data['rid'] = '';
     $data['icon'] = '';
     $data['template'] = '';
     $data['company_secret'] = '';
   }

   return $data;
  }
  public function create_user($data){
    extract($data);

    if($name!='' && $email!=''){
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $user = User::create();

      // Mandatory.
      $user->setPassword('password');
      // $user->enforceIsNew();
      $user->setEmail($email);
      $user->setUsername($name);
      $user->setLastAccessTime($access);
      $user->setLastLoginTime($login);

      // note name is email from drupal 7
      $user->set("field_first_name", $fname);
      $user->set("field_last_name", $fname);
      $user->set("field_cid", $cid);
      $user->set("field_phone", $phone);
      $user->set("field_phoenix", $phoenix);

      // Optional.
      $user->set('init', $init);
      $user->set('uid', $uid);
      $user->set('langcode', $language);
      $user->set('preferred_langcode', $language);
      $user->set('preferred_admin_langcode', $language);
      $user->set('created', $created);
      // $user->set('setting_name', 'setting_value');
      $user->addRole('basic_user');
      $user->activate();

      // Save user account.
      $result = $user->save();

      $database = \Drupal::database();
// // Dirty overwrite of the re-hashed hash.
      $database->merge('users_field_data')
        ->fields(['pass' => $pass])
        ->keys(array('uid' => $uid))
        ->execute();

      $message = $result == 1 ? "User with name $name, and email $email was created successfully": "an error occured while creating user $name";
      \Drupal::logger('signtech_rest_resource')->notice(json_encode($message));

      $response["ServerMsg"]=[
          "your_name" => $name,
          "your_email" => $email,
          "Msg" => "SUCCESS",
          "DisplayMsg" => "Rest message for post"
      ];
    }
    else{
      $response["ServerMsg"]=[
          "Msg" => "Failure",
          "DisplayMsg" => "Rest message for post",
          "DisplayMsg1" => "Name & Email is required"
      ];
    }
    return $this->send_response($response);
  }
  public function login(array $data){
    if (empty($data['name']) || empty($data['password'])) {
      return  $this->send_response(SIGNTECH_API_1_1_PARAMETER_FAILED);
    }
    $username = trim($data['name']);
    $password = trim($data['password']);

    if ($id = \Drupal::service('user.auth')
          ->authenticate($username, $password)) {
        if ($token = $this->prepare_jwt($id)){
          return $this->send_response($token);
        }
        return $this->send_response(SIGNTECH_API_1_1_USER_NOT_EXISTS);
    }
    return  $this->send_response(SIGNTECH_API_1_1_USER_AUTHENTICATION_FAILED);
  }
  public function prepare_jwt($id){
    $expiration = strtotime('+24 hour');
    if($user =  $this->user_by_id($id)){
      return array(
        'token' => JWT::encode($user, JWT_SECRET, 'HS256'),
        'expiration' => $expiration,
      );
    }
    return null;
  }
  public function send_response($data){
    return new ResourceResponse($data);
  }
  public function  default_message($message = "Unknown Function"){
    $response = array();
    $response["message"] = $message;
    return $this->send_response($response);
  }
}

// $user = User::create(array(
//   'uid' => $data[0],
//   'name' => $data[1],
//   'pass' => $data[2],
//   'mail' => $data[3],
//   'status' => $data[4],
//   'access' => $data[6],
//   'login' => $data[7],
//   'timezone' => $data[8],
//   'langcode' => $data[9],
//   'preferred_langcode' => $data[9],
//   'preferred_admin_langcode' => $data[9],
//   'init' => $data[10],
// ));
// $user->created = $data[5];
// $roles = explode(",", $data[11]);
// foreach ($roles as $role) {
//   switch ($role) {
//     case 3:
//       $user->addRole('administrator');
//       break;

//     case 4:
//       $user->addRole('contributeur');
//       break;

//     case 6:
//       $user->addRole('admin_m2');
//       break;
//   }
// }
// // Save user account.
// $user->save();

// $database = \Drupal::database();
// // Dirty overwrite of the re-hashed hash.
// $database->merge('users_field_data')
//   ->fields(['pass' => 'YOUR_D7_HASH_HERE'])
//   ->keys(array('uid' => $data[0]))
//   ->execute();


// [
//   {
//       "cid": "5599",
//       "email": "adewunmi@e-beeze.co.uk",
//       "uid": "7178",
//       "name": "Adewunmi Test",
//       "phone": null,
//       "max_user_count": "0",
//       "max_form_count": "25",
//       "payment_category": "21",
//       "max_reply_count": "4800",
//       "max_apireply_count": "4800",
//       "package_updated": "2021-08-18 12:52:44",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//
