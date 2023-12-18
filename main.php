<?

use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

require __DIR__ . '/vendor/autoload.php';
require_once 'properties.php';

//$cm = new ClientManager('path/to/config/imap.php');

// or use an array of options instead
$cm = new ClientManager($options = []);

/** @var \Webklex\PHPIMAP\Client $client */
$client = $cm->account('account_identifier');

// or create a new instance manually
$client = $cm->make([
	'host'          => $prop['host'],
	'port'          => 993,
	'encryption'    => 'ssl',
	'validate_cert' => true,
	'username'      => $prop['username'],
	'password'      => $prop['password'],
	'protocol'      => 'imap'
]);

//Connect to the IMAP Server
$client->connect();

//Get all Mailboxes
/** @var \Webklex\PHPIMAP\Support\FolderCollection $folders */
$folders = $client->getFolders();

//Loop through every Mailbox
/** @var \Webklex\PHPIMAP\Folder $folder */

//Get all Messages of the current Mailbox $folder
/** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
$messages = $folders[1]->messages()->all()->get();
$today = date("Y-m-d");
/** @var \Webklex\PHPIMAP\Message $message */
foreach ($messages as $message) {
	//$mailFrom = $message->getTo()[0]->mail;
	//$mailDate = $message->getDate(); // 2023-11-01 15:37:18
	$mailDate = date("Y-m-d", strtotime($message->getDate()));
	if (strtotime($mailDate) == strtotime($today) && ($message->getFrom()[0]->mail == "ads.notifications@vk.company")) {
		$textMail =  $message->getHTMLBody() . '\n';
		$flags = $message->getFlags()/*->collect('seen')*/;
		$textMail = explode("<br>", $textMail);

		if (!isset($flags["seen"])) {
			$queryURL = $prop['b24Url'] . "/rest/" . $prop['b24UserID'] . "/" . $prop['b24WebHook'] . "/crm.lead.add.json";


			if (str_contains($textMail[1], 'Дата отправки')) {
				unset($textMail[1]);
			}
			if (str_contains($textMail[2], 'Время лида на момент заполнения формы')) {
				unset($textMail[2]);
			}

			$textMail = array_values($textMail);
			$mailTitle = explode(": ", $textMail[0]);
			$mailName = explode(": ", $textMail[2]);
			$mailTelephone = explode(": ", $textMail[3]);
			$mailCity = explode(": ", $textMail[4]);

			echo $mailTitle[1] . "\n";
			echo $mailName[1] . "\n";
			echo $mailCity[1] . "\n";
			echo $mailTelephone[1] . "\n";

			// формируем параметры для создания лида
			$queryData = http_build_query(array(
				"fields" => array(
					"TITLE" => $mailTitle[1],
					"NAME" => $mailName[1],
					"ADDRESS_CITY" => $mailCity[1],
					"PHONE" => array(
						"n0" => array(
							"VALUE" =>  $mailTelephone[1],
							"VALUE_TYPE" => "MOBILE",
						),
					),
					"STATUS_ID" => 8,
					"ASSIGNED_BY_ID" => 8856,
				),
				'params' => array("REGISTER_SONET_EVENT" => "N")    // Y = произвести регистрацию события добавления лида в живой ленте. Дополнительно будет отправлено уведомление ответственному за лид.	
			));

			// отправляем запрос в Б24 и обрабатываем ответ
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_POST => 1,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $queryURL,
				CURLOPT_POSTFIELDS => $queryData,
			));
			$result = curl_exec($curl);
			curl_close($curl);
			$result = json_decode($result, 1);

			// если произошла какая-то ошибка - выведем её
			if (array_key_exists('error', $result)) {
				die("Ошибка при сохранении лида: " . $result['error_description']);
			}
			echo "Лид добавлен" . "\n";
			$message->setFlag('Seen');
		} else {
			echo "Уже просмотренно";
		}
	}
}
