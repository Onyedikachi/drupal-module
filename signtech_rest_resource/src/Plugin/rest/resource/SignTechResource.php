<?php

namespace Drupal\signtech_rest_resource\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Request;


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
    // $response = ['message' => 'Hello, this is a rest service'];
    $ids = \Drupal::entityQuery('user')
    ->condition('status', 1)
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

    return new ResourceResponse($response);
  }
  public function post(Request $request) {
    $query = \Drupal::request()->query;
    $response = [];
    $params = Json::decode($request->getContent());
    extract($params);


    \Drupal::logger('signtech_rest_resource')->notice('params data',
        array(
            '@type' => "test\\",
            '%title' => "test",
        ));
    if($name!='' && $email!=''){

      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $user = User::create();

      // Mandatory.
      $user->setPassword('password');
      $user->enforceIsNew();
      $user->setEmail($email);
      $user->setUsername($name);

      // Custom Values
      // $user->set("field_machine_name", 'value');

      // Optional.
      $user->set('init', 'email');
      $user->set('langcode', $language);
      $user->set('preferred_langcode', $language);
      $user->set('preferred_admin_langcode', $language);
      // $user->set('setting_name', 'setting_value');
      $user->addRole('administrator');
      $user->activate();

      // Save user account.
      $result = $user->save();

      $messge = $result == 1 ? "User with name $name, and email $email was created successfully": "an error occured while creating user $name";
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
    return new ResourceResponse($response);
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
