#!/bin/bash

VERSION=$1
COMPOSER=$2
FOLDER_DIR=$(pwd)

mv $FOLDER_DIR/drupal-$VERSION/* $FOLDER_DIR/
rm -rf $FOLDER_DIR/drupal-$VERSION

$COMPOSER run-script pre-install-cmd
$COMPOSER run-script pre-update-cmd
$COMPOSER install --no-interaction
$COMPOSER run-script post-install-cmd
$COMPOSER run-script post-update-cmd