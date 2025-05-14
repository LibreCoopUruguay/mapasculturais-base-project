#!/bin/bash
git pull --recurse-submodules

#git submodule update
git submodule update --remote --merge

docker compose build --no-cache --pull

./stop.sh
./start.sh
