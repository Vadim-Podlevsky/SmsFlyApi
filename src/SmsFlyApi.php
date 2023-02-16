<?php

namespace SmsFly;

/**
 * Created by PhpStorm.
 * User: vadimpodlevsky
 * Date: 16.02.2023
 * Time: 17:53
 * @see https://sms-fly.ua/public/api.v2.03.pdf
 */
class SmsFlyApi
{

	/**
	 * @var string
	 */
	private $API_KEY;

	/**
	 * @var bool
	 */
	private $use_curl;

	const API_PROTOCOL = 'HTTP';
	const API_HOSTNAME = 'sms-fly.ua';
	const API_PATH = '/api/v2/api.php';

	/**
	 * SmsFlyApi constructor.
	 * @param string $API_KEY
	 */
	public function __construct(string $API_KEY)
	{
		$this->setApiKey($API_KEY);
		$this->use_curl = function_exists('curl_version');
	}

	/**
	 * @param string $API_KEY
	 */
	public function setApiKey(string $API_KEY){
		$this->API_KEY = $API_KEY;
	}

	/**
	 * @return mixed
	 */
	public function getBalance(){
		$request['auth']['key'] = $this->API_KEY;
		$request['action'] = 'GETBALANCE';
		return $this->sendApiRequest($request);
	}

	/**
	 * @return mixed
	 */
	public function getBalanceExt(){
		$request['auth']['key'] = $this->API_KEY;
		$request['action'] = 'GETBALANCEEXT';
		return $this->sendApiRequest($request);
	}

	/**
	 * @return mixed
	 */
	public function getSources(){
		$request['auth']['key'] = $this->API_KEY;
		$request['action'] = 'GETSOURCES';
		return $this->sendApiRequest($request);
	}

	/**
	 * @param array $channels
	 * @param int $mcc
	 * @return mixed
	 */
	public function getPriceList(array $channels = ['sms'], $mcc = 255){
		$request['auth']['key'] = $this->API_KEY;
		$request['action'] = 'GETPRICELIST';
		$request['data']['channels']  = $channels;
		$request['data']['mcc'] = $mcc;
		return $this->sendApiRequest($request);
	}

	/**
	 * @param string $recipient = 380991001010, phonenumber
	 * @param array $channels = ["viber", "sms"],
	 * @param array $viber = [
	 * 		"source" => "MyViberSource",
	 * 		"ttl" => 5,
	 *		"text" => "Viber text",
	 *		"button" => [
	 *			"caption": "Button Caption",
	 *			"url": "https://example.org"
	 * 		],
	 *		"image" => "https://example.org/image.png"
	 * ]
	 * @param array $sms = [
	 * 		"source" => "MySMSSource",
	 *		"ttl" => 5,
	 *		"text" => "SMS text"
	 * ]
	 * @return array [
	 *	"success" => 1,
	 *  "date" => "2023-02-16 18:48:13 +0200",
	 *	"data" => [
	 * 		"messageID" => "FAPI000B84EF7B000001"
	 *		"viber" => [
	 * 			"status" => "ACCEPTD",
	 * 			"date" => "2023-02-16 18:48:13 +0200",
	 * 			"label" => "transaction:1",
	 * 			"cost" => "0.750"
	 * 		],
	 * 		"sms" => [
	 * 			"status" => "ACCEPTD",
	 * 			"date" => "2023-02-16 18:48:13 +0200",
	 * 			"cost" => "0.459"
	 * 		]
	 * 	]
	 * ]
	 */
	public function sendMessage(string $recipient, array $channels = ['viber', 'sms'], array $viber = [], array $sms = []){
		$request['auth']['key'] = $this->API_KEY;
		$request['action'] = 'SENDMESSAGE';
		$request['data']['recipient'] = $recipient;
		$request['data']['channels']  = $channels;
		$request['data']['viber'] = $viber;
		$request['data']['sms'] = $sms;
		return $this->sendApiRequest($request);
	}

	/**
	 * Shortcut method
	 *
	 * @param string $recipient
	 * @param string $source
	 * @param int $ttl
	 * @param string $text
	 * @return array
	 */
	public function sendSmsMessage(string $recipient, string $source, int $ttl, string $text){
		return $this->sendMessage($recipient, ['sms'], [], ['source'=>$source, 'ttl'=>$ttl, 'text'=>$text]);
	}

	/**
	 * @param string $messageId
	 * @return array [
	 * 		"success"=> 1,
	 *		"date"=> "2021-12-17 10:54:37 +0200",
	 *		"data"=> [
	 *		"messageID"=> "FAPI00040A3AFA000002",
	 *		"viber"=> [
	 *			"status"=> "DELIVRD",
	 *			"date"=> "2021-12-17 10:36:09"
	 *		],
	 *		"sms"=> [
	 *			"status"=> "REFUND",
	 *			"date"=> "2021-12-17 10:36:12"
	 *		]
	 * ]
	 */
	public function getMessageStatus(string $messageId){
		$request['auth']['key'] = $this->API_KEY;
		$request['action'] = 'GETMESSAGESTATUS';
		$request['data']['messageID'] = $messageId;
		return $this->sendApiRequest($request);
	}

	/**
	 * @param $request
	 * @return mixed
	 * @throws SmsFlyApiException
	 */
	private function sendApiRequest($request){
		$request = json_encode($request);
		if($this->use_curl)
		{
			$api_url = strtolower(self::API_PROTOCOL).'://'.self::API_HOSTNAME.self::API_PATH;
			# ініціалізуємо CURL для відправки запиту. Також можна відправляти через звичайні сокети.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); # повернути контент
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); # переходити по перенаправлення
			curl_setopt($ch, CURLOPT_POSTREDIR, 1); # опрацьовувати 301 перенаправлення
			curl_setopt($ch, CURLOPT_HEADER, 0); # 1 - відображати заголовки, 0 - не відображати
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_URL, $api_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/json")); # означимо в заголовках, що у нас  контент JSON
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
			$response = curl_exec($ch);

			$info = curl_getinfo($ch);

//			var_dump($info);
			if ($info['http_code'] == 0) {
				throw new SmsFlyApiException('Http error on Curl request');
			}
			if ($info['http_code'] == 404) {
				throw new SmsFlyApiException('Api Path not found');
			}

			curl_close($ch); # закриваємо з'єднання

			return json_decode($response, true);
		}
		else
		{
			# використовуємо звичайний socket

			$hostname = self::API_HOSTNAME;
			$path = self::API_PATH;
			$protocol = self::API_PROTOCOL;
			$lines = array();
			$is_data = false;
			$fp = fsockopen($hostname, 80, $errno, $errstr, 30);
			if (!$fp)
			{
				echo "$errstr ($errno)<br />\n";
			}
			else
			{
				$headers = "POST $path $protocol/1.1\r\n";
				$headers .= "Host: $hostname\r\n";
				$headers .= "Content-type: Content-Type: application/json\r\n";
				$headers .= "Content-Length: ".strlen($request)."\r\n\r\n";
				fwrite($fp, $headers.$request);
				while (!feof($fp))
				{
					$one_line = fgets($fp, 1024);
					if($one_line == "\r\n")
					{
						$is_data = ($is_data) ? false : true;
					}
					if($is_data) $lines[] = $one_line;
				}
				fclose($fp);
			}

			$response = $lines[2];

			return json_decode(trim($response), true);

		}
	}

}

class SmsFlyApiException extends \Exception {

	public function __toString(){
		return 'SmsFlyApi Error: '.parent::__toString();
	}
}