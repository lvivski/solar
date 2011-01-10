#!/bin/bash
# Clear PHP sessions in custom path
cd ../tmp/session; find -cmin +24 | xargs rm