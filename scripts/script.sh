#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

SCRIPT_STEP_SCRIPTS_PATH="$(pwd)/scripts/steps/script"
echo 'Running unit test';
./vendor/phpunit/phpunit/phpunit  app/code/Elementary/EmployeesManager/Test/Unit/Model/CustomerEmployee/TestModel.php
echo 'Functional test...';
./vendor/bin/mftf run:test AdminLoginTest --remove

#cd $MAGENTO_DIR
#${SCRIPT_STEP_SCRIPTS_PATH}/phpcs.sh
#${SCRIPT_STEP_SCRIPTS_PATH}/phpmd.sh
#${SCRIPT_STEP_SCRIPTS_PATH}/phpcpd.sh
