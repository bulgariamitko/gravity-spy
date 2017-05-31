# gravity-spy
This is a gravity-spy image classifier using TensorFlow

# Referrence used in this code
- https://codelabs.developers.google.com/codelabs/tensorflow-for-poets/?utm_campaign=chrome_series_machinelearning_063016&utm_source=gdev&utm_medium=yt-desc#0

## Requirements
- Docker
- Python
- Jupyter Notebook
- TensorFlow
- Imagemagick

## Installentions
1. Python: https://www.python.org/downloads/
2. Imagemagick: https://www.imagemagick.org/script/download.php
3. Docker: https://www.docker.com/community-edition. Check if you have installed Docker with:
`docker run hello-world`
and you shuld get
`Hello from Docker!
This message shows that your installation appears to be working correctly.
...`
4. Install/Run image of TensorFlow inside Docker:
`docker run -it tensorflow/tensorflow:1.1.0 bash`
To test if TensorFlow is installed and running correctly run this code: 
`import tensorflow as tf
hello = tf.constant('Hello, TensorFlow!')
sess = tf.Session() # It will print some warnings here.
print(sess.run(hello))`
You should get: `Hello TensorFlow!`
5. Run docker. First create new folder called `tf_files` and then run this command to run Docker:
`docker run -it \
  --publish 6006:6006 \
  --volume ${HOME}/tf_files:/tf_files \
  --workdir /tf_files \
  tensorflow/tensorflow:1.1.0 bash`
  Your prompt should change to this: `root@xxxxxxxxx:/tf_files#`
  
## Prepare, load images and train algorithm
We will use images from https://www.zooniverse.org/projects/zooniverse/gravity-spy/classify and first when you load this page without registration you will have 3 classifiers: `Blip`, `Whistle`, `None of the above`
1. Put at least 30 images from any category into a folder. I called my folder `Galaxies`, but you can call it anything you want. The structure will be something like /tf_files/galaxies/blip/[images].png
2. Convert the images from png to jpg using this command, but in order to do this you need to be inside the folder of images: `mogrify -format jpg *.png`
3. Remove the png images as we dont need them: rm *.png
4. Zipping the folder with all the images in my case the name of the folder is `galaxies`. Create .tar file. So you must end up with a file galaxies.tar, command: `tar -czvf galaxies.tar /galaxies`
5. Upload galaxies.tar to a server. Any server will do as long as you have direct access to the file
6. Download the galaxies.tar inside the docker inside terminal using: `curl -O http://path.to.your.server/galaxies.tar`
7. Unzipping the folder: `tar xvf galaxies.tar`
8. Download retrain.py in order to train algorithm: `curl -O https://raw.githubusercontent.com/tensorflow/tensorflow/r1.1/tensorflow/examples/image_retraining/retrain.py`
9. OPTIONAL STEP: In order to see how the algorithm is trained you need to run this code: `tensorboard --logdir training_summaries &`
10. Train the algorithm: `python retrain.py \
  --bottleneck_dir=bottlenecks \
  --how_many_training_steps=500 \
  --model_dir=inception \
  --summaries_dir=training_summaries/basic \
  --output_graph=retrained_graph.pb \
  --output_labels=retrained_labels.txt \
  --image_dir=galaxies`

## Work with the newly trained algorithm
1. Download the python code for labeling new images: `curl -L https://goo.gl/3lTKZs > label_image.py`
2. python label_image.py galaxies/blip/[someimage].jpg
