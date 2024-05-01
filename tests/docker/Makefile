# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

# Setup options
NO_CACHE ?= false

# If NO_CACHE option is true, set the __NO_CACHE_FLAG
ifeq ($(NO_CACHE), true)
	__NO_CACHE_FLAG = --no-cache
endif

# Registry and image names
REGISTRY = sugarcrm
KARMA_IMAGE = ea-karma
THORN_IMAGE = ea-thorn

.PHONY: help
help:
	@echo "Example:"
	@echo " NO_CACHE=true make build-thorn"
	@echo ""
	@echo "Options:"
	@echo " NO_CACHE=                 Building an image from scratch without using cache"
	@echo ""
	@echo "TARGETS:"
	@echo " build-all:                Build ALL containers"
	@echo " build-karma:              Build the Karma Docker Container"
	@echo " build-thorn:              Build the Thorn Docker Container"
	@echo " push-karma:               Push the Karma Docker Container"
	@echo " push-thorn:               Push the Thorn Docker Container"

# Task for updating all images
.PHONY: build-all
build-all: build-karma build-thorn

.PHONY: build-karma
build-karma:
	@echo "Building Karma Image"
	docker build -f Karma.Dockerfile -t $(REGISTRY)/$(KARMA_IMAGE):latest ${__NO_CACHE_FLAG} .

.PHONY: build-thorn
build-thorn:
	@echo "Building Thorn Image"
	docker build -f Thorn.Dockerfile -t $(REGISTRY)/$(THORN_IMAGE):latest ${__NO_CACHE_FLAG} .

.PHONY: push-karma
push-karma:
	@echo "Push Karma Image to Registry"
	docker push $(REGISTRY)/$(KARMA_IMAGE):latest

.PHONY: push-thorn
push-thorn:
	@echo "Push Thorn Image to Registry"
	docker push $(REGISTRY)/$(THORN_IMAGE):latest
