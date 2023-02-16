<?php
require 'vendor/autoload.php';

use \SmsFly\SmsFlyApi;

$SmsFlyApi = new SmsFlyApi('PLACE_YOUR_API_KEY');

$response = $SmsFlyApi->getPriceList();
//$response = $SmsFlyApi->getSources();

//$phone = '380XXXXXXXXX';
//$response = $SmsFlyApi->sendMessage($phone, ['sms'], [], ['source'=>'InfoCentr', 'ttl'=>5, 'text'=>'Тестова СМС-ка']);
//$response = $SmsFlyApi->sendSmsMessage($phone, 'InfoCentr', 5, 'Тестова СМС-ка 2');
//$response = $SmsFlyApi->sendSmsMessage($phone, 'InfoCentr', 5, 'Тестова СМС-ка 3');

//$msg_id = 'FAPI000B84EF7B000001';
//$msg_id = 'FAPI000B84EF7B000002';
//$msg_id = 'FAPI000B84EF7B000003';

//$response = $SmsFlyApi->getMessageStatus($msg_id);

var_dump($response);