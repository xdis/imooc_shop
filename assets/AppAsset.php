<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl  = '@web';
    public $css      = [
        'app/css/main.css',
        'app/css/red.css',
        'app/css/owl.carousel.css',
        'app/css/owl.transitions.css',
        'app/css/animate.min.css',
        'app/css/font-awesome.min.css',
    ];
    public $js       = [
        'app/js/jquery-1.10.2.min.js',
        'app/js/jquery-migrate-1.2.1.js',
        'app/js/bootstrap.min.js',
        'app/js/gmap3.min.js',
        'app/js/bootstrap-hover-dropdown.min.js',
        'app/js/owl.carousel.min.js',
        'app/js/css_browser_selector.min.js',
        'app/js/echo.min.js',
        'app/js/jquery.easing-1.3.min.js',
        'app/js/bootstrap-slider.min.js',
        'app/js/jquery.raty.min.js',
        'app/js/jquery.prettyPhoto.min.js',
        'app/js/jquery.customSelect.min.js',
        'app/js/wow.min.js',
        'app/js/scripts.js',
        ['app/js/html5shiv.js', 'condition' => 'lte IE9', 'position' => \yii\web\View::POS_HEAD],
        ['app/js/respond.min.js', 'condition' => 'lte IE9', 'position' => \yii\web\View::POS_HEAD],

    ];
    public $depends  = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
