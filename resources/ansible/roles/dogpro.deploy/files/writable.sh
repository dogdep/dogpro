#!/bin/bash

[ -a $3  ] || mkdir -p $3

setfacl -R -m u:"$1":rwX -m u:"$2":rwX "$3"
setfacl -dR -m u:"$1":rwX -m u:"$2":rwX "$3"
