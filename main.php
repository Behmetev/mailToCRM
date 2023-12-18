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
		//print_r($flags["seen"]);


		if (isset($flags["seen"])) {
			echo "isset";
		} else {
			echo "no isset";
		}



		//print_r($textMail);
		//$message->setFlag('Seen');
	}
}


function addB24(): string
{
	return "Лид добавлен";
}
