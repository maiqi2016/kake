#! /bin/bash

path=$(cd `dirname $0`; pwd)

# create config files
cp ${path}/common/config/main.php.backup ${path}/common/config/main.php
cp ${path}/common/config/main-local.php.backup ${path}/common/config/main-local.php
cp ${path}/common/config/params.php.backup ${path}/common/config/params.php
cp ${path}/common/config/params-local.php.backup ${path}/common/config/params-local.php

cp ${path}/backend/config/main.php.backup ${path}/backend/config/main.php
cp ${path}/backend/config/main-local.php.backup ${path}/backend/config/main-local.php
cp ${path}/backend/config/params.php.backup ${path}/backend/config/params.php
cp ${path}/backend/config/params-local.php.backup ${path}/backend/config/params-local.php

cp ${path}/frontend/config/main.php.backup ${path}/frontend/config/main.php
cp ${path}/frontend/config/main-local.php.backup ${path}/frontend/config/main-local.php
cp ${path}/frontend/config/params.php.backup ${path}/frontend/config/params.php
cp ${path}/frontend/config/params-local.php.backup ${path}/frontend/config/params-local.php

cp ${path}/console/config/main.php.backup ${path}/console/config/main.php
cp ${path}/console/config/main-local.php.backup ${path}/console/config/main-local.php
cp ${path}/console/config/params.php.backup ${path}/console/config/params.php
cp ${path}/console/config/params-local.php.backup ${path}/console/config/params-local.php

# add write
chmod -R a+w ${path}/common/config/
chmod -R a+w ${path}/console/config/
chmod -R a+w ${path}/mixed/
chmod -R a+w ${path}/backend/runtime/
chmod -R a+w ${path}/frontend/runtime/
mkdir ${path}/backend/web/assets/ 2> /dev/null
chmod -R a+w ${path}/backend/web/assets/
mkdir ${path}/frontend/web/assets/ 2> /dev/null
chmod -R a+w ${path}/frontend/web/assets/

echo
read -p "Please choose environment. [dev/prod]: " env
if [ "${env}" != "dev" -a "${env}" != "prod" ]
then
    alert 31 'Environment must be dev/prod!'
    exit 1
fi

cp ${path}/yii-${env} ${path}/yii
chmod a+x ${path}/yii

cp ${path}/backend/web/index-${env}.php ${path}/backend/web/index.php
cp ${path}/frontend/web/index-${env}.php ${path}/frontend/web/index.php