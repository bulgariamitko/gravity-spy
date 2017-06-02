# Why i did this
Today machine learnig, deep learning and thanks to TensorFlow you dont need anymore to classify images using human and the algorithms that are available are better even the humans if you provide them with the right set of images and teach the algorithm the right way. I want to proove with this repo/code that is it possible to use TensorFlow and PHP to create automation algorithm that is using Reinforcement learning to re-train itself and improve by itself and get better and better of classifing images. I'm happy to help Albert Einstein as well and all the team.

# About
This is a gravity-spy image classifier using TensorFlow for https://www.zooniverse.org/projects/zooniverse/gravity-spy/classify. The Zooniverse is the world’s largest and most popular platform for people-powered research. The project that this repo is about is for Gravity Spy which is trying to prove Albert Einstein who predicted that accelerating masses create ripples that propagate through the fabric of spacetime known as gravitational waves. You can read more about it here: https://www.zooniverse.org/projects/zooniverse/gravity-spy/about/research and also read those research papers here: https://arxiv.org/pdf/1611.04596.pdf and here https://arxiv.org/pdf/1705.00034.pdf

## Referrence used in this code
- https://codelabs.developers.google.com/codelabs/tensorflow-for-poets/?utm_campaign=chrome_series_machinelearning_063016&utm_source=gdev&utm_medium=yt-desc#0

## Requirements
- Docker
- Python
- Jupyter Notebook
- TensorFlow
- Imagemagick

## Installations
1. Python: https://www.python.org/downloads/
2. Imagemagick: https://www.imagemagick.org/script/download.php
3. Docker: https://www.docker.com/community-edition. Check if you have installed Docker with:
`docker run hello-world`
and you shuld get
```
Hello from Docker!
This message shows that your installation appears to be working correctly.
...
```
4. Install/Run image of TensorFlow inside Docker:
`docker run -it tensorflow/tensorflow:1.1.0 bash`
To test if TensorFlow is installed and running correctly run this code: 
```
import tensorflow as tf
hello = tf.constant('Hello, TensorFlow!')
sess = tf.Session() # It will print some warnings here.
print(sess.run(hello))
```
You should get: `Hello TensorFlow!`
5. Run docker. First create new folder called `tf_files` and then run this command to run Docker:
```
docker run -it \
  --publish 6006:6006 \
  --volume ${HOME}/tf_files:/tf_files \
  --workdir /tf_files \
  tensorflow/tensorflow:1.1.0 bash
```
  Your prompt should change to this: `root@xxxxxxxxx:/tf_files#` => xxxxxxxxx is the `containerId`
  
## Prepare, load images and train algorithm
We will use images from https://www.zooniverse.org/projects/zooniverse/gravity-spy/classify and first when you load this page without registration you will have 3 classifiers: `Blip`, `Whistle`, `None of the above`
1. Put at least 30 images from any category into a folder. I called my folder `Trainset`, but you can call it anything you want. The structure will be something like /tf_files/Trainset/blip/[images].png
2. Convert the images from png to jpg using this command, but in order to do this you need to be inside the folder of images: `mogrify -format jpg *.png`
3. Remove the png images as we dont need them: rm *.png
4. Send /Trainset to docker cointainer docker cp /tf_files/Trainset `containerId`:/tf_files/Trainset [i had some issue with this step, but it should work]
5. Download retrain.py in order to train algorithm: `curl -O https://raw.githubusercontent.com/tensorflow/tensorflow/r1.1/tensorflow/examples/image_retraining/retrain.py`
6. OPTIONAL STEP: In order to see how the algorithm is trained you need to run this code: `tensorboard --logdir training_summaries &`
7. Train the algorithm:
```
python retrain.py \
  --bottleneck_dir=bottlenecks \
  --how_many_training_steps=500 \
  --model_dir=inception \
  --summaries_dir=training_summaries/basic \
  --output_graph=retrained_graph.pb \
  --output_labels=retrained_labels.txt \
  --image_dir=Trainset
```
8. Download `tf_files` from docker dontainer to local machine: docker cp `containerId`:/tf_files /tf_files [i had some issue with this step, but it should work]
9. Download the python code for labeling new images: `curl -L https://goo.gl/3lTKZs > label_image.py`

## Use the newly trained algorithm
- Locate the /tf_files inside your local machine and use: `python label_image.py Trainset/blip/[someimage].jpg`. As a result you should get that this image is blip


## Reinforcement learning
- When we are sure an image is a given class, that means when all 4 images are more then 50% sure to be of a specific class OR we have an image that is more then 90% sure that is of a curtain class, then add it to the Trainset/[the class that the algorithm have desided those 4 images have to go]
- After that train the algorithm again with the newly added images in the Trainset

# gravity.php
The perpose of this file to collect images from https://www.zooniverse.org/projects/zooniverse/gravity-spy/ website and put them into folders with the name of the `subject`. The subject is used to group 4 images of the same type in one. We are using this subject number and we are creating 10 folders with each folder having inside 4 images which all images should be the same class. The only difference of those images are that they are of different measuring size, but they are all belong to one class. Code flow:
1. Using Guzzle to get all 40 images grouping them by there subject number
2. Deleting all images and folders in the folder 'testImages'
3. Saving all 40 images in 10 folders with the name of the subject and each folder containing 4 images.

# gravity2.php
This file is the second stage in the API im building of automatically providing the correct classification using deep learning with TensorFlow. Code flow:
1. Converting all images from png to jpg as all images have to be in jpg format
2. Deleting all png images as we dont need them anymore
3. Displaying every image and calculating using the algorithm what class this image belong to
4. At every 4 images (the whole folder) decide whatever those 4 images belong to a class or not. The desition is base on whatever all images belong to 1 class and the averange proccent of all 4 images is more then 50% OR if the algorithm decide that there is one image that is more then 90% of a certain class. Then it is copying all 4 images to the folder tf_files/Trainset/[the class the algorithm decided this image belongs]
5. Put all subjects into one json file as an array so when there is the same subject the code is not retraining again on the same images. The file is located at results/results.json

# sendData.php
This file is used for sending the data to the server. This is very important page and when you have researched some subjects and assine them to there current class you can use this file to send the data to the server. The file is using curl, but it will be good if i do it using Guzzle as i was not able to see how to send the raw json data with the request. Code flow:
1. Checking which subjects are NOT send to the server already
2. Send data using curl to the server with the current date and time, subject ID and class name
3. When the data is successfully sended store the subject and class name to the json file results/sendResults.json

## Results (part from train #2)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/06ff6f06-56d3-4ac6-b184-488fe5d4f1c8.png' width='200'>
- blip (score = 0.90396)
- none (score = 0.04392)
- koifish (score = 0.03582)
- whistle (score = 0.01581)
- power (score = 0.00035)
- violin (score = 0.00013)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/0bfcbc25-adbc-4e95-8da6-4545c31be37b.png' width='200'>
- blip (score = 0.77283)
- koifish (score = 0.13899)
- none (score = 0.07771)
- whistle (score = 0.00784)
- power (score = 0.00147)
- violin (score = 0.00116)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/0db6de5a-b0c2-46cd-b1e5-065f05a0294f.png' width='200'>
- none (score = 0.61142)
- blip (score = 0.21862)
- koifish (score = 0.13199)
- whistle (score = 0.03253)
- power (score = 0.00289)
- violin (score = 0.00254)
