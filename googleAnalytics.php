<?php

/* ----------------------------------------------------
 *  service define
 * ---------------------------------------------------- */
function getService() {
  // Load the Google API PHP Client Library.
  $google_root_dir = '/home/matsuki/php-ga-api/';
  require_once $google_root_dir.'google-api-php-client-1/autoload.php';
  require_once $google_root_dir.'../google-api-data/google-api-const.php';

  // Use the developers console
  $service_account_email = getGoogleacountEmail();
  $key_file_location = getFilelocation($google_root_dir);

  // Create and configure a new client object.
  $client = new Google_Client();
  $client->setApplicationName("MyAnalyticsApp");
  $analytics = new Google_Service_Analytics($client);

  // Read the generated client_secrets.p12 key.
  $key = file_get_contents($key_file_location);
  $cred = new Google_Auth_AssertionCredentials(
    $service_account_email,
    array(Google_Service_Analytics::ANALYTICS_READONLY),
    $key
  );
  $client->setAssertionCredentials($cred);
  if($client->getAuth()->isAccessTokenExpired()) {
    $client->getAuth()->refreshTokenWithAssertion($cred);
   }
 return $analytics;
}

/* ----------------------------------------------------
 * Get the user's first view (profile) ID
 * ---------------------------------------------------- */
function getFirstprofileId(&$analytics) {
  $accounts = $analytics->management_accounts->listManagementAccounts();

  if (count($accounts->getItems()) > 0) {
    $items = $accounts->getItems();
    // print_r($accounts);  
    $firstAccountId = $items[0]->getId();

    // Get the list of properties for the authorized user.
    $properties = $analytics->management_webproperties
      ->listManagementWebproperties($firstAccountId);

    if (count($properties->getItems()) > 0) {
      $items = $properties->getItems();
      $firstPropertyId = $items[0]->getId();

      // Get the list of views (profiles) for the authorized user.
      $profiles = $analytics->management_profiles
        ->listManagementProfiles($firstAccountId, $firstPropertyId);

      if (count($profiles->getItems()) > 0) {
        $items = $profiles->getItems();
        // Return the first view (profile) ID.
        return $items[0]->getId();
      } else {
        throw new Exception('No views (profiles) found for this user.');
      }
    } else {
      throw new Exception('No properties found for this user.');
    }
  } else {
   throw new Exception('No accounts found for this user.');
  }
}

/* ----------------------------------------------------
 * Get the user's first view (profile) ID
 * ---------------------------------------------------- */
function getResults(&$analytics, $profileId) {
  // APIで取得する条件など
  $target_array = array(
    'dimensions'  => 'ga:pagePath', // 取得するディメンション設定
    'filters' => 'ga:pagePath=~/archives/' // URLフィルタリング
  );
// ga:pageTitle,
//    'sort'        => '-ga:pageviews', // ページビューを取得
//    'max-results' => '10' //件数


  // Calls the Core Reporting API and queries for the number of sessions for the last seven days.
  return $analytics->data_ga->get(
    'ga:'.$profileId,
    '1daysAgo',   
    'today',
    'ga:pageViews',
    $target_array
 );
// 'ga:sessions'
}

/* ----------------------------------------------------
 * Get the user's first view (profile) ID
 * ---------------------------------------------------- */
function writeFile($fdata){

  $fname = date('Y-m-d')."_ga_data.txt";
  if ( !file_exists($fname) ){
    touch( $fname );
    echo 'create'.$fname ;
  }
 
  $fp = fopen($fname, "w");
  fwrite($fp, $fdata);
  fclose($fp);
}

function printResults(&$results) {
  // Parses the response from the Core Reporting API
  if (count($results->getRows()) > 0) {
    $fdata = "";
  
    // Get the profile name.
    $profileName = $results->getProfileInfo()->getProfileName();
    $rows = $results->getRows();
//    $sessions = $rows[0][0];

//     print_r ($results);
    print "First view (profile) found: $profileName\n";

    foreach ($rows as $apv) {
//    print_r($apv);
      $fdata .= $apv[0].', '.$apv[1].', '.date('Y/m/d')."\n";
    }

    writeFile($fdata);
  } else {
    print "No results found.\n";
  }
}

/* ----------------------------------------------------
 *  main
 * ---------------------------------------------------- */
$analytics = getService();
$profile=getFirstProfileId($analytics);
$results=getResults($analytics,$profile);
printResults($results);

?>