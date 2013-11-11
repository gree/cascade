<?php
/**
 *  Cascade定数値の設定
 */
define('CASCADE_SRC_ROOT',        dirname(dirname(__FILE__)).'/class');
define('CASCADE_SKEL_DIR_PATH',   dirname(dirname(__FILE__)).'/skel');

/**
 *  テストで利用する定数値の設定
 */
define('CASCADE_SRC_TEST_ROOT',    dirname(__FILE__).'/class');
define('CASCADE_CONF_TEST_ROOT',   dirname(__FILE__).'/conf');

$is_ci     = (getenv("CI") == "true");
$is_travis = (getenv("TRAVIS") == "true");

if ($is_ci && $is_travis) {
    /**
     * NOTE: read travis specific config here.
     */
    define('CASCADE_CONFIG_DIR_PATH', dirname(__FILE__) .'/etc');
} else {
    define('CASCADE_CONFIG_DIR_PATH', dirname(dirname(__FILE__)).'/etc');
}


// PHPファイルの読み込み
require_once CASCADE_SRC_ROOT.'/Cascade.php';
date_default_timezone_set('Asia/Tokyo');
