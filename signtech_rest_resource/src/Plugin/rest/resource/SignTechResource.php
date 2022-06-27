<?php

namespace Drupal\signtech_rest_resource\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Drupal\companies\Entity\Company;
use Drupal\Component\Serialization\Json;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Transaction;

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

  protected  $base_url = "https://2w3tn83u78.execute-api.eu-west-2.amazonaws.com/v1";
  protected  $from_email_address = "support@signtechforms.com";

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
    $params = Json::decode($request->getContent());
    \Drupal::logger('signtech_rest_resource')->notice(json_encode($params));

    $function = $params['function'];

    switch ($function) {
      case 'create_user':
        return $this->create_user_migration_handler($params);
      case 'login':
        return $this->login($params);
      case 'create_company':
        return $this->create_company_handler($params);
      case 'registration':
        return $this->registration($params);
      case 'company_registration':
        return $this->company_registration($params);
      default:
        return $this->default_Message();
    }
  }

  public function create_company_handler($data){
    $response = $this->create_company($data);
    return $this->send_response($response);
  }

  public function get_new_company_id(){
    $database = \Drupal::database();

    $cid = $database->query("SELECT cid from companies ORDER BY cid DESC LIMIT 1;")->fetchField();

    \Drupal::logger('signtech_rest_resource')->notice(json_encode(($cid)));

    return $cid + 1;
  }

  public function create_company($data){
    $company = Company::create();

    $name = trim($data['name']);
    $email = trim($data['email']);
    if (empty($name) || empty($email)){
      return $this->send_response([
        "success" => false,'message' => "name or email undefined"
      ]);
    }

    \Drupal::logger('signtech_rest_resource')->notice(json_encode(($data)));

    $cid = empty($data['cid'])? $this->get_new_company_id(): $data['cid'];

    // $company->set('id', $data['cid']);
    // $company->set('uuid', $data['uuid']);
    $company->set('email', $email);
    $company->set('name', $name);
    $company->set('phone', $data['phone']);
    $company->set('reseller_id', $data['rid']);
    $company->set('reseller', $data['reseller']);
    $company->set('api', $data['company_api']);
    $company->set('icon', $data['icon']);
    $company->set('secret', $data['secret']);
    $company->set('template', $data['template']);

    $company->set('cid', $cid);

    $company->set('max_user_count', $data['max_user_count']);
    $company->set('max_form_count', $data['max_form_count']);
    $company->set('payment_category',$data['payment_category']);
    $company->set('max_reply_count', $data['max_reply_count']);
    $company->set('max_apireply_count', $data['max_apireply_count']);

    $timestamp =  strtotime($data['package_updated']);
    $company->set('package_updated',$timestamp);
    $company->set('package_self_service', $data['package_self_service']);

    $company->set('payment_options', $data['payment_options']);
    $company->set('payment_price', $data['payment_price']);
    $company->set('voucher_used', $data['voucher_used']);

    $result = $company->save();

    $message =
      $result == 1 ?
        "Company with name $name , and email $email was created successfully"
          : "an error occured while creating company $name";

    return $this->send_response([
      "success" =>  $result == 1 ,
      'message' => $message,
      'cid' => $cid
    ]);
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
     $data['admin'] = $user->get('field_phoenix')->value == 1;
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
  public function create_user_migration_handler($data){
    $response = $this->create_user(array_merge($data, array('not_a_new_user' => true)));
    return $this->send_response($response);
  }
  public function create_user($data){
    extract($data);

    $response = [
      'success' => false,
      'message' => ''
    ];

    if($name!='' && $email!=''){
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $user = User::create();

      // Mandatory.
      $user->setPassword($pass? $pass : 'password');
      $not_a_new_user ?
        $user->set('uid', $uid)
        :  $user->enforceIsNew();

      $user->setEmail($email);
      $user->setUsername($name);
      $user->setLastAccessTime($access ? $access : 0);
      $user->setLastLoginTime($login ? $login : 0);

      // note name is email from drupal 7
      $user->set("field_first_name", $fname);
      $user->set("field_last_name", $fname);
      $user->set("field_cid", $cid);
      $user->set("field_phone", $phone);
      $user->set("field_phoenix", $phoenix ? $phoenix : 0);

      // Optional.
      $language = $language ? $language: 'en';
      $user->set('init', $init ? $init : $email);

      $user->set('langcode', $language);
      $user->set('preferred_langcode', $language);
      $user->set('preferred_admin_langcode', $language);
      $user->set('created', $created ? $created : time());
      // $user->set('setting_name', 'setting_value');
      $user->addRole('basic_user');
      $user->activate();

      // Save user account.
      $result = $user->save();
      $uid = $user->id();


      //  Dirty overwrite of the re-hashed hash.

      if ($not_a_new_user){
        $database = \Drupal::database();
        $database->merge('users_field_data')
        ->fields(['pass' => $pass])
        ->keys(array('uid' => $uid))
        ->execute();
      }

      $response['success'] = $result == 1;
      $response['uid'] = $uid;

      $message = $response['success'] ? "User with name $name, and email $email was created successfully": "an error occured while creating user $name";
      \Drupal::logger('signtech_rest_resource')->notice(json_encode($message));
    }
    else{
      $message = "Name & Email is required";
    }

    $response['message'] = $message;

    return $response;
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
    \Drupal::logger('signtech_rest_resource')->notice(json_encode($data));

    return new ResourceResponse((array) $data);
  }
  public function  default_message($message = "Unknown Function"){
    $response = array();
    $response["message"] = $message;
    return $this->send_response($response);
  }

  public function email_exists($email){
    $ids = \Drupal::entityQuery('user')
    ->condition('mail', $email)
    ->execute();

    return count($ids) > 0;
  }
  public function user_id_exists($uid){
    $ids = \Drupal::entityQuery('user')
    ->condition('uid', $uid)
    ->execute();

    return count($ids) > 0;
  }
  public function find_user_by_email($email){
    $user = null;

    if ($this->email_exists($email)){
        $ids = \Drupal::entityQuery('user')
        ->condition('mail', $email)
        ->execute();

        $user_id = $ids[0];

        $user = User::load($user_id);
    }

    return $user;
  }
  public function find_user_by_id($uid){
    $user = null;

    if ($this->user_id_exists($uid)){
        $ids = \Drupal::entityQuery('user')
        ->condition('uid', $uid)
        ->execute();

        $user_id = $ids[0];
        $user = User::load($user_id);
    }
    return $user;
  }
  public function find_company_by_id($cid){
    $company = null;

    $ids = \Drupal::entityQuery('companies')
        ->condition('cid', $cid)
        ->execute();

    if (count($ids) < 1){
      return $company;
    }

    $cid = $ids[0];
    $company = Company::load($cid);

    return $company;
  }

  /**
  *  Company Registration handling.
  *
  * @param array $args
  */
  public function company_registration($args){
    $data = (object) json_decode($args['data'], true);
    \Drupal::logger('signtech_rest_resource')->notice(json_encode($args));

    if(empty($data) || empty($args['cid']) || empty($data->email)){
      $response = array('success'=> false, 'message'=> 'Invalid request.');
      return $this->send_response($response);
    }
    $database = \Drupal::database();
    $transaction = $database->startTransaction();
    try{
      // Insert the phoenix user
      $newUser = array(
          'name' => $data->email,
          'fname' => $data->first_name,
          'lname' => $data->last_name,
          'phone' => $data->phone,
          'email' => $data->email,
          'status' => 1,
          'pass' => $data->password,
          'init' => $data->email,
          'phoenix' => 1
      );

      $nu = $this->create_user($newUser);
      \Drupal::logger('signtech_rest_resource')->notice(json_encode($nu));


      $uuid_service = \Drupal::service('uuid');
      $apisecret = md5($uuid_service->generate());

      $pid = $data->pid;

      $client = \Drupal::httpClient();

      $get_packages_url = $this->base_url . "/packages/$pid";
      $response = $client->get($get_packages_url, [
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        ],
      ]);

      \Drupal::logger('signtech_rest_resource')->notice("About to display package");

      $package = (object)json_decode($response->getBody()->getContents(), TRUE);
      \Drupal::logger('signtech_rest_resource')->notice(json_encode($package));

      // if($data->trial == false){
      //   db_insert('signtech_paypal_payments')->fields(array(
      //       'cid' => $company_id,
      //       'phoenix_id' => $account->uid,
      //       'profile_id' => $data->payment['id'],
      //       'profile_status' => $data->payment['status'],
      //       'timestamp' => time(),
      //       'payment_price' => $data->price,
      //       'package_type' => $data->pid,
      //       'data' => drupal_json_encode(array(
      //           'details' => $data->payment['details'],
      //           'profile' => $data->payment['order'],
      //       )),
      //   ))->execute();
      // }

      // Insert payment options
      $df = $date = \Drupal::service('date.formatter');
      $company = array(
          'name' => $data->name,
          'email' => $data->email,
          'enabled' => 1,
          'api_secret' => $apisecret,
          'payment_options' => $data->method,
          'payment_category' => $data->pid,
          'payment_price' => $data->price,
          'package_updated' => $df->format(time(), 'Y-m-d H:i:s'),
          'package_interval' => 1,
          'max_user_count' => $package->pusers,
          'max_form_count' => $package->pforms,
          'max_reply_count' => $package->pdownloads,
          'max_apireply_count' => $package->papiforms,
          'package_self_service' => $package->pselfservice,
          'reseller' => 1,
          'reseller_id' => $args['cid'],
          'voucher_used' => empty($data->voucher) ? NULL : $data->voucher,
      );

      $new_company = $this->create_company($company);

      \Drupal::logger('signtech_rest_resource')->notice(json_encode($data));


      // TODO: set company api

      // TODO: send email to both new user and admin

      $send_email_url = $this->base_url . "/send-email";

      $response = $client->post($send_email_url, [
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        ],
        'json' => [
          'to' => '',
          'from' => '',
          'type' => 'register_company_created',
          'name' => $data->name ? $data->name : "Test Company",
        ]
      ]);

      $result = (object)json_decode($response->getBody()->getContents(), TRUE);
      \Drupal::logger('signtech_rest_resource')->notice(json_encode($result));

      return $this->send_response(array('success' => true));
    }
    catch (Exception $e) {
        // On any exception rollback the transaction, since not all of the data above could be inserted properly
        $transaction->rollback();
        $response = array('success'=> false, 'message'=> $e->getMessage());
        return  $this->send_response($response);
    }
  }


  /**
  * Registration handling.
  *
  * @param array $args
  */
  public function registration(array $args) {
    try {
      $default = array(
        'pass' => '',
        'mail' => '',
        'language' => '',
        'last_name' => '',
        'first_name' => '',
        'phone' => '',
        'access' => 0,
        'login' => 0,
        'phoenix' => 0,
        'name' => "",
        'status' => 1,
        'init' => '',
        'company' => ''
      );
      // Check if get required parameters.
      if (!empty($args['mail']) &&
          !empty($args['language']) && !empty($args['company'])) {
        // Check if exist user name or user mail.
        if ($this->email_exists($args['mail']) === FALSE) {

          // Prepare new user from parameters.
          $new_user = array_intersect_key($args, $default);

          if (!trim($new_user['pass'])){
            $new_user['pass'] = $this->randomPassword();
          }

          \Drupal::logger('signtech_rest_resource')->notice(json_encode($new_user));

          // rename keys
          $new_user["email"] = $new_user['mail'];
          unset($new_user['mail']);
          $new_user['lname'] = $new_user['last_name'];
          unset( $new_user['last_name']);
          $new_user['fname'] = $new_user['first_name'];
          unset($new_user['first_name']);
          $new_user['cid'] = $new_user['company'];
          unset($new_user['company']);


          $new_user['name'] = $new_user['email'];
          $new_user['init'] = $new_user['email'];
          $new_user['status'] = 1;


          // $new_user['data']['args'] = $args;
          \Drupal::logger('signtech_rest_resource')->notice(json_encode($new_user));
          $response = $this->create_user($new_user);

          if ($response['success']) {

            $client = \Drupal::httpClient();
            $send_email_url = $this->base_url . "/send-email";

            $response = $client->post($send_email_url, [
              'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
              ],
              'json' => [
                'to' => $new_user['email'],
                'from' => $this->from_email_address,
                'type' => 'register_user_to_company',
                'name' => $new_user['fname'] . ' ' . $new_user['lname'],
                'pass' => $new_user['pass'],
                'url' => $args['url'] ? $args['url'] : 'https://multi.amazondigitaloffice.com'
              ]
            ]);
            $result = (object)json_decode($response->getBody()->getContents(), TRUE);
            \Drupal::logger('signtech_rest_resource')->notice(json_encode($result));
            return $this->send_response(SIGNTECH_API_1_1_SUCCESS);
          }
        }
        else {
          return $this->send_response(SIGNTECH_API_1_1_USER_EXISTS);
        }
      }
      else {
        \Drupal::logger('signtech_rest_resource')->notice('Can not create user. Required parameter or parameters failed.');
        return $this->send_response(SIGNTECH_API_1_1_PARAMETER_FAILED);
      }
    }
    catch (Exception $e) {
      \Drupal::logger('signtech_rest_resource')->notice($e->getMessage());
      throw ($e);
    }

    return SIGNTECH_API_1_1_UNDEFINIED_ERROR;
  }
  function decode_token($token) {
    $secret = JWT_SECRET;

    $data = JWT::decode($token, $secret, array('HS256'));
    if (!$data->expiration) {
      return NULL;
    }
    return $data;
  }
  function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
  }
}
