#!/bin/sh

DOCKER_STOREFRONT_CI_REPOSITORY_TAG=$1
IMAGE_EXISTS=$2

if [ "$IMAGE_EXISTS" = "false" ]; then
    echo "Image not found. Building without cache."
    docker image build \
        --tag ${DOCKER_STOREFRONT_CI_REPOSITORY_TAG} \
        --target development \
        --no-cache \
        --compress \
        -f ./project-base/storefront/docker/ci.Dockerfile \
        ./project-base/storefront
else
    echo "Image found. Building with cache."
    docker image build \
        --tag ${DOCKER_STOREFRONT_CI_REPOSITORY_TAG} \
        --target development \
        --compress \
        -f ./project-base/storefront/docker/ci.Dockerfile \
        ./project-base/storefront
fi
