<?php

namespace Drupal\signtech_rest_resource\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;


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
    $response = ['message' => 'Hello, this is a rest service'];
    return new ResourceResponse($response);
  }

}
