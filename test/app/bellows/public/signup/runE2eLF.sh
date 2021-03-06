#!/bin/bash

TESTDIR=`pwd|perl -pi -e 's@/test/app/.*@/test/app@'`

sh $TESTDIR/useTestConfig.sh
sudo -u www-data php $TESTDIR/setupTestEnvironment.php "languageforge.local"
node $TESTDIR/node_modules/protractor/bin/protractor $1 $TESTDIR/protractorConf.languageforge.js --verbose --specs "$TESTDIR/allspecs/e2e/*.spec.js,`find . -wholename "*e2e*.spec.js" -printf "%p,"|perl -pi -e 's/,$//'`"
php $TESTDIR/teardownTestEnvironment.php
sh $TESTDIR/useLiveConfig.sh
