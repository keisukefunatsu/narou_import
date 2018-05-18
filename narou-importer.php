<?php
/*
Plugin Name: narou importer
Description: なろう小説のデータを格納します
Author: Keiuske Funastu
Version: 1.0
Author URI:
 */


if (!defined('NAROU_DIR')) {
    define('NAROU_DIR', dirname(__FILE__));
}
if (!defined('NAROU_PATH')) {
    define('NAROU_PATH', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)) . '/');
}

// 管理画面を呼び出す
require_once NAROU_DIR . '/admin/init.php';

// 設定項目

function pp($str)
{
    echo '<div style="margin-left:200px;">';
    echo '<pre>';
    print_r($str);
    echo '</pre>';
    echo '</div>';
}


add_action('init', 'create_post_type');
function create_post_type()
{
    register_post_type(
        'blog',
        array(
            'labels' => array(
                'name' => __('blog'),
                'singular_name' => __('blog')
            ),
            'public' => true,
            'has_archive' => true,
        )
    );
}
