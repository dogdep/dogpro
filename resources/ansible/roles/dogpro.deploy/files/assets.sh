#!/bin/bash

LOG_FILE="$1/build-assets.log"
ASSET_DIR="$1"
ASSET_CACHED_DIR="$2"
echo "[assets] starting build in $ASSET_DIR" >> $LOG_FILE
cd $ASSET_DIR

if [ -f package.json ]; then
  if [ -d $ASSET_CACHED_DIR/node_modules ]; then
    echo "[assets] copying cached node_modules $ASSET_CACHED_DIR/node_modules" >> $LOG_FILE
    cp -r $ASSET_CACHED_DIR/node_modules $ASSET_DIR/node_modules;
  fi

  echo "[assets] running npm install" >> $LOG_FILE
  npm install >> $LOG_FILE

  if [ -f bower.json ]; then
    BOWER_DIR=`test -f .bowerrc && cat .bowerrc | grep -Po '(?<="directory": ")[^"]*' || echo "bower_components"`

    if [ -d "$ASSET_CACHED_DIR/$BOWER_DIR" ]; then
      echo "[assets] copying cached $BOWER_DIR $ASSET_CACHED_DIR/$BOWER_DIR" >> $LOG_FILE
      cp -r "$ASSET_CACHED_DIR/$BOWER_DIR" "$ASSET_DIR/$BOWER_DIR";
    fi

    [ -x "$(command -v bower)" ] || npm install -g bower
    echo "[assets] running bower install" >> $LOG_FILE
    bower install --allow-root >> $LOG_FILE
  fi

  if [ -f gulpfile.js ]; then
    [ -x "$(command -v gulp)" ] || npm install -g gulp
    echo "[assets] running gulp --production" >> $LOG_FILE
    gulp --production >> $LOG_FILE
  fi

  if [ -f gruntfile.js ]; then
    [ -x "$(command -v grunt)" ] || npm install -g grunt-cli
    echo "[assets] running grun release" >> $LOG_FILE
    grunt release >> $LOG_FILE
  fi
fi
