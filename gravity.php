<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Galaxy - Machine Learning magic</title>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
</head>
<body>
<?php
if (!file_exists('testImages')) {
    mkdir('testImages', 0777, true);
}

$storedResults = json_decode(file_get_contents("results/results.json"), true);
$sendResults = json_decode(file_get_contents("results/sendResults.json"), true);
$result = array_diff_assoc($storedResults, $sendResults);
// sending data on line 164
echo "<h1>Will send data after " . (1000 - count($result)) . " images to classify</h1>";

$dir = 'testImages' . DIRECTORY_SEPARATOR;
if (!is_dir($dir)) {
    header("Location: gravity.php");
}

// convert images from png to jpg
$shellCommand1 = shell_exec('mogrify -format jpg testImages/*/*.png');
// delete png images as we will not need them
$shellCommand2 = shell_exec('rm testImages/*/*.png');

$i = 1;

$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
             RecursiveIteratorIterator::CHILD_FIRST);

$subjects = [];
$imgs = [];

// find already sended subjects
$results = json_decode(file_get_contents("results/results.json"), true);
// find already labaled subjects from zooinverse
$labaled = json_decode(file_get_contents("results/labelsFromZooniverse.json"), true);
// if there is no new subjects then dont retrain the algorithm!!!
$newSubjects = false;

// classifier
foreach ($files as $fileName => $file) {
    $info = new SplFileInfo($fileName);
    $fileName = $info->getFilename();

    // find subject number
    $subject =  preg_replace("/[^0-9]+/", "", $info->getPath());
    if (!$file->isDir() && !array_key_exists($subject, $results)) {
        // new subject found, please train the algorithm:
        $newSubjects = true;
        // store file into the array of 4 files
        array_push($imgs, $fileName);

        // tensorflow algorithm
        $changedFile = str_replace('jpg', 'png', $fileName);
        $linkToImg = 'https://panoptes-uploads.zooniverse.org/production/subject_location/' . $changedFile;

        // classifier
        $shellCommand3 = shell_exec('cd tf_files/; python label_image.py ../' . $info->getPath() . '/' . $fileName);

        echo "<a href='" . $linkToImg . "' target='_blank'>";
            echo "<figure class='figure col-md-3'>";
                echo "<img src='" . $linkToImg . "' class='figure-img img-fluid rounded'>";
                echo "<figcaption class='figure-caption'>" . $changedFile . ' <br>' . preg_replace("/\)/", ")<br>", $shellCommand3) . "</figcaption>";
            echo "</figure>";
        echo "</a>";

        // extract scores
        $re = '/(\w+) \(/';
        preg_match_all($re, $shellCommand3, $classes, PREG_SET_ORDER, 0);

        // extract scores
        $re = '/[0-9]{1}.[0-9]{5}/';
        preg_match_all($re, $shellCommand3, $scores, PREG_SET_ORDER, 0);

        for ($y = 0; $y < count($classes); $y++) { 
            switch ($classes[$y][1]) {
                case 'blip':
                    $subjects['blip'][] = $scores[$y][0];
                    break;
                case 'whistle':
                    $subjects['whistle'][] = $scores[$y][0];
                    break;
                case 'koifish':
                    $subjects['koifish'][] = $scores[$y][0];
                    break;
                case 'powerline60hz':
                    $subjects['powerline60hz'][] = $scores[$y][0];
                    break;
                case 'violin':
                    $subjects['violin'][] = $scores[$y][0];
                    break;
                case 'none':
                    $subjects['none'][] = $scores[$y][0];
                    break;
                default:
                    throw new Exception("No such class/subject " . $classes[$y][1]);
                    break;
            }
        }

        // echo "<pre>";
        // print_r($classes);
        // echo "</pre>";
        // echo "<pre>";
        // print_r($classScore);
        // echo "</pre>";
    }


    if ($i % 4 == 0 && !array_key_exists($subject, $results)) {
        $selectedClassName = '';
        $bestAvgScore = 0;
        foreach ($subjects as $sub => $s) {
            $average = round(array_sum($s) / 4, 2);
            echo "<span>avarage " . $sub . ": <strong>" . $average * 100 . "%</strong> </span>";
            if ($average > $bestAvgScore) {
                $selectedClassName = $sub;
                $bestAvgScore = $average;
            }
        }
        // if the best average from all classes have 70% or more procent, then we are sure this is the right class
        if ($bestAvgScore >= 0.7) {
            // echo Subject + Class + label from zooinverse if any
            echo "<h3>Subject: " . $subject . ", Class: " . strtoupper($selectedClassName) . ", Label from Zooinverse: " . (array_key_exists($subject, $labaled) ? $labaled[$subject] : '') . "</h3>";

            $storedResults = json_decode(file_get_contents("results/results.json"), true);

            // if we dont have stored those set of images store them in the array and in docker
            if (!array_key_exists($subject, $storedResults)) {
                // store imgs inside docker
                foreach ($imgs as $img) {
                    // copying the imgs from testImages to Trainset/classFolder
                    shell_exec('cp testImages/' . $subject . '/' . $img . ' tf_files/Trainset/' . $selectedClassName . '/' . $img);
                }

                // store subjects inside a json file
                $storedResults[$subject] = $selectedClassName;
                file_put_contents("results/results.json", json_encode($storedResults));

                // update already sended subjects
                $results = json_decode(file_get_contents("results/results.json"), true);
                echo "<h3>Algorithm will train in... " . (count($results) % 100) . "/100</h3>";
                // train the algorithm only if there are new subjects found
                if ($newSubjects && count($results) % 100 == 0) {
                    $shell = shell_exec('cd tf_files/; python retrain.py \
                      --bottleneck_dir=bottlenecks \
                      --how_many_training_steps=500 \
                      --model_dir=inception \
                      --summaries_dir=training_summaries/basic \
                      --output_graph=retrained_graph.pb \
                      --output_labels=retrained_labels.txt \
                      --image_dir=Trainset');

                    // generate random 5 char string to name the file
                    $fileName = date('Y-m-d_H-i-s');
                    $newFile = fopen("learning/" . $fileName, "w") or die("Unable to open file!");
                    // save training to file
                    fwrite($newFile, $shell);
                    fclose($myfile);
                    // echo "<pre>";
                    // print_r($shell);
                    // echo "</pre>";
                    // exit;
                }
            }
        } else {
            echo "<h3>Subject: " . $subject . ", Label from Zooinverse: " . (array_key_exists($subject, $labaled) ? $labaled[$subject] : '') . "</h3>";
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
        $subjects = [];
        $imgs = [];
        echo "<hr>";
    }

    // increse number if its a file
    if (!$file->isDir()) {
        $i++;
    }
}

// send data if there are more then 1000 images identifed
if (count($result) > 1000) { ?>
    <script type="text/javascript">
    $(window).on('load', function () {
        window.location = 'sendData.php';
    });
    </script>    
<?php } ?>
<!-- COMMENT THIS CODE IF YOU WANT TO STOP THE LOOP -->
<script type="text/javascript">
$(window).on('load', function () {
    window.location = 'index.php';
});
</script>
</body>
</html>