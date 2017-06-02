<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Galaxy part 2</title>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
</head>
<body>
<?php

// convert images from png to jpg
$shellCommand1 = shell_exec('mogrify -format jpg testImages/*/*.png');
// delete png images as we will not need them
$shellCommand2 = shell_exec('rm testImages/*/*.png');

$i = 1;

$dir = 'testImages' . DIRECTORY_SEPARATOR;
$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);

$classes = [];
$classScore = [];
$imgs = [];

// classifier
foreach ($files as $fileName => $file) {
    // if ($i == 6) {
    // 	break;
    // }
    if (!$file->isDir()) {
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
        $className = trim(preg_replace("/\([^)]+\)/", "", $result));
        array_push($classes, $className);
        $score =  number_format((float)preg_replace("/[^0-9.]+/", "", $result), 2, '.', '');
        
        array_push($classScore, $score);

        // echo "<pre>";
        // print_r($classes);
        // echo "</pre>";
        // echo "<pre>";
        // print_r($classScore);
        // echo "</pre>";
    }

    if ($i % 4 == 0) {
        $average = array_sum($classScore) / count($classScore);
        $unique = count(array_unique($classes));

        $subject =  preg_replace("/[^0-9]+/", "", $info->getPath());
        // if all classes are the same class and the average is more then 50% then store it OR we have one image that is more then 90% sure of a class
        if ($unique == 1 && $average > 0.50 || max($classScore) > 0.9) {
            $maxes = array_keys($classScore, max($classScore));
            $selectedClassName = $classes[$maxes[0]];
            echo "<pre>";
            print_r($selectedClassName);
            echo "</pre>";

            $storedResults = json_decode(file_get_contents("results/results.json"), true);

            // if we dont have stored those set of images store them in the array and in docker
            if (!array_key_exists($subject, $storedResults)) {
                // store imgs inside docker
                foreach ($imgs as $img) {
                    // copying the imgs from testImages to Trainset/classFolder
                    shell_exec('cp testImages/' . $subject . '/' . $img . ' tf_files/Trainset/' . $selectedClassName . '/' . $img);
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
                $storedResults[$subject] = $selectedClassName;
                file_put_contents("results/results.json", json_encode($storedResults));
            }
        } else {
            $buildLink = '';
            for ($i=0; $i < count($imgs); $i++) {
                $buildLink .= 'img' . $i . '=' . $imgs[$i] . "&";
            }
            echo "<h3>Help algorithm to classify those 4 set of images:</h3>";
            echo "<a href='manuel.php?" . $buildLink . "class=blip&subject=" . $subject . "' target='_blank'>blip</a><br>";
            echo "<a href='manuel.php?" . $buildLink . "class=whistle&subject=" . $subject . "' target='_blank'>whistle</a><br>";
            echo "<a href='manuel.php?" . $buildLink . "class=koifish&subject=" . $subject . "' target='_blank'>koifish</a><br>";
            echo "<a href='manuel.php?" . $buildLink . "class=power&subject=" . $subject . "' target='_blank'>power</a><br>";
            echo "<a href='manuel.php?" . $buildLink . "class=violin&subject=" . $subject . "' target='_blank'>violin</a><br>";
            echo "<a href='manuel.php?" . $buildLink . "class=none&subject=" . $subject . "' target='_blank'>none</a><br>";
            $buildLink ='';
        }

        // null the arrays and prepare for the next folder of images
        $classes = [];
        $classScore = [];
        $imgs = [];
        echo "<hr>";
    }

    // increse number if its a file
    if (!$file->isDir()) {
        $i++;
    }
}
?>

<!-- COMMENT THIS CODE IF YOU WANT TO STOP THE LOOP -->
<script type="text/javascript">
// $(window).on('load', function () {
//     window.location = 'gravity.php';
// });
</script>
</body>
</html>