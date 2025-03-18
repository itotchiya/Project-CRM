<?php

$PRODUCT = 'worksuite-new';
$ENVATO_ID = 20052522;
$PRODUCT_URL = 'https://1.envato.market/worksuite';
$UPDATE_DOMAIN = 'https://froiden-update-hub.s3.ap-south-1.amazonaws.com';
$VERIFY_DOMAIN = 'https://envato.froid.works';

return [

    /*
     * Model name of where purchase code is stored
     */
    'setting' => \App\Models\GlobalSetting::class,

    /*
     * Add redirect route here route('login') will be used
     */
    'redirectRoute' => 'login',

    'envato_item_id' => $ENVATO_ID,

    'envato_product_name' => $PRODUCT,

    'envato_product_url' => $PRODUCT_URL,

    'plugins_url' => $VERIFY_DOMAIN . '/plugins/' . $ENVATO_ID,

    /*
    * Temp folder to store update before to install it.
    */
    'tmp_path' => storage_path() . '/app',
    /*
    * URL where your updates are stored ( e.g. for a folder named 'updates', under http://site.com/yourapp ).
    */
    'update_baseurl' => $UPDATE_DOMAIN . '/' . $PRODUCT,
    /*
    * URL to verify your purchase code
    */
    'verify_url' => $VERIFY_DOMAIN . '/verify-purchase',

    'latest_version_file' => $VERIFY_DOMAIN . '/latest-version/' . $ENVATO_ID,

    /*
     * Update log file
     */
    'updater_file_path' => $UPDATE_DOMAIN . '/' . $PRODUCT . '/laraupdater.json',

    /*
    * Set a middleware for the route: updater.update
    * Only 'auth' NOT works (manage security using 'allow_users_id' configuration)
    */
    'middleware' => ['web', 'auth'],

    /*
    * Set which users can perform an update;
    * This parameter accepts: ARRAY(user_id) ,or FALSE => for example: [1]  OR  [1,3,0]  OR  false
    * Generally, ADMIN have user_id=1; set FALSE to disable this check (not recommended)
    */

    'allow_users_id' => false,
    /*
     * Change Log URL
     */
    'versionLog' => $VERIFY_DOMAIN . '/version-log/' . $PRODUCT
];
