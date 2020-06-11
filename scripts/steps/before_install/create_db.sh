#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

mysql -uroot -e '
    SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION;
    CREATE DATABASE '${DB}';
'
mysql -e "CREATE USER magento@localhost IDENTIFIED BY magento;"
mysql -e "GRANT ALL PRIVILEGES ON magento.* TO 'magento'@'localhost' IDENTIFIED BY 'magento';"
mysql -e "FLUSH PRIVILEGES;"