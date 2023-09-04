#!/bin/sh

chown -R application:application .

exec "$@"
