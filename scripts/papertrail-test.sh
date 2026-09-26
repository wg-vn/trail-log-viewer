#!/bin/sh

curl -v --location --request POST \
    "https://logs.collector.na-01.cloud.solarwinds.com/v1/logs" \
    --header 'Content-Type: application/octet-stream' \
    --header "Authorization: Bearer ${PAPERTRAIL_TOKEN}" \
    --data-raw $'hello'
