#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

SCRIPT_STEP_SCRIPTS_PATH="$(pwd)/scripts/steps/script"
echo 'Running unit test';
./vendor/phpunit/phpunit/phpunit  app/code/Elementary/EmployeesManager/Test/Unit/Model/CustomerEmployee/TestModel.php
echo 'Functional test...';
apt-get update &&  apt install default-jdk
curl -O http://selenium-release.storage.googleapis.com/3.14/selenium-server-standalone-3.14.0.jar
java -Dwebdriver.chrome.driver=chromedriver -jar selenium-server-standalone-3.14.0.jar
composer require magento/magento2-functional-testing-framework
composer update
vendor/bin/mftf generate:tests

./vendor/bin/mftf build:project
cp dev/tests/acceptance/.htaccess.sample dev/tests/acceptance/.htaccess 2>/dev/null || :
./vendor/bin/mftf run:test AdminLoginTest --remove

#cd $MAGENTO_DIR
#${SCRIPT_STEP_SCRIPTS_PATH}/phpcs.sh
#${SCRIPT_STEP_SCRIPTS_PATH}/phpmd.sh
#${SCRIPT_STEP_SCRIPTS_PATH}/phpcpd.sh
