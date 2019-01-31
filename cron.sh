##
#cd pwd
#su -c '/usr/bin/php /var/www/html/wp-content/plugins/bookeasy/api/sync.php' -s /bin/bash www-data

CURRENT_DIR=$(pwd)
SCRIPT_DIR=$(dirname $0)
RUN_FILE="$SCRIPT_DIR/../../uploads/bookeasy.sync"

if [ ! -f $RUN_FILE ]
then
    RUN_FILE="$SCRIPT_DIR/../../wp-content/uploads/bookeasy.sync"
fi

if [  -f $RUN_FILE ]
then
    EMAIL=$(<$RUN_FILE)
    rm -f $RUN_FILE
    su -c "/usr/bin/php /var/www/html/wp-content/plugins/bookeasy/api/sync.php -e'$EMAIL'" -s /bin/bash www-data

fi

