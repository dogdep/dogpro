#!/bin/bash

mkdir -p "$1/$3" && chown "$4" "$1/$3"
rm -rf "$2/$3"
ln -nfs "$1/$3" "$2/$3"
