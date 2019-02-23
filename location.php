<?php
// กรณีต้องการตรวจสอบการแจ้ง error ให้เปิด 3 บรรทัดล่างนี้ให้ทำงาน กรณีไม่ ให้ comment ปิดไป
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
// include composer autoload
require_once 'vendor/autoload.php';
 
// การตั้งเกี่ยวกับ bot
require_once 'bot_settings.php';
 
// กรณีมีการเชื่อมต่อกับฐานข้อมูล
require_once 'dbconnect.php';


$latitude = NULL;
$longitude = NULL;

// 13.7502462,100.5237243,14.31z
// $latitude = "13.7502462";
// $longitude = "100.5237243";

// 13.620962498384  100.5753780741
$latitude = "13.718046102539";
$longitude = "100.64266057178";

echo $latitude = substr($latitude, 0, 4); // returns "abcdef"
echo "<br>";
echo $longitude = substr($longitude, 0, 5); // returns "abcdef"
echo "<br>";echo "<br>";

$center_lat = $latitude;
$center_lng = $longitude;
$radius = "4";

// $query = sprintf("SELECT id, title, address, latitude, longitude, 
// 	( 3959 * acos( cos( radians('%s') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( latitude ) ) ) )
//     AS distance FROM db_location HAVING distance < '%s' ORDER BY distance LIMIT 0 , 3", $center_lat, $center_lng, $center_lat, $radius);
    
$query = "SELECT * FROM db_location WHERE latitude LIKE '$latitude%' AND longitude LIKE '$longitude%' ";

$result = mysqli_query($conn, $query);
$result_row = mysqli_num_rows($result);
echo "Row = ".$result_row."<br>";

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    $i = 0;
    while($row = mysqli_fetch_assoc($result)) {
        // echo "id: " . $row["id"]. " - Title: " . $row["title"]. " " . $row["address"]. "  " . $row["latitude"]. " " . $row["longitude"]. "<br>";
        // $location['title'][$i] = $row["title"];
        // $location['address'][$i] = $row["address"];
        echo $location['latitude'][$i] = $row["latitude"]." ";
        echo $location['longitude'][$i] = $row["longitude"]."<br>";
        $i++;
    }
    // Print_R
    // print_r($location);
    // printf($location['title'][0]);
} else {
    echo "0 results";
}


/**
 * Calculates the great-circle distance between two points, with
 * the Haversine formula.
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius Mean earth radius in [m]
 * @return float Distance between points in [m] (same as earthRadius)
 */
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
  {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
  
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
  
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
  }
// echo "<br>";
// echo haversineGreatCircleDistance('13.718046102539', '100.64266057178', '13.7405440', '100.6376550');

?>