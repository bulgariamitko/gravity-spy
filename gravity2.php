<?php

// convert images from png to jpg
$shellCommand1 = shell_exec('mogrify -format jpg testImages/*.png');
// delete png images as we will not need them
$shellCommand2 = shell_exec('rm testImages/*.png');

// guess classifier
$files = scandir('testImages');
foreach ($files as $file) {
	if ($file != '.' && $file != '..') {
		$changedFile = str_replace('jpg', 'png', $file);
		echo "<div><img src='https://panoptes-uploads.zooniverse.org/production/subject_location/" . $changedFile . "' width='200'" . $changedFile . "</div>";
		// guess classifier
		$shellCommand3 = shell_exec('cd tf_files/; python label_image.py ../testImages/' . $file);
		echo "<pre>";
		print_r($shellCommand3);
		echo "</pre>";

		// $lines = explode("\n", $shellCommand3);
		// echo "<pre>";
		// print_r($lines[0]);
		// echo "</pre>";
	}
}