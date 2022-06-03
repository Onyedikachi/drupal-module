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
 *     "canonical" = "/api/1.1"
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
  // public function post(Request $request) {
  //   $query = \Drupal::request()->query;
  //   $response = [];
  //   $params = Json::decode($request->getContent());
  //   extract($params);
  //   if($name!='' && $email!=''){
  //     $response["ServerMsg"]=[
  //         "your_name" => $name,
  //         "your_email" => $email,
  //         "Msg" => "SUCCESS",
  //         "DisplayMsg" => "Rest message for post"
  //     ];
  //   }
  //   else{
  //     $response["ServerMsg"]=[
  //         "Msg" => "Failure",
  //         "DisplayMsg" => "Rest message for post",
  //         "DisplayMsg1" => "Name & Email is required"
  //     ];
  //   }
  //   return new ResourceResponse($response);
  // }
}
