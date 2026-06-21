<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$input = file_get_contents('php://input');
$data = json_decode($input, true);


// Now you can access the values like this:
$workerId = $data['worker_id'];
$projectId = $data['project_id'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];

// $workerId = $_POST['worker_id'];
// $projectId = $_POST['project_id'];
// $latitude = $_POST['latitude'];
// $longitude = $_POST['longitude'];

// echo var_dump($_POST);
// exit;


// Supabase API details
$supabaseUrl = 'https://nubyenmpdyxesfmxnrte.supabase.co';

$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im51Ynllbm1wZHl4ZXNmbXhucnRlIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc0MzYwMTc5NCwiZXhwIjoyMDU5MTc3Nzk0fQ.tdUSYDDvL8Z2tCNbwiRQCDWFPPO88Ifbpzcdln-OY0s'; // Use service_role key (NOT anon/public key)





// $today = date('Y-m-d');
$today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
// $select_url = $supabaseUrl . "/rest/v1/worker_locations?" .
//       "worker_id=eq.$workerId&" .
//       "project_id=eq.$projectId&" .
//       "order=created_at.desc&limit=1";

$select_url = $supabaseUrl . "/rest/v1/worker_locations?" .
    "worker_id=eq.$workerId&" .
    "project_id=eq.$projectId&" .
    "created_at=gte.$today" . "T00:00:00&" . // Filter today's records
    "order=created_at.desc&" .
    "limit=1";


$ch1 = curl_init($select_url);

curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json",
    "Accept: application/json"
]);

curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);

// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


$response = curl_exec($ch1);

if (curl_errno($ch1)) {
    
    echo 'Request Error: ' . curl_error($ch1);
    
    

}else{
    
    
    $result = json_decode($response, true);
    
    
    if( $result==null ){
        
        $last_record = null;
        
    }else{
        
        $last_record = $result[0];
        
    }
    
    // echo $last_record; exit;
    
    // echo $last_record['worker_id'];
    
    // echo 'and project details are';
    
    $project_details = getprojectlocation($apiKey, $supabaseUrl, $projectId);
    
    
    $mylocation = ['latitude' => $latitude, 'longitude' => $longitude];
    $projectlocation = ['latitude' => $project_details['latitude'], 'longitude' => $project_details['longitude']];
    
    $distance = calculateDistance($mylocation, $projectlocation);
    
    
    $isInside = $distance <= (int)($project_details['radius']);
    $newStatus = $isInside ? 'inside' : 'outside';
    $lastStatus = $last_record ? $last_record['status'] : '';
    $lastId = $last_record ? $last_record['id'] : '';
    
    // echo 'now ='. $isInside . ' new status = '. $newStatus. ' lastStatus = '. $lastStatus;
    
    // echo "\n";
    
    if ($lastStatus == '' && $newStatus === 'inside') {
        // console.log('first');
        $savedornot = saveNow($supabaseUrl, $apiKey, [
            'worker_id'  => $workerId, // or however you store session
            'project_id' => $projectId,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'status'     => $newStatus,
            'reason'     => 'working'
        ]);
        
        // echo 'old null new inside';
        // echo $savedornot;
        $msg = ['msg'=>'success', 'status'=>'old_null_new_inside', 'new'=>$newStatus, 'old'=>$lastStatus, 'id'=>$lastId];
        echo json_encode($msg);
        exit;
    }
    
    
    if ( $lastStatus === 'inside' && $newStatus === 'outside' ) {
        // echo 'old_inside_now_outside';
        
        $savedornot = saveNow($supabaseUrl, $apiKey, [
            'worker_id'  => $workerId, // or however you store session
            'project_id' => $projectId,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'status'     => $newStatus,
            'reason'     => 'not_selected'
        ]);
        
        sendNotification($supabaseUrl, $apiKey, $workerId);
        
        $msg = ['msg'=>'success', 'status'=>'old_inside_new_outside', 'new'=>$newStatus, 'old'=>$lastStatus, 'id'=>$lastId];
        echo json_encode($msg);
        exit;
    }
    
    if ( $lastStatus === 'inside' && $newStatus === 'inside') {
        // console.log('third');
        // echo 'old inside new inside';
        $msg = ['msg'=>'success', 'status'=>'old_inside_new_inside', 'new'=>$newStatus, 'old'=>$lastStatus, 'id'=>$lastId];
        echo json_encode($msg);
        exit;
    }
    
    if ( $lastStatus === 'outside' && $newStatus === 'outside') {
        // console.log('third');
        // echo 'old outside new outside';
        $msg = ['msg'=>'success', 'status'=>'old_outside_new_outside', 'new'=>$newStatus, 'old'=>$lastStatus, 'id'=>$lastId];
        echo json_encode($msg);
        exit;
    }
    
    if ( $lastStatus == '' && newStatus === 'outside') {
        // console.log('fourth');
        // return 'fourth';
        // echo 'old null new outside';
        $msg = ['msg'=>'success', 'status'=>'old_null_new_outside', 'new'=>$newStatus, 'old'=>$lastStatus, 'id'=>$lastId];
        echo json_encode($msg);
        exit;
    }
    
    if ( $lastStatus == 'outside' && $newStatus === 'inside' ) {
        // 
        // echo 'old outside new inside';
        $savedornot = saveNow($supabaseUrl, $apiKey, [
            'worker_id'  => $workerId, // or however you store session
            'project_id' => $projectId,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'status'     => $newStatus,
            'reason'     => 'working'
        ]);
        // echo $savedornot;
        
        $msg = ['msg'=>'success', 'status'=>'old_outside_new_inside', 'new'=>$newStatus, 'old'=>$lastStatus, 'id'=>$lastId];
        echo json_encode($msg);
        exit;
        // return resolve('fourth');
    }
            
            
            
    // echo "Distance: " . $distance . " meters";
    
}



    
    

    
    // print_r($result);

exit;
curl_close($ch);




function getprojectlocation($apiKey, $supabaseUrl, $project_id){
    // $project_url = $supabaseUrl . "/rest/v1/projects?" ."project_id=eq.$project_id";
    $project_url = $supabaseUrl . "/rest/v1/projects?id=eq." . $project_id;

       
    $ch2 = curl_init($project_url);
    
    curl_setopt($ch2, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response2 = curl_exec($ch2);
    
    if (curl_errno($ch2)) {
    
        echo 'Request Error: ' . curl_error($ch2);
    
    }else{
        
        
        $result2 = json_decode($response2, true);
        
        return $result2[0];
        
    }
    
    curl_close($ch2);
}



function calculateDistance($point1, $point2) {
    $R = 6371000; // Earth's radius in meters

    $lat1 = deg2rad($point1['latitude']);
    $lat2 = deg2rad($point2['latitude']);
    $deltaLat = deg2rad($point2['latitude'] - $point1['latitude']);
    $deltaLon = deg2rad($point2['longitude'] - $point1['longitude']);

    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
         cos($lat1) * cos($lat2) *
         sin($deltaLon / 2) * sin($deltaLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $R * $c; // in meters
    return $distance;
}



function saveNow($supabaseUrl, $apiKey, $datatosave){
    
    $table = 'worker_locations';
    $url = $supabaseUrl . "/rest/v1/" . $table;
    
    $ch3 = curl_init($url);
    
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch3, CURLOPT_POST, true);
    curl_setopt($ch3, CURLOPT_POSTFIELDS, json_encode($datatosave));
    
    curl_setopt($ch3, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Prefer: return=representation' // Optional: returns the inserted record
    ]);
    
    $response3 = curl_exec($ch3);
    $httpcode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch3)) {
        return 'cURL Error: ' . curl_error($ch3);
    } else {
        // echo "HTTP Status: $httpcode\n";
        return "Response: $response3";
    }
    
    curl_close($ch3);
    
}



function sendNotification($supabaseUrl, $apiKey, $workerid){
    // https://faisaltechnologies.com/apis/EventsValley_Backend/api/sendnotification?device=c7v_SVsLRyy4pLFHOe7JRU:APA91bG345-kwDxtIFk0H4me6gxSf6XEAQQZFAbaATLl9hJlv5x1QC42R2JWYEJq6ELju-FPJcc-PxwzAGGsSILVyINXIOhPrwtCx6sKbbQpFnA81ji_Ucs
    
    // getting user device token
    $project_url = $supabaseUrl . "/rest/v1/users?id=eq.".$workerid;

    $ch4 = curl_init($project_url);
    
    curl_setopt($ch4, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    
    curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
    
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response4 = curl_exec($ch4);
    
    if (curl_errno($ch4)) {
    
        $deviceId = '';
    
    }else{
        
        
        $result4 = json_decode($response4, true);
        
        $deviceId = $result4[0]['device_token'];
        
    }
    
    curl_close($ch4);
    // exit;
    

    
    // $deviceToken = 'c7v_SVsLRyy4pLFHOe7JRU:APA91bG345-kwDxtIFk0H4me6gxSf6XEAQQZFAbaATLl9hJlv5x1QC42R2JWYEJq6ELju-FPJcc-PxwzAGGsSILVyINXIOhPrwtCx6sKbbQpFnA81ji_Ucs';
    $deviceToken = $deviceId;
    
    $url = 'https://faisaltechnologies.com/apis/EventsValley_Backend/api/sendnotification';
    $fullUrl = $url . '?device=' . urlencode($deviceToken);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        // echo 'cURL error: ' . curl_error($ch);
        return false;
    } else {
        return true;
        // echo "HTTP Status Code: $httpCode\n";
        // echo "Response: $response\n";
    }
    
    curl_close($ch);



}





// Data to send
// $data = [
//     'worker_id'   => 'a64b4af9-f858-4362-bfb1-7a556260ba24',
//     'project_id'  => 'd6003abd-caf2-4766-b876-b18c3e665785',
//     'latitude'    => $_POST['latitude'],
//     'longitude'   => $_POST['longitude'],
//     'reason'      => 'background',
//     'status'      => 'moving',
//     'created_at'  => date('c') // ISO 8601 format
// ];

// // cURL setup
// $ch = curl_init($url);

// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// curl_setopt($ch, CURLOPT_HTTPHEADER, [
//     'Content-Type: application/json',
//     'apikey: ' . $apiKey,
//     'Authorization: Bearer ' . $apiKey,
//     'Prefer: return=representation' // Optional: returns the inserted record
// ]);

// $response = curl_exec($ch);
// $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// if (curl_errno($ch)) {
//     echo 'cURL Error: ' . curl_error($ch);
// } else {
//     echo "HTTP Status: $httpcode\n";
//     echo "Response: $response";
// }

// curl_close($ch);
?>
