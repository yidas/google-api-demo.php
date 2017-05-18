<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../components/GoogleAPI.php';
require_once __DIR__ . '/../components/GoogleApiModel.php';

// Set Client with accessToken(whether or not)
$client = GoogleAPI::setClient()
	->setAccessToken(GoogleApiModel::getToken())
	->getClient();
	
if (!GoogleAPI::isAuth()) {
	
	header('Location: ./');
}

/**
 * Google Drive Service
 */
$service = GoogleAPI::getService('Google_Service_Drive');


try {

	/**
	 * Operation
	 */
	if (isset($_GET['op'])) {
		
		switch ($_GET['op']) {
			case 'upload':

				if (isset($_POST["submit"])) {

					try {

						$files = $_FILES["file_upload"];

						$fileMetadata = new Google_Service_Drive_DriveFile([
							'name' => basename($files['name'])
							]);

						$content = file_get_contents($files["tmp_name"]);

						$file = $service->files->create($fileMetadata, [
							'data' => $content,
							// 'mimeType' => 'image/jpeg',
							'uploadType' => 'multipart',
							'fields' => 'id'
							]);

					} catch (Exception $e) {
						
						throw $e;
					}	
				}

				echo "Operation Success!<br/> Updated ID: {$file->id}<br/> <a href=\"?\">Back to List Page</a>";

				break;
			
			default:
				echo '404 - Bad Operation';
				break;
		}

		return;
	}


	/**
	 * Index List
	 */

	// Print the names and IDs for up to 10 files.
	$optParams = [
	  	'pageSize' => 10,
	  	'fields' => 'nextPageToken, files(id, name)'
	];

	$results = $service->files->listFiles($optParams);

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
	<title>Google API - Drive</title>
</head>
<body>

	<h3><a href="./">Google API</a> - Drive</h3>

	<form action="?op=upload" method="post" enctype="multipart/form-data">
	    Select a file to upload:
	    <input type="file" name="file_upload">
	    <input type="submit" value="Upload" name="submit">
	</form>

	<hr/>

	<?php if (count($results->getFiles()) == 0): ?>
		No Result: Your Google Drive have no record.
	<?php else: ?>
		<ul>
		<?php foreach ($results->getFiles() as $file): ?>
			<li><?=printf("%s (%s)\n", $file->getName(), $file->getId())?></li>
		<?php endforeach ?>
		</ul>
	<?php endif ?>

</body>
</html>