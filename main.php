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
foreach ($folders as $folder) {

	//Get all Messages of the current Mailbox $folder
	/** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
	$messages = $folder->messages()->all()->get();

	/** @var \Webklex\PHPIMAP\Message $message */
	foreach ($messages as $message) {

		if ($message->getFrom()[0]->mail == "vk_market@mikros.vrn.ru") {
			//echo $message->getTextBody() . '<br />';
			echo $message->getHTMLBody() . '<br />';
		}

		/*$attribute = $message->getAttributes();


		foreach ($attribute as $key => $value) {
			echo $key . " ";
			echo $value . "\n";
		}
*/
		//echo $message->getSubject() . '<br />';



		break;
		//echo 'Attachments: ' . $message->getAttachments()->count() . '<br />';
		//echo $message->getHTMLBody() . '<br />';
		/*
		//Move the current Message to 'INBOX.read'
		if ($message->move('INBOX.read') == true) {
			echo 'Message has ben moved';
		} else {
			echo 'Message could not be moved';
		}
		*/
	}
}
