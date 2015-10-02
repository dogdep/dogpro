#!/bin/bash

mkdir -p `dirname $1`
touch $1 && chown $3 $1
ln -nfs $1 $2
