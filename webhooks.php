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
// require_once("dbconnect.php");
 
///////////// ส่วนของการเรียกใช้งาน class ผ่าน namespace
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Event;
use LINE\LINEBot\Event\BaseEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\ImagemapActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder ;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
 
// เชื่อมต่อกับ LINE Messaging API
$httpClient = new CurlHTTPClient(LINE_MESSAGE_ACCESS_TOKEN);
$bot = new LINEBot($httpClient, array('channelSecret' => LINE_MESSAGE_CHANNEL_SECRET));
 
// คำสั่งรอรับการส่งค่ามาของ LINE Messaging API
$content = file_get_contents('php://input');
 
// แปลงข้อความรูปแบบ JSON  ให้อยู่ในโครงสร้างตัวแปร array
$events = json_decode($content, true);
$message = $events['events'][0]['message']['text'];
$replyToken = $events['events'][0]['replyToken'];

$arrayJson['events'][0]['message']['text'];


// Start
if ($events['events'][0]['message']['type']=="location") {

	// $textReplyMessage = 'กำลังค้นหาร้าน';
	// $replyData = new TextMessageBuilder($textReplyMessage);

	// Data form user
	$userId = $events['events'][0]['source']['userId'];

	$title = $events['events'][0]['message']['title'];
	$address = $events['events'][0]['message']['address'];
	$latitude = $events['events'][0]['message']['latitude'];
	$longitude = $events['events'][0]['message']['longitude'];
	// $LocationAll = $title." ".$address." ".$latitude." ".$longitude;
	// $replyData = new TextMessageBuilder($LocationAll); // Check Location User

	// Function Add On
	$center_lat = $latitude;
	$center_lng = $longitude;
	$radius = "5";
	$log = date("m/d/Y g:i:sA");

	// Insert location to Database
	$sql = "INSERT INTO db_sharelocation (userId, title, address, latitude, longitude, log_time) 
	VALUES ('$userId', '$title', '$address', '$latitude', '$longitude', '$log')";
	$succeeded = mysqli_query($conn, $sql);
	
	$query = sprintf("SELECT id, title, address, latitude, longitude,
	( 3959 * acos( cos( radians('%s') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( latitude ) ) ) )
	AS distance FROM db_location HAVING distance < '%s' ORDER BY distance LIMIT 0 , 3", $center_lat, $center_lng, $center_lat, $radius);

	$result = mysqli_query($conn, $query);
	$result_row = mysqli_num_rows($result);


		// $textReplyMessage = 'กำลังค้นหาร้าน';
		// $replyData2 = new TextMessageBuilder($textReplyMessage);

	if ($result_row==1) {
    	/// output data of 1 row
	    $i = 0;
		while($row = mysqli_fetch_assoc($result)) {
			// echo "id: " . $row["id"]. " - Title: " . $row["title"]. " " . $row["address"]. "  " . $row["latitude"]. " " . $row["longitude"]. "<br>";
			$dq_title = mb_substr($row["title"],0,60,'UTF-8');
			$location['title'][$i] = $dq_title;
			$dq_address = mb_substr($row["address"],0,60,'UTF-8');
			$location['address'][$i] = $dq_address;
			$location['latitude'][$i] = $row["latitude"];
			$location['longitude'][$i] = $row["longitude"];
			$i++;
		}

		// Set out put
		$actionBuilder1 = array(
		    new UriTemplateActionBuilder(
				'ดูแผนที่ตั้ง', // ข้อความแสดงในปุ่ม
				'https://maps.google.com/maps?q='.$location['latitude'][0].','.$location['longitude'][0]
			),
		);
		$replyData = new TemplateMessageBuilder('Carousel',
		    new CarouselTemplateBuilder(
		        array(
		            new CarouselColumnTemplateBuilder(
		                $location['title'][0],
		                $location['address'][0],
		                'https://line.phranakornsoft.com/dq-th/img/location.jpg',
		                $actionBuilder1
		            ),
				)
		    )
		);

		// Set Variable
		$title = $location['title'][0];
		$address = $location['address'][0];
		$latitude = $location['latitude'][0];
		$longitude = $location['longitude'][0];

		$sql = "INSERT INTO db_sharelocation (userId, title, address, latitude, longitude, log_time) 
		VALUES ('$userId', '$title', '$address', '$latitude', '$longitude', 'ตอบกลับ')";
		$succeeded = mysqli_query($conn, $sql);
	} elseif ($result_row==2) {
	    /// output data of 2 row
	    $i = 0;
		while($row = mysqli_fetch_assoc($result)) {
			// echo "id: " . $row["id"]. " - Title: " . $row["title"]. " " . $row["address"]. "  " . $row["latitude"]. " " . $row["longitude"]. "<br>";
			$dq_title = mb_substr($row["title"],0,60,'UTF-8');
			$location['title'][$i] = $dq_title;
			$dq_address = mb_substr($row["address"],0,60,'UTF-8');
			$location['address'][$i] = $dq_address;
			$location['latitude'][$i] = $row["latitude"];
			$location['longitude'][$i] = $row["longitude"];
			$i++;
		}
		// $location['address'][9] = "Test";

		// Set out put
		$actionBuilder1 = array(
		    new UriTemplateActionBuilder(
				'ดูแผนที่ตั้ง', // ข้อความแสดงในปุ่ม
				'https://maps.google.com/maps?q='.$location['latitude'][0].','.$location['longitude'][0]
			),
		);
		$actionBuilder2 = array(
		    new UriTemplateActionBuilder(
				'ดูแผนที่ตั้ง', // ข้อความแสดงในปุ่ม
				'https://maps.google.com/maps?q='.$location['latitude'][1].','.$location['longitude'][1]
		    ),
		);
		$replyData = new TemplateMessageBuilder('Carousel',
		    new CarouselTemplateBuilder(
		        array(
		            new CarouselColumnTemplateBuilder(
		                $location['title'][0],
		                $location['address'][0],
		                'https://line.phranakornsoft.com/dq-th/img/location.jpg',
		                $actionBuilder1
		            ),new CarouselColumnTemplateBuilder(
		                $location['title'][1],
		                $location['address'][1],
		                'https://line.phranakornsoft.com/dq-th/img/location.jpg',
		                $actionBuilder2
		            ),
				)
		    )
		);

		// Set Variable
		$title = $location['title'][0];
		$address = $location['address'][0];
		$latitude = $location['latitude'][0];
		$longitude = $location['longitude'][0];
		$title2 = $location['title'][1];
		$address2 = $location['address'][1];
		$latitude2 = $location['latitude'][1];
		$longitude2 = $location['longitude'][1];

		$sql = "INSERT INTO db_sharelocation (userId, title, address, latitude, longitude, log_time) 
		VALUES ('$userId', '$title', '$address', '$latitude', '$longitude', 'ตอบกลับ')";
		$succeeded = mysqli_query($conn, $sql);
		$sql = "INSERT INTO db_sharelocation (userId, title, address, latitude, longitude, log_time) 
		VALUES ('$userId', '$title2', '$address2', '$latitude2', '$longitude2', 'ตอบกลับ')";
		$succeeded = mysqli_query($conn, $sql);
	} elseif ($result_row==3) {
	    /// output data of 3 row
	    $i = 0;
		while($row = mysqli_fetch_assoc($result)) {
			// echo "id: " . $row["id"]. " - Title: " . $row["title"]. " " . $row["address"]. "  " . $row["latitude"]. " " . $row["longitude"]. "<br>";
			$dq_title = mb_substr($row["title"],0,60,'UTF-8');
			$location['title'][$i] = $dq_title;
			$dq_address = mb_substr($row["address"],0,60,'UTF-8');
			$location['address'][$i] = $dq_address;
			$location['latitude'][$i] = $row["latitude"];
			$location['longitude'][$i] = $row["longitude"];
			$i++;
		}
		// $location['address'][9] = "Test";

		// Set out put
		$actionBuilder1 = array(
		    new UriTemplateActionBuilder(
				'ดูแผนที่ตั้ง', // ข้อความแสดงในปุ่ม
				'https://maps.google.com/maps?q='.$location['latitude'][0].','.$location['longitude'][0]
			),
		);
		$actionBuilder2 = array(
		    new UriTemplateActionBuilder(
				'ดูแผนที่ตั้ง', // ข้อความแสดงในปุ่ม
				'https://maps.google.com/maps?q='.$location['latitude'][1].','.$location['longitude'][1]
		    ),
		);
		$actionBuilder3 = array(
		    new UriTemplateActionBuilder(
				'ดูแผนที่ตั้ง', // ข้อความแสดงในปุ่ม
				'https://maps.google.com/maps?q='.$location['latitude'][2].','.$location['longitude'][2]
		    ),
		);
		$replyData = new TemplateMessageBuilder('Carousel',
		    new CarouselTemplateBuilder(
		        array(
		            new CarouselColumnTemplateBuilder(
		                $location['title'][0],
		                $location['address'][0],
		                'https://line.phranakornsoft.com/dq-th/img/location-02.jpg',
		                $actionBuilder1
		            ),new CarouselColumnTemplateBuilder(
		                $location['title'][1],
		                $location['address'][1],
		                'https://line.phranakornsoft.com/dq-th/img/location-02.jpg',
		                $actionBuilder2
		            ),
		            new CarouselColumnTemplateBuilder(
						$location['title'][2],
		                $location['address'][2],
		                'https://line.phranakornsoft.com/dq-th/img/location-02.jpg',
		                $actionBuilder3
		            ),
				)
		    )
		);

		// Set Variable
		$title = $location['title'][0];
		$address = $location['address'][0];
		$latitude = $location['latitude'][0];
		$longitude = $location['longitude'][0];
		$title2 = $location['title'][1];
		$address2 = $location['address'][1];
		$latitude2 = $location['latitude'][1];
		$longitude2 = $location['longitude'][1];
		$title3 = $location['title'][2];
		$address3 = $location['address'][2];
		$latitude3 = $location['latitude'][2];
		$longitude3 = $location['longitude'][2];

		$sql = "INSERT INTO db_sharelocation (userId, title, address, latitude, longitude, log_time) 
		VALUES ('$userId', '$title', '$address', '$latitude', '$longitude', 'ตอบกลับ')";
		$succeeded = mysqli_query($conn, $sql);
		$sql = "INSERT INTO db_sharelocation (userId, title, address, latitude, longitude, log_time) 
		VALUES ('$userId', '$title2', '$address2', '$latitude2', '$longitude2', 'ตอบกลับ')";
		$succeeded = mysqli_query($conn, $sql);
		$sql = "INSERT INTO db_sharelocation (userId, title, address, latitude, longitude, log_time) 
		VALUES ('$userId', '$title3', '$address3', '$latitude3', '$longitude3', 'ตอบกลับ')";
		$succeeded = mysqli_query($conn, $sql);
	} else {
	    // No Near location
		$textReplyMessage = 'ไม่มีร้านใกล้เคียง';
		$replyData = new TextMessageBuilder($textReplyMessage);

		$sql = "INSERT INTO db_sharelocation (log_time) 
		VALUES ('ไม่มีร้านใกล้เคียง')";
	}
	
} elseif ($message == "register") {
	$userId = $events['events'][0]['source']['userId'];

	$response = $bot->getProfile($userId);
	if ($response->isSucceeded()) {
		$userData = $response->getJSONDecodedBody();
		$userId = $userData['userId'];
		$displayName = $userData['displayName'];
		$pictureUrl = $userData['pictureUrl'];
		$statusMessage = $userData['statusMessage'];

		$sql = "INSERT INTO db_members (id_line, line_displayName, line_pictureUrl, line_statusMessage) 
		VALUES ('$userId', '$displayName', '$pictureUrl', '$statusMessage')";
		$succeeded = mysqli_query($conn, $sql);
	}
} elseif ($message!="") {
	$messages = $events['events'][0]['message']['text'];
	$log = date("m/d/Y g:i:sA");
	$userData = $response->getJSONDecodedBody();
	$userId = $userData['userId'];
	$sql = "INSERT INTO db_message (id_line, message, log_time) VALUES ('$userId', '$messages', '$log')";
	$succeeded = mysqli_query($conn, $sql);
}

// Auto Register
$userId = $events['events'][0]['source']['userId'];
$response = $bot->getProfile($userId);
if ($response->isSucceeded()) {
	$userData = $response->getJSONDecodedBody();
	$userId = $userData['userId'];
	$displayName = $userData['displayName'];
	$pictureUrl = $userData['pictureUrl'];
	$statusMessage = $userData['statusMessage'];

	$sql_select = "SELECT id_line FROM db_members";
	$result_select = mysqli_query($conn, $sql);
	if(mysqli_num_rows($result) > 0) {
		$sql = "INSERT INTO db_members (id_line, line_displayName, line_pictureUrl, line_statusMessage) 
		VALUES ('$userId', '$displayName', '$pictureUrl', '$statusMessage')";
		$succeeded = mysqli_query($conn, $sql);
	}	
}
 
// ส่วนของคำสั่งตอบกลับข้อความ

$response = $bot->replyMessage($replyToken,$replyData);
if ($response->isSucceeded()) {
	$ok = 'Succeeded!';
	
	$log = date("m/d/Y g:i:sA");
	$userData = $response->getJSONDecodedBody();
	$userId = $userData['userId'];
	$error = $response->getHTTPStatus() . ' ' . $response->getRawBody();

	$sql = "INSERT INTO db_message (id_line, message, log_time) 
		VALUES ('$userId', '$ok', '$log')";
		$succeeded = mysqli_query($conn, $sql);

    return;
} else {
	$log = date("m/d/Y g:i:sA");
	$userData = $response->getJSONDecodedBody();
	$userId = $userData['userId'];
	$error = $response->getHTTPStatus() . ' ' . $response->getRawBody();

	$sql = "INSERT INTO db_message (id_line, message, log_time) 
		VALUES ('$userId', '$error', '$log')";
		$succeeded = mysqli_query($conn, $sql);
}
 
// Failed
// echo $response->getHTTPStatus() . ' ' . $response->getRawBody();


?>