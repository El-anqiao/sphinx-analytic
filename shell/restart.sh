#! /bin/bash

cd /usr/local/sphinx/

kill `cat var/log/analytic.pid`

bin/searchd -c var/etc/analytic.conf
