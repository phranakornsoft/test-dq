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
//require_once("dbconnect.php");
 
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

if ($message == "location_check") {
	$placeName = "ที่ตั้งร้าน";
	$placeAddress = "แขวง พลับพลา เขต วังทองหลาง กรุงเทพมหานคร ประเทศไทย";
	$latitude = 13.780401863217657;
	$longitude = 100.61141967773438;
	$replyData = new LocationMessageBuilder($placeName, $placeAddress, $latitude ,$longitude);
} elseif ($message == "audio_check") {
	$audioUrl = "https://www.ninenik.com/line/S_6988827932080.wav";
	$replyData = new AudioMessageBuilder($audioUrl,20000);
} elseif ($message == "sticker_check") {
	$stickerID = 22;
	$packageID = 2;
	$replyData = new StickerMessageBuilder($packageID,$stickerID);
} elseif ($message == "images_check") {
	$imageMapUrl = 'https://res.cloudinary.com/ginja-co-ltd/image/upload/s--jOaq21IL--/c_fill,h_300,q_jpegmini,w_485/v1/brands/6/inventory/products/18591-coconut-with-toasted-coconut-f-bpxnMg';
	$replyData = new ImagemapMessageBuilder(
	$imageMapUrl, 'This is Title',
	new BaseSizeBuilder(699,1040),
		array(
			new ImagemapMessageActionBuilder(
				'test image map',
				new AreaBuilder(0,0,520,699)
			)
		)
	);
} elseif ($message == "confirm_check") {
	$replyData = new TemplateMessageBuilder('Confirm Template',
	    new ConfirmTemplateBuilder(
	            'Confirm template builder',
	            array(
	                new MessageTemplateActionBuilder(
	                    'Yes',
	                    'Text Yes'
	                ),
	                new MessageTemplateActionBuilder(
	                    'No',
	                    'Text NO'
	                )
	            )
	    )
	);
}  else {
	$textReplyMessage = json_encode($events);
	$replyData = new TextMessageBuilder($textReplyMessage);
}

// if(!is_null($events)){
//     // ถ้ามีค่า สร้างตัวแปรเก็บ replyToken ไว้ใช้งาน
//     $replyToken = $events['events'][0]['replyToken'];
// 	// $userID = $events['events'][0]['source']['userId'];
// 	// $sourceType = $events['events'][0]['source']['type'];

// 	// ส่งข้อมูลกลับ
// 	// $textReplyMessage = 'สวัสดีครับ';
// 	// $replyData = new TextMessageBuilder($textReplyMessage);

// 	// TemplateMessageBuilder
// 	$replyData = new TemplateMessageBuilder('Confirm Template',
// 		new ConfirmTemplateBuilder( 'Confirm template builder', // ข้อความแนะนหรือบอกวิธีการ หรือคำอธิบาย
// 			array(
// 				new MessageTemplateActionBuilder(
// 					'Yes', // ข้อความสำหรับปุ่มแรก
// 					'YES'  // ข้อความที่จะแสดงฝั่งผู้ใช้ เมื่อคลิกเลือก
// 				),
// 				new MessageTemplateActionBuilder(
// 					'No', // ข้อความสำหรับปุ่มแรก
// 					'NO' // ข้อความที่จะแสดงฝั่งผู้ใช้ เมื่อคลิกเลือก
// 				)
// 			)
// 		)
// 	);
// }
// ส่วนของคำสั่งจัดเตียมรูปแบบข้อความสำหรับส่ง
// $textMessageBuilder = new TextMessageBuilder(json_encode($events));

 
//l ส่วนของคำสั่งตอบกลับข้อความ
$response = $bot->replyMessage($replyToken,$replyData);
if ($response->isSucceeded()) {
    echo 'Succeeded!';
    return;
}
 
// Failed
echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
?>