## Getting Started

You can run Thorn locally on your workstation or in Docker without installing
it locally if that's your preference. To run Thorn exactly as CI runs it,
you'll have to run it in Docker.

# Running Thorn in Docker

## Introduction to Docker

You'll need a basic grasp of Docker and containers in general if you plan to
run Thorn in Docker. Read [this Confluence page](https://sugarcrm.atlassian.net/wiki/display/EA/Introduction+to+Docker)
for a brief introduction to get you started.

Please remember that if you plan to run REST tests in Docker against a locally
installed instance of Sugar on a Mac (running in MAMP, for example) you may not
be able to specify http://localhost because localhost inside the container
might actually point to a virtual machine running in the Mac OS X hypervisor.
You should specify your Mac's IP address in the URL instead, and you can always
test against deployed Honeycomb instances.

## Install Docker

You'll need to install Docker if you haven't already, so download and run the
appropriate Docker installer for your platform:

[Get Docker for Linux](https://docs.docker.com/engine/installation/)  
[Get Docker for Mac](https://download.docker.com/mac/stable/Docker.dmg)  
[Get Docker for
Windows](https://download.docker.com/win/stable/InstallDocker.msi)

## Running Tests in Docker

Start in your fork of Mango, inside the sugarcrm directory:
```
cd /path/to/Mango/sugarcrm
```

You don't need to perform a yarn install because the docker image will do this
for you automatically before running any tests. You *do* however need to mount
your sugarcrm directory inside the docker container as /sugarcrm. You also need
to mount the results directory so that you can view your test results. To start
a docker container with both directories mounted inside and run *all* of the
REST tests:
```
docker run \
    --rm \
    -v /tmp:/tmp/test-rest \
    -v ${PWD}:/sugarcrm \
    sugarcrm/ea-thorn:latest \
        --url "http://your.sugar/instance" \
        --username "admin" \
        --password "" \
        --ci
```
This starts a Docker container and runs the REST tests. The --url argument
points Thorn to your deployed Sugar instance while --username and --password
specify your admin account credentials, so adjust those accordingly.
This will run all of the REST tests that reside in your fork of
Mango on the specified sugar instance. Note that you can pass any other
command-line arguments that are supported by Thorn. Results and failures are
stored in /tmp in this example.

## Executing Arbitrary Commands Inside The Docker Container

To run the Docker container and drop into an interactive root shell with your
local fork of Mango mounted and ready for any sort of interactive work you
might want to do:
```
docker run \
    -it \
    --env DEV=true \
    --rm \
    -v ${PWD}:/sugarcrm \
    sugarcrm/ea-thorn:latest
```
As a matter of fact you can perform a yarn install and use nodejs without ever
installing them on your local workstation. Simply run the container in developer mode, as
shown above, then fire away! All changes persist in your local Mango sugarcrm
directory.

# Running Tests Without Docker

To run all REST tests locally outside of Docker you'll need to install nodejs
and yarn. Once you have nodejs and yarn just perform a yarn install inside
sugarcrm/:
```
yarn install
```
The yarn install actually installs Thorn and all of its nodejs dependencies.
Finally you can execute the following command inside of sugarcrm/ to run your
tests:
```
node_modules/gulp/bin/gulp.js test:rest \
    --url "http://your.sugar/instance" \
    --username "admin" \
    --password "" \
    --ci
```
