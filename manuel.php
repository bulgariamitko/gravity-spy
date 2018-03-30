<?php

if (!empty($_GET['img0']) && !empty($_GET['img1']) && !empty($_GET['img2']) && !empty($_GET['img3']) && !empty($_GET['class']) && !empty($_GET['subject'])) {
	$storedResults = json_decode(file_get_contents("results/results.json"), true);

	// if we dont have stored those set of images store them in the array and in docker
	if (!array_key_exists($_GET['subject'], $storedResults)) {
		// store imgs inside docker
		// copying the imgs from testImages to Trainset/classFolder
		shell_exec('cp testImages/' . $_GET['subject'] . '/' . $_GET['img0'] . ' tf_files/Trainset/' . $_GET['class'] . '/' . $_GET['img0']);
		shell_exec('cp testImages/' . $_GET['subject'] . '/' . $_GET['img1'] . ' tf_files/Trainset/' . $_GET['class'] . '/' . $_GET['img1']);
		shell_exec('cp testImages/' . $_GET['subject'] . '/' . $_GET['img2'] . ' tf_files/Trainset/' . $_GET['class'] . '/' . $_GET['img2']);
		shell_exec('cp testImages/' . $_GET['subject'] . '/' . $_GET['img3'] . ' tf_files/Trainset/' . $_GET['class'] . '/' . $_GET['img3']);

		// train the algorithm
		// $shell = shell_exec('cd tf_files/; python retrain.py \
		//   --bottleneck_dir=bottlenecks \
		//   --how_many_training_steps=500 \
		//   --model_dir=inception \
		//   --summaries_dir=training_summaries/basic \
		//   --output_graph=retrained_graph.pb \
		//   --output_labels=retrained_labels.txt \
		//   --image_dir=Trainset');
		
		// echo "<pre>";
		// print_r($shell);
		// echo "</pre>";

		// store subjects inside a json file
		$storedResults[$_GET['subject']] = $_GET['class'];
		file_put_contents("results/results.json", json_encode($storedResults));
		echo "<h1>DONE!</h1>";
	}
}