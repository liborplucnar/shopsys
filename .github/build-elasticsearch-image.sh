#!/bin/sh

DOCKER_ELASTICSEARCH_REPOSITORY_TAG=$1
IMAGE_EXISTS=$2

if [ "$IMAGE_EXISTS" = "false" ]; then
    echo "Image not found. Building without cache."
    docker image build \
        --tag ${DOCKER_ELASTICSEARCH_REPOSITORY_TAG} \
        --no-cache \
        --compress \
        -f project-base/app/docker/elasticsearch/Dockerfile \
        .
else
    echo "Image found. Building with cache."
    docker image build \
        --tag ${DOCKER_ELASTICSEARCH_REPOSITORY_TAG} \
        --compress \
        -f project-base/app/docker/elasticsearch/Dockerfile \
        .
fi
