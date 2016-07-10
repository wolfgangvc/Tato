#!/bin/bash
echo "PSR2 Fixin"
./vendor/bin/phpcbf --standard=PSR2 src tests views *.php public/*.php

