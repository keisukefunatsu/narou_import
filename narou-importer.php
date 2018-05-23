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


// スラッグで分岐させる
function is_parent_slug()
{
    global $post;
    if (!empty($post)) {
        if ($post->post_parent) {
            $post_data = get_post($post->post_parent);
            return $post_data->post_name;
        }
    }
}

// カスタム投稿のブログの時は独自テンプレートを適用
function get_narou_template($template = '')
{
    if (is_post_type_archive('blog')) {
        $template = NAROU_DIR . '/templates/archive-blog.php';
    }
    if (is_front_page() || is_home()){
        $template = NAROU_DIR . '/templates/home.php';
    }

    return $template;
}

add_filter('template_include', 'get_narou_template', 1);
/**
 * スタイルを読み込む
 *
 * @return void
 */
function narou_enqueue_styles() {
	wp_enqueue_style( 'narou_style', NAROU_PATH . '/assets/css/style.css', false, filemtime( NAROU_DIR . '/assets/css/style.css' ) );
}
add_action( 'wp_enqueue_scripts', 'narou_enqueue_styles' );

/**
 * トップは更新日順に並べる
 *
 * @param [type] $query
 * @return void
 */
function narou_orderby_modified( $query ) {
	if( $query->is_main_query() ) {
        if ($query->is_front_page() || $query->is_home()) {
            $query->set('post_type', 'blog');
        }
        // カテゴリ一覧はランダムで表示
		if( $query->is_category() || $query->is_tag() ) {
			$query->set( 'orderby', 'rand' );
		}
	}
}
add_action( 'pre_get_posts', 'narou_orderby_modified' );