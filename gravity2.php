<?php

// convert images from png to jpg
$shellCommand1 = shell_exec('mogrify -format jpg testImages/*/*.png');
// delete png images as we will not need them
$shellCommand2 = shell_exec('rm testImages/*/*.png');

$i = 0;

$dir = 'testImages' . DIRECTORY_SEPARATOR;
$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);

$classes = [];
$classScore = [];
$imgs = [];

// classifier
foreach($files as $fileName => $file) {
	if ($i == 6) {
		break;
	}
	if ($i % 4 == 0) {
		$average = array_sum($classScore) / count($classScore);
		$unique = count(array_unique($classes));

		if ($unique == 1 && $average > 0.50) {
			$subject =  preg_replace("/[^0-9]+/","", $info->getPath());
			$storeArray = [$subject => $classes[0]];

			$storedResults = json_decode(file_get_contents("results/results.json"), true);

			// if we dont have stored those set of images store them in the array and in docker
			if (!array_key_exists($subject, $storedResults)) {
				// store imgs inside docker
				foreach ($imgs as $img) {
					// copying the imgs from testImages to Trainset/classFolder
					shell_exec('cp testImages/' . $subject . '/' . $img . ' tf_files/Trainset/' . $classes[0] . '/' . $img);
				}

				// train the algorithm
				$shell = shell_exec('cd tf_files/; python retrain.py \
				  --bottleneck_dir=bottlenecks \
				  --how_many_training_steps=500 \
				  --model_dir=inception \
				  --summaries_dir=training_summaries/basic \
				  --output_graph=retrained_graph.pb \
				  --output_labels=retrained_labels.txt \
				  --image_dir=Trainset');
				
				echo "<pre>";
				print_r($shell);
				echo "</pre>";

				// store subjects inside a json file
				$storedResults[$subject] = $classes[0];
				file_put_contents("results/results.json", json_encode($storedResults));

			}

		}

		// null the arrays and prepare for the next folder of images
		$classes = [];
		$classScore = [];
		$imgs = [];
	}
    if (!$file->isDir()){
    	$info = new SplFileInfo($fileName);
    	$fileName = $info->getFilename();

    	// store file into the array of 4 files
    	array_push($imgs, $fileName);

    	// tensorflow algorithm
    	$changedFile = str_replace('jpg', 'png', $fileName);
		echo "<div><img src='https://panoptes-uploads.zooniverse.org/production/subject_location/" . $changedFile . "' width='200'" . $changedFile . "</div>";
		// classifier
		$shellCommand3 = shell_exec('cd tf_files/; python label_image.py ../' . $info->getPath() . '/' . $fileName);
		echo "<pre>";
		print_r($shellCommand3);
		echo "</pre>";

		// getting the result as CLASS and SCORE
		$result = explode("\n", $shellCommand3)[0];
        $className = trim(preg_replace("/\([^)]+\)/","", $result));
        array_push($classes, $className);
        $score =  number_format((float)preg_replace("/[^0-9.]+/","", $result), 2, '.', '');
        
        array_push($classScore, $score);

        // echo "<pre>";
        // print_r($classes);
        // echo "</pre>";
        // echo "<pre>";
        // print_r($classScore);
        // echo "</pre>";
    }
    $i++;
}