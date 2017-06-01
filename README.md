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

## Work with the newly trained algorithm
1. Download the python code for labeling new images: `curl -L https://goo.gl/3lTKZs > label_image.py`
2. Copy all files from Docker /tf_files to your local machine: `docker cp <containerId>:/file/path/within/container /host/path/target` For example when you are inside Docker you will see the prompt will be: root@a0428763b71f: and a0428763b71f is the containerId
3. Locate the /tf_files inside your local machine and use algorithm: `python label_image.py Trainset/blip/[someimage].jpg`

## Reinforcement learning
- When we are sure an image is a given class, that means when all 4 images are more then 80% sure to be of a specific class, then add it to the Trainset.
- After that train the algorithm again with the newly added Trainset
### How to do that
1. Copy the image we are sure it is of a given class to the docker of the given class. Example: `docker cp testImages/fe60d99e-c8ec-4a59-a00b-7c7f44b484a3.jpg b1d858f2edc6:/tf_files`

## Results
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
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/1a4e4fad-0c30-4921-a011-f724d025f2d0.png' width='200'>
- blip (score = 0.80679)
- none (score = 0.10544)
- whistle (score = 0.06856)
- koifish (score = 0.01460)
- power (score = 0.00304)
- violin (score = 0.00158)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/20e5e113-1754-49f3-9947-6ebed7db2391.png' width='200'>
- blip (score = 0.81001)
- koifish (score = 0.10418)
- none (score = 0.05979)
- whistle (score = 0.02315)
- power (score = 0.00152)
- violin (score = 0.00135)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/249af6f3-81c2-402e-9f66-6cdde7a7edbd.png' width='200'>
- blip (score = 0.78913)
- koifish (score = 0.11808)
- none (score = 0.08487)
- whistle (score = 0.00606)
- power (score = 0.00094)
- violin (score = 0.00092)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/2686e31a-922c-4558-92f1-ef50218687be.png' width='200'>
- none (score = 0.57422)
- blip (score = 0.23539)
- whistle (score = 0.12478)
- koifish (score = 0.04983)
- power (score = 0.00797)
- violin (score = 0.00782)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/281a2687-7be5-4c6f-b609-44f61faa2ed9.png' width='200'>
- whistle (score = 0.44252)
- none (score = 0.36375)
- blip (score = 0.09723)
- power (score = 0.06358)
- violin (score = 0.01849)
- koifish (score = 0.01443)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/2b3c57fd-a358-473e-ad00-bc83679d1708.png' width='200'>
- blip (score = 0.87623)
- none (score = 0.08329)
- koifish (score = 0.03599)
- whistle (score = 0.00273)
- power (score = 0.00098)
- violin (score = 0.00078)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/355eeabf-7c36-4dd4-ac13-24eaa9283ead.png' width='200'>
- none (score = 0.53253)
- blip (score = 0.29792)
- whistle (score = 0.12646)
- koifish (score = 0.02827)
- power (score = 0.00820)
- violin (score = 0.00661)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/4a7250d4-c20e-4d80-87e9-c06f0f7f976d.png' width='200'>
- blip (score = 0.64923)
- none (score = 0.20777)
- whistle (score = 0.11672)
- koifish (score = 0.01778)
- power (score = 0.00484)
- violin (score = 0.00365)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/552c6d2a-da3a-4b32-9655-5709ccd1be30.png' width='200'>
- blip (score = 0.71156)
- none (score = 0.17392)
- whistle (score = 0.06595)
- koifish (score = 0.04121)
- power (score = 0.00476)
- violin (score = 0.00259)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/5b9b6973-68c2-4ee1-b536-4b23e5901f91.png' width='200'>
- blip (score = 0.71606)
- none (score = 0.22672)
- koifish (score = 0.03420)
- whistle (score = 0.02123)
- power (score = 0.00125)
- violin (score = 0.00055)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/5caaecc0-352c-49f2-bbc0-3bed2dccda74.png' width='200'>
- blip (score = 0.86006)
- none (score = 0.07630)
- koifish (score = 0.03839)
- whistle (score = 0.02386)
- violin (score = 0.00086)
- power (score = 0.00053)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/5d491ebf-1058-4bcd-ab06-5ab3bd49af33.png' width='200'>
- blip (score = 0.85141)
- none (score = 0.09383)
- koifish (score = 0.02687)
- whistle (score = 0.02617)
- power (score = 0.00096)
- violin (score = 0.00075)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/6ff43f72-9936-4e23-8e45-034dfdd51eb8.png' width='200'>
- whistle (score = 0.91245)
- none (score = 0.05847)
- koifish (score = 0.01374)
- blip (score = 0.00921)
- power (score = 0.00470)
- violin (score = 0.00144)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/73c855d7-3724-4a01-bc1a-900816feea87.png' width='200'>
- blip (score = 0.80747)
- none (score = 0.11984)
- whistle (score = 0.05548)
- koifish (score = 0.01229)
- power (score = 0.00308)
- violin (score = 0.00184)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/78325137-bcb2-4259-9ebb-d6c3c300ffbd.png' width='200'>
- none (score = 0.54301)
- blip (score = 0.30541)
- whistle (score = 0.07247)
- koifish (score = 0.06198)
- violin (score = 0.01046)
- power (score = 0.00667)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/849c7506-5517-4851-8892-929a70eeff5f.png' width='200'>
- blip (score = 0.41514)
- none (score = 0.41338)
- whistle (score = 0.13334)
- koifish (score = 0.01559)
- power (score = 0.01486)
- violin (score = 0.00769)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/93333371-4e84-4cd1-b93c-6ad3472ec567.png' width='200'>
- whistle (score = 0.87082)
- none (score = 0.08218)
- blip (score = 0.01980)
- koifish (score = 0.01217)
- power (score = 0.01148)
- violin (score = 0.00353)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/935d8e7a-1d46-4ec7-afd4-8622fdd71a69.png' width='200'>
- blip (score = 0.86339)
- koifish (score = 0.11040)
- none (score = 0.02304)
- whistle (score = 0.00242)
- violin (score = 0.00041)
- power (score = 0.00034)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/95445a92-0909-4e45-81dd-d6b43f003bcf.png' width='200'>
- blip (score = 0.82947)
- none (score = 0.11027)
- koifish (score = 0.05036)
- whistle (score = 0.00805)
- power (score = 0.00097)
- violin (score = 0.00088)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/9c95b27f-2f78-431f-a661-caaffdcfd442.png' width='200'>
- blip (score = 0.80983)
- koifish (score = 0.14467)
- none (score = 0.04177)
- whistle (score = 0.00186)
- power (score = 0.00095)
- violin (score = 0.00093)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/a00b803c-c1b3-4daf-93cd-c422a3e9768b.png' width='200'>
- blip (score = 0.80970)
- koifish (score = 0.11230)
- none (score = 0.05572)
- whistle (score = 0.02041)
- power (score = 0.00105)
- violin (score = 0.00081)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/a1fe72c4-41d9-401c-bfe7-cc20148b04fe.png' width='200'>
- whistle (score = 0.84379)
- none (score = 0.09940)
- blip (score = 0.02995)
- power (score = 0.01237)
- koifish (score = 0.01200)
- violin (score = 0.00249)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/b6039f76-5554-4985-8056-8b270d52535f.png' width='200'>
- blip (score = 0.83432)
- koifish (score = 0.11830)
- none (score = 0.03581)
- whistle (score = 0.01088)
- violin (score = 0.00039)
- power (score = 0.00032)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/b6cdc5e8-64e3-4e7f-9b58-d10f8d877093.png' width='200'>
- whistle (score = 0.63990)
- violin (score = 0.18833)
- power (score = 0.12696)
- none (score = 0.03147)
- koifish (score = 0.00762)
- blip (score = 0.00572)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/bcb24d2a-9404-46fb-9605-6836a4d76e81.png' width='200'>
- blip (score = 0.72070)
- none (score = 0.13019)
- koifish (score = 0.07554)
- whistle (score = 0.06993)
- power (score = 0.00208)
- violin (score = 0.00156)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/c9eba1e8-69ec-47b4-8b45-d794a581b89e.png' width='200'>
- blip (score = 0.72676)
- none (score = 0.16696)
- whistle (score = 0.07489)
- koifish (score = 0.02527)
- power (score = 0.00377)
- violin (score = 0.00235)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/ce7013ce-a846-4792-819f-a674129c174b.png' width='200'>
- blip (score = 0.75871)
- none (score = 0.13089)
- koifish (score = 0.09529)
- whistle (score = 0.01214)
- power (score = 0.00182)
- violin (score = 0.00115)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/d2b83b46-5874-4701-a1da-ff0806aa56f5.png' width='200'>
- blip (score = 0.45432)
- none (score = 0.36092)
- koifish (score = 0.13287)
- whistle (score = 0.04063)
- power (score = 0.00583)
- violin (score = 0.00542)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/d6d9dd65-9e6f-44a9-8de2-ddf68f3dc3f1.png' width='200'>
- whistle (score = 0.78170)
- power (score = 0.10283)
- violin (score = 0.10053)
- none (score = 0.01008)
- blip (score = 0.00249)
- koifish (score = 0.00236)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/dbc3c09f-3190-4cd8-bf33-8cce03934cc0.png' width='200'>
- power (score = 0.50920)
- whistle (score = 0.23674)
- violin (score = 0.22465)
- none (score = 0.02004)
- blip (score = 0.00828)
- koifish (score = 0.00109)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/e166421b-cbc0-45e5-9891-1985d0b8038a.png' width='200'>
- blip (score = 0.44712)
- none (score = 0.34724)
- whistle (score = 0.14981)
- koifish (score = 0.04413)
- power (score = 0.00744)
- violin (score = 0.00428)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/ec74c98c-d613-44e4-84ef-f750d014172d.png' width='200'>
- blip (score = 0.57195)
- none (score = 0.33411)
- whistle (score = 0.05169)
- koifish (score = 0.03362)
- power (score = 0.00723)
- violin (score = 0.00141)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/f0f21058-c68e-402d-acb7-3c98e618e9e2.png' width='200'>
- blip (score = 0.84244)
- none (score = 0.09984)
- whistle (score = 0.03261)
- koifish (score = 0.02045)
- power (score = 0.00266)
- violin (score = 0.00200)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/f30f3e23-7793-4402-8635-7119405ec4cf.png' width='200'>
- blip (score = 0.84825)
- none (score = 0.07372)
- koifish (score = 0.05216)
- whistle (score = 0.02331)
- power (score = 0.00177)
- violin (score = 0.00079)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/f57be117-f42d-410f-b1c7-dbfd84d3c5fc.png' width='200'>
- blip (score = 0.88242)
- koifish (score = 0.08723)
- none (score = 0.02391)
- whistle (score = 0.00543)
- power (score = 0.00056)
- violin (score = 0.00046)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/f856f3ed-578f-46a8-9596-f0a173ae5a27.png' width='200'>
- whistle (score = 0.67332)
- violin (score = 0.19664)
- power (score = 0.12102)
- none (score = 0.00545)
- blip (score = 0.00267)
- koifish (score = 0.00090)
<img src='https://panoptes-uploads.zooniverse.org/production/subject_location/fe60d99e-c8ec-4a59-a00b-7c7f44b484a3.png' width='200'>
- blip (score = 0.77248)
- none (score = 0.14806)
- whistle (score = 0.05588)
- koifish (score = 0.02079)
- power (score = 0.00183)
- violin (score = 0.00097)
