# Building Mango Docker Images

These instructions assume you are in the sugarcrm/tests/docker directory.

## Modifying Docker Entrypoint Files

As a friendly reminder, you should know that we copy each entrypoint script
into its Docker image root so that we can check for the presence of a /sugarcrm
mount. Whenever you make changes to an entrypoint script your changes won't be
utilized until you rebuild that Docker image. As another friendly reminder, if
you haven't changed anything inside the Dockerfile since the last time you
built it, the Dockerfile won't automatically rebuild because Docker has no way
to detect that you altered the contents of a script that's included in the
Docker image itself.

To forcefully rebuild the docker image after making changes to the entrypoint
script or any other files included in the image, pass `NO_CACHE=true` to the
`make` command or if the image is built with `docker build`, use the
`--no-cache` argument.

e.g. `NO_CACHE=true make build-node` or
`docker build -f Node.Dockerfile -t sugarcrm/node:custom_tag --no-cache .`

## Images

### Karma Image

* Build image with `make build-karma`
* To run Karma tests using the image
```
cd Mango
docker run \
    -v "${PWD}/sugarcrm:/karma" \
    sugarcrm/ea-karma:latest \
    node_modules/gulp/bin/gulp.js karma --ci --coverage --path=/karma --browsers $browsers
```

### Thorn Image

* Built image with `make build-thorn`
* To run Thorn tests using the image
```
cd Mango
SUGAR_URL='http://sugar-url/' # You will need to set this
docker run \
   -v "${PWD}/sugarcrm:/sugarcrm" \
   --net=host \
   sugarcrm/ea-thorn:latest \
   --url "${SUGAR_URL}" \
   --username "admin" \
   --password "asdf" \
   --ci
```

# Pushing Images

If the image is pushed to production with the 'latest' tag, use the Makefile commands

```
make build-${name} # name can be node, karma or thorn
```

If the image is pushed to registry with a custom tag,
```
docker push sugarcrm/image_name:tag
```
