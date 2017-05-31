# gravity-spy
This is a gravity-spy image classifier using TensorFlow

# Referrence used in this code
- https://codelabs.developers.google.com/codelabs/tensorflow-for-poets/?utm_campaign=chrome_series_machinelearning_063016&utm_source=gdev&utm_medium=yt-desc#0

## Requirements
- Docker
- Python
- Jupyter Notebook
- TensorFlow

## Installentions
1. Python: https://www.python.org/downloads/
2. Docker: https://www.docker.com/community-edition. Check if you have installed Docker with:
`docker run hello-world`
and you shuld get
`Hello from Docker!
This message shows that your installation appears to be working correctly.
...`
3. Install/Run image of TensorFlow inside Docker:
`docker run -it tensorflow/tensorflow:1.1.0 bash`
To test if TensorFlow is installed and running correctly run this code: 
`import tensorflow as tf
hello = tf.constant('Hello, TensorFlow!')
sess = tf.Session() # It will print some warnings here.
print(sess.run(hello))`
You should get: `Hello TensorFlow!`
4. Run docker. First create new folder called `tf_files` and then run this command to run Docker:
`docker run -it \
  --publish 6006:6006 \
  --volume ${HOME}/tf_files:/tf_files \
  --workdir /tf_files \
  tensorflow/tensorflow:1.1.0 bash`
  Your prompt should change to this: `root@xxxxxxxxx:/tf_files#`
  
