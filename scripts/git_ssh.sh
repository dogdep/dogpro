#!/bin/sh
ssh -o StrictHostKeyChecking=no \
    -o IdentitiesOnly=yes \
    -o UserKnownHostsFile=/dev/null \
    -i "$GIT_SSH_KEY" \
     "$@"
