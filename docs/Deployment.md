# HOW TO DEPLOY

Upload the release ZIP to:
/home/releases

The application can be installed with the following commands executed in SSH on the server:

```` sh
export APP_NAME=enviro-hub
export APP_RELEASE=$APP_NAME_v0.1.0

export APP_DEST=/var/www/$APP_NAME/ && \
export RELEASE_ZIP=/home/releases/$APP_NAME/$APP_RELEASE.zip && \
[ -e $RELEASE_ZIP ] && \
rm -rf $APP_DEST && \
unzip $RELEASE_ZIP -d $APP_DEST && \
chmod -R 755 $APP_DEST && \
cd $APP_DEST && \
php bin/console cache:clear --no-warmup --env=prod && \
php bin/console doctrine:migrations:migrate --no-interaction --env=prod && \
php bin/console assets:install public --symlink --env=prod
````
