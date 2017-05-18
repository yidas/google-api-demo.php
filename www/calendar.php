<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/GoogleAPI.php';
require_once __DIR__ . '/../components/GoogleApiModel.php';
require_once __DIR__ . '/../components/GoogleCalendar.php';

// Set Client with accessToken(whether or not)
$client = GoogleAPI::setClient()
	->setAccessToken(GoogleApiModel::getToken())
	->getClient();

if (!GoogleAPI::isAuth()) {
	
	header('Location: ./');
}

// Get Service for initializing GoogleCalendar
$service = GoogleAPI::getService('Google_Service_Calendar');

// GoogleCalendar Component
GoogleCalendar::init($service);

/**
 * Operation
 */
if (isset($_GET['op'])) {
	
	switch ($_GET['op']) {
		case 'addedit':
			try {

				/**
				 * Insert
				 */
				$eventID = GoogleCalendar::eventInsert([
						'summary' => 'Demo by GoogleAPI - Insert',
						'location' => 'Default Location',
						'description' => 'For description.',
						'start' => [
							'dateTime' => '2017-05-28T09:00:00',
							'timeZone' => 'Asia/Taipei',
						],
						'end' => [
							'dateTime' => '2017-05-29T17:00:00+08',
							// 'dateTime' => '2017-05-29T17:00:00',
							// 'timeZone' => 'Asia/Taipei',
						],
					]);

				/**
				 * Update
				 */
				$event = GoogleCalendar::eventGet($eventID);
				// print_r($event->getStart());throw new Exception("Error Processing Request", 1);
				
				$event->setSummary('Demo by GoogleAPI - Edit');

				$dateObject = new Google_Service_Calendar_EventDateTime(); // WTF
				// $dateObject->setTimeZone('Asia/Taipei');
				$dateObject->setDateTime('2017-05-10T19:15:00+08:00');
				$event->start = $dateObject;

				$event->end->timeZone = 'Asia/Taipei';
				$event->end->dateTime = '2017-05-13T19:15:00';

				$updateAt = GoogleCalendar::eventUpdate($event);
				// print_r($updateAt);
			
				/**
				 * Delete
				 */		
				// $result = GoogleCalendar::eventDelete($eventID);print_r($result);exit;

			} catch (Exception $e) {
				
				throw $e;
			}

			echo "Operation Success!<br/> Updated at {$updateAt}<br/> <a href=\"?\">Back to List Page</a>";

			break;
		
		default:
			echo '404 - Bad Operation';
			break;
	}

	return;
}


/**
 * Index
 */
try {

	$events = GoogleCalendar::eventList();

	// print_r($events);

} catch (Exception $e) {
	
	switch ($e->getCode()) {
		case '403':
			echo 'Access Denied: You don\'t have permissions';
			break;
		
		default:
			echo $e->getMessage();
			break;
	}

	exit;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>Google API - Calendar</title>
</head>
<body>

	<h3><a href="./">Google API</a> - Calendar</h3>

	<ul>
		<li><a href="?op=addedit">Add&Edit an Event</a></li>
	</ul>

	<hr/>

	<ul>
		<?php foreach ((array)$events as $key => $event): ?>
			<li><?=$event['title']?>
			<ul>
				<li><?=$event['start']?></li>
				<li><?=$event['end']?></li>	
			</ul>
			</li>
		<?php endforeach ?>
	</ul>

</body>
</html>





