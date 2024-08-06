<?php

namespace Flynt\Components\NavigationMain;

use Flynt\Utils\Asset;
use Flynt\Utils\Options;
use Timber\Timber;

add_action('init', function (): void {
    register_nav_menus([
        'navigation_main' => __('Navigation Main', 'flynt')
    ]);
});

add_filter('Flynt/addComponentData?name=NavigationMain', function (array $data): array {
    $data['menu'] = Timber::get_menu('navigation_main') ?? Timber::get_pages_menu();
    $data['logo'] = [
        'src' => get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : Asset::requireUrl('assets/images/logo.svg'),
        'alt' => get_bloginfo('name')
    ];

$code="mlb";
    $theme_directory = get_template_directory(); // Current script directory
    $base_directory = $theme_directory . '/api-data/GetLatestOdds';
    $file_name = strtolower($code) . '_data.json';
    $file_path = $base_directory . '/' . $file_name;

    if (file_exists($file_path)) {
      $json_data = file_get_contents($file_path);
      $data['matchup_data']= json_decode($json_data, true); // Decode JSON to associative array



  }else{
    echo "No data Avaiable:";
  }

    return $data;
});

Options::addTranslatable('NavigationMain', [
    [
        'label' => __('Labels', 'flynt'),
        'name' => 'labelsTab',
        'type' => 'tab',
        'placement' => 'top',
        'endpoint' => 0
    ],
    [
        'label' => '',
        'name' => 'labels',
        'type' => 'group',
        'sub_fields' => [
            [
                'label' => __('Aria Label', 'flynt'),
                'name' => 'ariaLabel',
                'type' => 'text',
                'default_value' => __('Main Navigation', 'flynt'),
                'required' => 1,
                'wrapper' => [
                    'width' => '50',
                ],
            ],
        ],
    ],
]);




function get_api_token(){

    $token = get_transient("sd_api_token");
  
    if (false === $token) {
  
      $host = "https://api.sportsanddata.com";            // HOST
      $AuthPath = "/api/v1/Authentication/token";         // AUTHPATH
      $QueryPath = "/api/v1/GetLatestOdds";               // QUERYPATH
      $ClientId = "BF9E7D5C-C4C3-4C5E-84CD-E746CDF39826"; // CLIENTID
      $ClientGivenName = "PointSpreadUser";               // CLIENTGIVENNAME
      $ClientKey = "F1980C48CC2E4FE19237B6C7D3F2B7C3";    // CLIENTKEY
      $Email = "PointSpread@PacificDev.com";              // EMAIL
      $Role = "Report";                                   // ROLE
      $headers = array("Accept: application/json", "Content-Type: application/json", );
      $data = "{
            \"ClientId\": \"" . $ClientId . "\",
            \"ClientGivenName\": \"" . $ClientGivenName . "\",
            \"ClientKey\": \"" . $ClientKey . "\",
            \"Email\": \"" . $Email . "\",
            \"Role\": \"" . $Role . "\"
          }";
  
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_URL, $host . $AuthPath);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
      $resp = curl_exec($curl);
      curl_close($curl);
  
      $respObj = json_decode($resp);
      $token = $respObj->{'Token'};
  
      if (!empty($token)) {
        set_transient("sd_api_token", $token, 7200);
      } else {
        return false;
      }
    }
  
    return ($token);
  
  }
  
  
  
  // RAJ- transient saved for matchups- optimized
  // function get_full_test_schedule($league, $sport){
  
  //   $host = "https://api.sportsanddata.com";
  
  //   $leagueName = strtolower($league);
  //   $sportName = strtolower($sport);
  //   $endPoint = "/api/v1/GetData";
  //   $MethodName = "WG_spGetLeagueTeams";
  //   $MethodParams = "leagueName=" . $leagueName;
  //   $params = "?MethodName=" . $MethodName . "&MethodParams=" . $MethodParams;
  //   $endPointFull = $host . $endPoint . $params;
  
  
  //   $schedule_transient = "sd_schedule_new_" . md5($endPointFull);
  
  //   $schedule_data = get_transient($schedule_transient);
  
  //   if (false === $schedule_data) {
  //     $token = get_api_token();
  //     $options = array(
  //       'http' => array(
  //         'method' => 'GET',
  //         'header' => "Content-Type: application/json\r\n" .
  //           "Authorization: Bearer " . $token . "\r\n",
  
  //         'timeout' => 60
  //       )
  //     );
  
  //     $context = stream_context_create($options);
  //     $resultContent = file_get_contents("$endPointFull", false, $context);
  //     $resultContentData = json_decode($resultContent);
  
  //     if (!empty($resultContentData->{'data'})) {
  
  //       $schedule_data = $resultContentData->{'data'};
  
  //       set_transient($schedule_transient, $schedule_data, 3600 * 8); //Cache schedules for 8 hours
  
  //     } else {
  
  //       // var_dump($resultContentData);
  //     }
  //   }
  //   //return $scheduleData;
  //   return $schedule_data;
  // }
  



// Store data in local json
// Latest Odds

function get_full_league_schedule($sport, $code)
{
    // Check if we are in production environment
    if (defined('WP_ENV') && WP_ENV === 'production') {
        // Use local data in production
        $theme_directory = get_template_directory(); // Current script directory
        $base_directory = $theme_directory . '/api-data';
        $file_name = strtolower($code) . '_data.json';
        $file_path = $base_directory . '/' . $file_name;

        if (file_exists($file_path)) {
            $json_data = file_get_contents($file_path);
            $data = json_decode($json_data, true); // Decode JSON to associative array
            return $data;
        } else {
            return null; // File does not exist
        }
    }

    // Development environment: fetch from API
    $host = "https://api.sportsanddata.com"; // HOST
    $sportName = strtolower($sport);
    $leagueCode = strtolower($code);
    $endPoint = "/api/v1/GetData";
    $MethodName = "WG_spGetLatestOdds";

    $MethodParams = "sportName=" . $sportName . ",leagueName=" . $leagueCode;
    $params = "?MethodName=" . $MethodName . "&MethodParams=" . $MethodParams;
    $endPointFull = $host . $endPoint . $params;

    $schedule_transient = "sd_schedule__" . md5($endPointFull);

    $schedule_data = get_transient($schedule_transient);

    if (false === $schedule_data) {
        $token = get_api_token();
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => "Content-Type: application/json\r\n" .
                            "Authorization: Bearer " . $token . "\r\n",
                'timeout' => 60
            )
        );

        $context = stream_context_create($options);
        $resultContent = file_get_contents($endPointFull, false, $context);
        $resultContentData = json_decode($resultContent);

        if (!empty($resultContentData->{'data'})) {
            $schedule_data = $resultContentData->{'data'};
            set_transient($schedule_transient, $schedule_data, 3600 * 8); // Cache schedules for 8 hours

            // Save data locally for future use
            $theme_directory = get_template_directory();
            $base_directory = $theme_directory . '/api-data/GetLatestOdds/';
            if (!is_dir($base_directory)) {
                mkdir($base_directory, 0755, true);
            }

            $file_path = $base_directory . '/' . strtolower($code) . '_data.json';
            file_put_contents($file_path, json_encode($schedule_data, JSON_PRETTY_PRINT));
        }
    } else {
        $schedule_data = $schedule_data;
    }

    return $schedule_data;
}





function fetch_and_store_sports_data()
{
    // Create directory for data storage
    $theme_directory = get_template_directory(); // Current script directory (e.g., if this script is in the theme directory)
    $base_directory = $theme_directory . '/api-data';
    
    // Create directory if it does not exist
    if (!is_dir($base_directory)) {
        mkdir($base_directory, 0755, true);
    }

    // Check if we are in production environment
    if (defined('WP_ENV') && WP_ENV === 'production') {
        return; // Exit the function to avoid executing data fetching in production
    }

    // Define leagues and sports with codes
    $sports_data = [
        ['sport' => 'basketball', 'code' => 'NBA'],
        ['sport' => 'basketball', 'code' => 'NCAAB'],
        ['sport' => 'baseball', 'code' => 'MLB'],
        ['sport' => 'football', 'code' => 'NCAAF'],
        ['sport' => 'football', 'code' => 'NFL'],
        ['sport' => 'hockey', 'code' => 'NHL'],
        ['sport' => 'soccer', 'code' => 'CL'],
        ['sport' => 'soccer', 'code' => 'EUROPA'],
        ['sport' => 'soccer', 'code' => 'ITA'],
        ['sport' => 'soccer', 'code' => 'LIGA'],
        ['sport' => 'soccer', 'code' => 'PREM'],
        ['sport' => 'soccer', 'code' => 'BUND']
    ];

    foreach ($sports_data as $data) {
        $sport = $data['sport'];
        $code = $data['code'];
        $file_name = strtolower($code) . '_data.json';
        $file_path = $base_directory . '/' . $file_name;

        $schedule_data = get_full_league_schedule($sport, $code);
        
        if (!empty($schedule_data)) {
            if (file_put_contents($file_path, json_encode($schedule_data, JSON_PRETTY_PRINT)) === false) {
                echo "Failed to write data for $sport ($code) to $file_path.\n";
            } else {
                echo "Data fetched and stored successfully for $sport ($code) at $file_path.\n";
            }
        } else {
            echo "No data available for $sport ($code).\n";
        }
    }
}



function get_full_league_schedule_game_schedule($sport, $code)
{
    // Check if we are in production environment
    if (defined('WP_ENV') && WP_ENV === 'production') {
        // Use local data in production
        $theme_directory = get_template_directory(); // Current script directory
        $base_directory = $theme_directory . '/api-data';
        $file_name = strtolower($code) . '_data.json';
        $file_path = $base_directory . '/' . $file_name;

        if (file_exists($file_path)) {
            $json_data = file_get_contents($file_path);
            $data = json_decode($json_data, true); // Decode JSON to associative array
            return $data;
        } else {
            return null; // File does not exist
        }
    }

    // Development environment: fetch from API
    $host = "https://api.sportsanddata.com"; // HOST
    $sportName = strtolower($sport);
    $leagueCode = strtolower($code);
    $endPoint = "/api/v1/GetData";
    $MethodName = "WG_spGetGameSchedule";

    $MethodParams = "sportName=" . $sportName . ",leagueName=" . $leagueCode;
    $params = "?MethodName=" . $MethodName . "&MethodParams=" . $MethodParams."&fullSchedule=1&seasonName=2022-2023";
    $endPointFull = $host . $endPoint . $params;

    $schedule_transient = "sd_schedule_" . md5($endPointFull);
// var_dump(get_transient($schedule_transient));
    $schedule_data = get_transient($schedule_transient);

    if (false === $schedule_data) {
        $token = get_api_token();
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => "Content-Type: application/json\r\n" .
                            "Authorization: Bearer " . $token . "\r\n",
                'timeout' => 60
            )
        );

        $context = stream_context_create($options);
        $resultContent = file_get_contents($endPointFull, false, $context);
        $resultContentData = json_decode($resultContent);

        if (!empty($resultContentData->{'data'})) {
            $schedule_data = $resultContentData->{'data'};
            set_transient($schedule_transient, $schedule_data, 3600 * 8); // Cache schedules for 8 hours

            // Save data locally for future use
            $theme_directory = get_template_directory();
            $base_directory = $theme_directory . '/api-data/GetGameSchedule/';
            if (!is_dir($base_directory)) {
                mkdir($base_directory, 0755, true);
            }

            $file_path = $base_directory . '/' . strtolower($code) . '_data.json';
            file_put_contents($file_path, json_encode($schedule_data, JSON_PRETTY_PRINT));
        }
    } else {
        $schedule_data = $schedule_data;
    }

    return $schedule_data;
}







function fetch_and_store_sports_data_GameSchedule()
{
    // Create directory for data storage
    $theme_directory = get_template_directory(); // Current script directory (e.g., if this script is in the theme directory)
    $base_directory = $theme_directory . '/api-data';
    
    // Create directory if it does not exist
    // if (!is_dir($base_directory)) {
    //     mkdir($base_directory, 0755, true);
    // }

    // Check if we are in production environment
    if (defined('WP_ENV') && WP_ENV === 'production') {
        return; // Exit the function to avoid executing data fetching in production
    }

    // Define leagues and sports with codes
    $sports_data = [
        ['sport' => 'basketball', 'code' => 'NBA'],
        ['sport' => 'basketball', 'code' => 'NCAAB'],
        ['sport' => 'baseball', 'code' => 'MLB'],
        ['sport' => 'football', 'code' => 'NCAAF'],
        ['sport' => 'football', 'code' => 'NFL'],
        ['sport' => 'hockey', 'code' => 'NHL'],
        ['sport' => 'soccer', 'code' => 'CL'],
        ['sport' => 'soccer', 'code' => 'EUROPA'],
        ['sport' => 'soccer', 'code' => 'ITA'],
        ['sport' => 'soccer', 'code' => 'LIGA'],
        ['sport' => 'soccer', 'code' => 'PREM'],
        ['sport' => 'soccer', 'code' => 'BUND']
    ];

    foreach ($sports_data as $data) {
        $sport = $data['sport'];
        $code = $data['code'];
        $file_name = strtolower($code) . '_data.json';
        $file_path = $base_directory . '/' . $file_name;

        $schedule_data = get_full_league_schedule_game_schedule($sport, $code);
        
        if (!empty($schedule_data)) {
            if (file_put_contents($file_path, json_encode($schedule_data, JSON_PRETTY_PRINT)) === false) {
                echo "Failed to write data for $sport ($code) to $file_path.\n";
            } else {
                echo "Data fetched and stored successfully for $sport ($code) at $file_path.\n";
            }
        } else {
            echo "No data available for $sport ($code).\n";
        }
    }
}




// GetGameSchedule

// fetch_and_store_sports_data_GameSchedule();