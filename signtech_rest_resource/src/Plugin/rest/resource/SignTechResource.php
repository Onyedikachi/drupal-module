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

    return new ResourceResponse($response);
  }
  public function post(Request $request) {
    $query = \Drupal::request()->query;
    $response = [];
    $params = Json::decode($request->getContent());
    \Drupal::logger('signtech_rest_resource')->notice(json_encode($params));

    extract($params);

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

      // TODO. Set custom values
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
//       "users": [
//           {
//               "uid": "7178",
//               "name": "adewunmi@e-beeze.co.uk",
//               "first_name": "Adewunmi",
//               "last_name": "Test"
//           }
//       ]
//   },
//   {
//       "cid": "5601",
//       "email": "adewunmi2@e-beeze.co.uk",
//       "uid": "7180",
//       "name": "Adewunmi Test2",
//       "phone": null,
//       "max_user_count": "0",
//       "max_form_count": "25",
//       "payment_category": "21",
//       "max_reply_count": "4800",
//       "max_apireply_count": "4800",
//       "package_updated": "2021-08-18 15:29:40",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7180",
//               "name": "adewunmi2@e-beeze.co.uk",
//               "first_name": "Adewunmi",
//               "last_name": "Adegbesan"
//           }
//       ]
//   },
//   {
//       "cid": "203",
//       "email": "allianz@e-beeze.co.uk",
//       "uid": "623",
//       "name": "Allianz Digital Accelerator",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2013-11-22 11:32:30",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "332",
//       "email": "barclays@e-beeze.co.uk",
//       "uid": "778",
//       "name": "Barclays",
//       "phone": "0844 811 9111",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-06-09 12:28:32",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "778",
//               "name": "barclaysemail@e-beeze.co.uk",
//               "first_name": "Daniel",
//               "last_name": "Homoki"
//           }
//       ]
//   },
//   {
//       "cid": "5581",
//       "email": "beta.bsc@e-beeze.co.uk",
//       "uid": "7122",
//       "name": "Beta BSC",
//       "phone": "",
//       "max_user_count": "0",
//       "max_form_count": "25",
//       "payment_category": "21",
//       "max_reply_count": "4800",
//       "max_apireply_count": "4800",
//       "package_updated": "2020-09-23 15:40:23",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7122",
//               "name": "beta.bsc@e-beeze.co.uk",
//               "first_name": "BSC",
//               "last_name": "Beta"
//           },
//           {
//               "uid": "7123",
//               "name": "beta.bsc2@e-beeze.co.uk",
//               "first_name": "Beta",
//               "last_name": "BSC 2"
//           }
//       ]
//   },
//   {
//       "cid": "359",
//       "email": "dublin@brightwater.ie",
//       "uid": "829",
//       "name": "Brightwater",
//       "phone": "+44 28 903 2532",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-08-01 13:09:16",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "342",
//       "email": "derrick.smith@cranfield.ac.uk",
//       "uid": "797",
//       "name": "Cranfield University",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-07-01 12:35:34",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "569",
//       "email": "jdagon88@gmail.com",
//       "uid": "1132",
//       "name": "Demo Company",
//       "phone": "",
//       "max_user_count": "1000",
//       "max_form_count": "1000",
//       "payment_category": "7",
//       "max_reply_count": "1000",
//       "max_apireply_count": "500",
//       "package_updated": "2017-01-02 11:34:08",
//       "package_self_service": "1",
//       "reseller": "1",
//       "reseller_id": "5580",
//       "users": [
//           {
//               "uid": "1132",
//               "name": "selfservice1@e-beeze.co.uk",
//               "first_name": "Sels Service",
//               "last_name": "Trial 1"
//           },
//           {
//               "uid": "5261",
//               "name": "j_dag@gmail.com",
//               "first_name": "jerome test demo",
//               "last_name": "Dagon test"
//           },
//           {
//               "uid": "6533",
//               "name": "adewunmi@signtechforms.com",
//               "first_name": "Adegbesan",
//               "last_name": "Samuel"
//           },
//           {
//               "uid": "6683",
//               "name": "leke@signtechforms.com",
//               "first_name": "Leke",
//               "last_name": "Babalola"
//           },
//           {
//               "uid": "6688",
//               "name": "ajoke.sodimu@signtechforms.com",
//               "first_name": "Ajoke",
//               "last_name": "Sodimu"
//           },
//           {
//               "uid": "6689",
//               "name": "rachael.oladejo@signtechforms.com",
//               "first_name": "Rachael",
//               "last_name": "Oladejo"
//           },
//           {
//               "uid": "6704",
//               "name": "onyedikachinwosu@rocketmail.com",
//               "first_name": "Onyedikachi",
//               "last_name": "Nwosu"
//           },
//           {
//               "uid": "6711",
//               "name": "demo1@e-beeze.co.uk",
//               "first_name": "Demo 1",
//               "last_name": "Signtech beta"
//           },
//           {
//               "uid": "6712",
//               "name": "kachi.nwosu@signtechforms.com",
//               "first_name": "Onyedikachi",
//               "last_name": "Nwosu"
//           },
//           {
//               "uid": "7113",
//               "name": "SPS@e-beeze.co.uk",
//               "first_name": "Jerome",
//               "last_name": "Dagonneau"
//           },
//           {
//               "uid": "7129",
//               "name": "rachael.oladejo@gmail.com",
//               "first_name": "Clarence",
//               "last_name": "Signtech test"
//           },
//           {
//               "uid": "7139",
//               "name": "Multi5@e-beeze.co.uk",
//               "first_name": "multi5",
//               "last_name": "demo"
//           },
//           {
//               "uid": "7146",
//               "name": "ajokesodimu@gmail.com",
//               "first_name": "comfort",
//               "last_name": "sodimu"
//           },
//           {
//               "uid": "7202",
//               "name": "front.desk@e-beeze.co.uk",
//               "first_name": "Front",
//               "last_name": "Desk"
//           }
//       ]
//   },
//   {
//       "cid": "5592",
//       "email": "demopaypal@e-beeze.co.uk",
//       "uid": "7168",
//       "name": "demo tech multi",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2021-03-26 16:07:30",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7168",
//               "name": "demopaypal@e-beeze.co.uk",
//               "first_name": "Jerome",
//               "last_name": "test"
//           }
//       ]
//   },
//   {
//       "cid": "297",
//       "email": "lekee@e-beeze.co.uk",
//       "uid": "734",
//       "name": "E-beeze LTD - but test",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-05-10 06:59:14",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "93",
//       "email": "info@e-beeze.co.uk",
//       "uid": "1",
//       "name": "E-beeze Ltd.",
//       "phone": "4402032876578",
//       "max_user_count": "1000",
//       "max_form_count": "1000",
//       "payment_category": "7",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2016-12-23 17:14:29",
//       "package_self_service": "1",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": [
//           {
//               "uid": "1",
//               "name": "admin@signtech.co.uk",
//               "first_name": "Signtech",
//               "last_name": "Admin"
//           }
//       ]
//   },
//   {
//       "cid": "5603",
//       "email": "testpaymentJD@e-beeze.co.uk",
//       "uid": "7203",
//       "name": "FAQ account",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2022-03-23 17:31:46",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7203",
//               "name": "testpaymentJD@e-beeze.co.uk",
//               "first_name": "Jerome",
//               "last_name": "Dag"
//           }
//       ]
//   },
//   {
//       "cid": "484",
//       "email": "fdriver@fdlpower.co.uk",
//       "uid": "1009",
//       "name": "FDL Generators - Beta",
//       "phone": "0118 981 7451",
//       "max_user_count": "10",
//       "max_form_count": "20",
//       "payment_category": "5",
//       "max_reply_count": "0",
//       "max_apireply_count": "0",
//       "package_updated": "2015-03-20 15:27:00",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "1009",
//               "name": "fdltest@e-beeze.co.uk",
//               "first_name": "FDL",
//               "last_name": "Test"
//           }
//       ]
//   },
//   {
//       "cid": "209",
//       "email": "support@freerent.co.uk",
//       "uid": "630",
//       "name": "Freerent Limited",
//       "phone": "4408452997404",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2013-11-29 10:39:06",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "176",
//       "email": "info@hippopools.co.za",
//       "uid": "541",
//       "name": "Hippo Pools Resort",
//       "phone": "0027157932088",
//       "max_user_count": "5",
//       "max_form_count": "10",
//       "payment_category": "1",
//       "max_reply_count": "10000",
//       "max_apireply_count": "0",
//       "package_updated": "2013-10-07 12:34:42",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "201",
//       "email": "hvholtz@e-beeze.co.uk",
//       "uid": "621",
//       "name": "HV Holtzbrinck Ventures",
//       "phone": "+49 89 20 60 77-0",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2013-11-22 11:24:25",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "358",
//       "email": "ni@ico.org.uk",
//       "uid": "828",
//       "name": "ico.",
//       "phone": "02890278757",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-08-01 12:33:06",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "5600",
//       "email": "JD.test@e-beeze.co.uk",
//       "uid": "7179",
//       "name": "Jerome  multi  trial",
//       "phone": null,
//       "max_user_count": "3",
//       "max_form_count": "1",
//       "payment_category": "3",
//       "max_reply_count": "500",
//       "max_apireply_count": "0",
//       "package_updated": "2021-08-26 15:09:45",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7179",
//               "name": "JD.test@e-beeze.co.uk",
//               "first_name": "Jerome",
//               "last_name": "test multi"
//           }
//       ]
//   },
//   {
//       "cid": "5591",
//       "email": "signtechtest1@gmail.com",
//       "uid": "7167",
//       "name": "Jerome lite",
//       "phone": "",
//       "max_user_count": "3",
//       "max_form_count": "1",
//       "payment_category": "3",
//       "max_reply_count": "500",
//       "max_apireply_count": "0",
//       "package_updated": "2021-08-26 15:19:53",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7167",
//               "name": "signtechtest1@gmail.com",
//               "first_name": "Jerome",
//               "last_name": "Lite Multi"
//           }
//       ]
//   },
//   {
//       "cid": "205",
//       "email": "jsp@e-beeze.co.uk",
//       "uid": "625",
//       "name": "JSP Invest",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2013-11-22 11:39:35",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "5578",
//       "email": "test-jwt@e-beeze.co.uk",
//       "uid": "6710",
//       "name": "JWT Test",
//       "phone": "",
//       "max_user_count": "0",
//       "max_form_count": "25",
//       "payment_category": "21",
//       "max_reply_count": "4800",
//       "max_apireply_count": "4800",
//       "package_updated": "2019-11-30 18:59:06",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "5582",
//       "email": "test2@e-beeze.co.uk",
//       "uid": "7143",
//       "name": "Leke test Multi",
//       "phone": "",
//       "max_user_count": "0",
//       "max_form_count": "25",
//       "payment_category": "21",
//       "max_reply_count": "4800",
//       "max_apireply_count": "4800",
//       "package_updated": "2021-01-28 09:59:43",
//       "package_self_service": "1",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7143",
//               "name": "test2@e-beeze.co.uk",
//               "first_name": "LEKE",
//               "last_name": "BABALOLA"
//           }
//       ]
//   },
//   {
//       "cid": "5593",
//       "email": "multi8@e-beeze.co.uk",
//       "uid": "7169",
//       "name": "Mult8",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2021-03-31 10:58:01",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7169",
//               "name": "multi8@e-beeze.co.uk",
//               "first_name": "Multi 8",
//               "last_name": "Test"
//           }
//       ]
//   },
//   {
//       "cid": "5587",
//       "email": "multi@e-beeze.co.uk",
//       "uid": "7162",
//       "name": "Multi",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2021-03-26 22:21:54",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7162",
//               "name": "multi@e-beeze.co.uk",
//               "first_name": "Multi",
//               "last_name": "Test"
//           }
//       ]
//   },
//   {
//       "cid": "5594",
//       "email": "multi9@e-beeze.co.uk",
//       "uid": "7170",
//       "name": "Multi9",
//       "phone": null,
//       "max_user_count": "3",
//       "max_form_count": "1",
//       "payment_category": "3",
//       "max_reply_count": "500",
//       "max_apireply_count": "0",
//       "package_updated": "2021-03-31 13:07:21",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7170",
//               "name": "multi9@e-beeze.co.uk",
//               "first_name": "Multi 9",
//               "last_name": "Test"
//           }
//       ]
//   },
//   {
//       "cid": "5596",
//       "email": "hotadeoba@gmail.com",
//       "uid": "7173",
//       "name": "Multi99",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2021-04-05 10:02:58",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7173",
//               "name": "hotadeoba@gmail.com",
//               "first_name": "Multi",
//               "last_name": "Log"
//           }
//       ]
//   },
//   {
//       "cid": "5579",
//       "email": "multitenancy@e-beeze.co.uk",
//       "uid": "6722",
//       "name": "Multitenancy",
//       "phone": "",
//       "max_user_count": "0",
//       "max_form_count": "0",
//       "payment_category": "22",
//       "max_reply_count": "0",
//       "max_apireply_count": "0",
//       "package_updated": "2020-03-24 00:05:54",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "6722",
//               "name": "multitenancy@e-beeze.co.uk",
//               "first_name": "Multitenancy",
//               "last_name": "Test"
//           },
//           {
//               "uid": "6725",
//               "name": "multi1@e-beeze.co.uk",
//               "first_name": "Multi1",
//               "last_name": "Signtech"
//           },
//           {
//               "uid": "7135",
//               "name": "Multi3@e-beeze.co.uk",
//               "first_name": "Multi 3",
//               "last_name": "Demo"
//           },
//           {
//               "uid": "7138",
//               "name": "multi4@e-beeze.co.uk",
//               "first_name": "multi4",
//               "last_name": "demo"
//           },
//           {
//               "uid": "7142",
//               "name": "oladejorachael@gmail.com",
//               "first_name": "Rachael",
//               "last_name": "Oladejo"
//           }
//       ]
//   },
//   {
//       "cid": "5580",
//       "email": "multitenant@e-beeze.co.uk",
//       "uid": "6723",
//       "name": "Multitenant Beta",
//       "phone": "",
//       "max_user_count": "0",
//       "max_form_count": "0",
//       "payment_category": "22",
//       "max_reply_count": "0",
//       "max_apireply_count": "0",
//       "package_updated": "2020-03-25 10:42:22",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "6723",
//               "name": "multitenant@e-beeze.co.uk",
//               "first_name": "Multitenant",
//               "last_name": "Beta"
//           },
//           {
//               "uid": "6726",
//               "name": "multi2@e-beeze.co.uk",
//               "first_name": "Multi2",
//               "last_name": "Tenant organisation"
//           },
//           {
//               "uid": "7140",
//               "name": "multi6@e-beeze.co.uk",
//               "first_name": "multi6",
//               "last_name": "demo"
//           },
//           {
//               "uid": "7198",
//               "name": "jaylevoyageur@gmail.com",
//               "first_name": "test",
//               "last_name": "08-09-2021"
//           }
//       ]
//   },
//   {
//       "cid": "300",
//       "email": "info@neopost.com",
//       "uid": "737",
//       "name": "neopost",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "10",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-08-04 00:01:00",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "255",
//       "email": "1cphealth.wellbeing@barclayscorp.com",
//       "uid": "686",
//       "name": "Nuffield Health",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-02-28 10:38:49",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "459",
//       "email": "pte@e-beeze.co.uk",
//       "uid": "964",
//       "name": "Pécsi Tudományegyetem",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2014-12-23 13:22:33",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "294",
//       "email": "info@santander.co.uk",
//       "uid": "731",
//       "name": "Santander Demo",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-05-02 17:28:50",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "354",
//       "email": "training@signtechforms.com",
//       "uid": "822",
//       "name": "SignTech Training",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "0",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2014-07-21 14:36:21",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "5597",
//       "email": "expert@signtechforms.com",
//       "uid": "7176",
//       "name": "Signtech Tutorial",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2021-05-18 17:57:08",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7176",
//               "name": "expert@signtechforms.com",
//               "first_name": "Signtech",
//               "last_name": "Tutorial"
//           },
//           {
//               "uid": "7190",
//               "name": "jdagon88@gmail.com",
//               "first_name": "Standard User",
//               "last_name": "Tutorial"
//           }
//       ]
//   },
//   {
//       "cid": "5604",
//       "email": "merging@e-beeze.co.uk",
//       "uid": "7204",
//       "name": "STCS owner",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "1",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2022-05-10 10:34:39",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": [
//           {
//               "uid": "7204",
//               "name": "merging@e-beeze.co.uk",
//               "first_name": "Merging DB",
//               "last_name": "Test"
//           }
//       ]
//   },
//   {
//       "cid": "5590",
//       "email": "signtech.trial1@gmail.com",
//       "uid": "7165",
//       "name": "STCS paypal J1",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2021-03-26 10:02:54",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7165",
//               "name": "signtech.trial1@gmail.com",
//               "first_name": "Jerome",
//               "last_name": "Dagonneau"
//           }
//       ]
//   },
//   {
//       "cid": "5602",
//       "email": "sucroliq@e-beeze.co.uk",
//       "uid": "7199",
//       "name": "Sucroliq sandbox",
//       "phone": null,
//       "max_user_count": "0",
//       "max_form_count": "25",
//       "payment_category": "21",
//       "max_reply_count": "4800",
//       "max_apireply_count": "4800",
//       "package_updated": "2022-02-21 11:22:28",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7199",
//               "name": "sucroliq@e-beeze.co.uk",
//               "first_name": "Sucroliq",
//               "last_name": "Sandbox"
//           }
//       ]
//   },
//   {
//       "cid": "5605",
//       "email": "tenant1@e-beeze.co.uk",
//       "uid": "7205",
//       "name": "Tenant 1",
//       "phone": "",
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "1",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2022-05-10 10:40:05",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7205",
//               "name": "tenant1@e-beeze.co.uk",
//               "first_name": "Tenant1",
//               "last_name": "test"
//           }
//       ]
//   },
//   {
//       "cid": "5606",
//       "email": "tenant2@e-beeze.co.uk",
//       "uid": "7210",
//       "name": "Tenant 2",
//       "phone": "",
//       "max_user_count": "10",
//       "max_form_count": "0",
//       "payment_category": "24",
//       "max_reply_count": "0",
//       "max_apireply_count": "0",
//       "package_updated": "2022-05-10 11:36:49",
//       "package_self_service": "1",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7210",
//               "name": "tenant2@e-beeze.co.uk",
//               "first_name": "Tenant 2",
//               "last_name": "Test"
//           }
//       ]
//   },
//   {
//       "cid": "213",
//       "email": "thewriteplaceuk@gmail.com",
//       "uid": "637",
//       "name": "Test by Steve",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "0",
//       "max_reply_count": "1000",
//       "max_apireply_count": "0",
//       "package_updated": "2013-12-06 16:11:44",
//       "package_self_service": "0",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   },
//   {
//       "cid": "5588",
//       "email": "testco@e-beeze.co.uk",
//       "uid": "7163",
//       "name": "Test Co",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "2",
//       "max_reply_count": "100",
//       "max_apireply_count": "0",
//       "package_updated": "2021-03-23 16:25:06",
//       "package_self_service": "0",
//       "reseller": "1",
//       "reseller_id": "5604",
//       "users": [
//           {
//               "uid": "7163",
//               "name": "testco@e-beeze.co.uk",
//               "first_name": "TestFirst",
//               "last_name": "Testlast"
//           }
//       ]
//   },
//   {
//       "cid": "5573",
//       "email": "expert.signtechforms@gmail.com",
//       "uid": "6625",
//       "name": "Test_Paypal",
//       "phone": null,
//       "max_user_count": "1",
//       "max_form_count": "1",
//       "payment_category": "9",
//       "max_reply_count": "5",
//       "max_apireply_count": "0",
//       "package_updated": "2017-10-27 18:27:18",
//       "package_self_service": "1",
//       "reseller": "0",
//       "reseller_id": "0",
//       "users": []
//   }
// ]
