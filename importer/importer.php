<?php

require_once NAROU_DIR . '/lib/Spyc/Spyc.php';

class NarouImporter
{
    public function __construct()
    {
    }
    public function setup_category($arr)
    {
        $category_names = [];
        $category_ids = [];
        $category_name1 = $arr[':biggenre'];
        if (empty($category_name1)) {
            $category_names[] = 'その他';
        } else {
            $category_names[] = $category_name1;
        }

        $category_name2 = $arr[':original_title'];
        if (empty($category_name2)) {
            $category_names[] = $arr[':short_title'];
        } else {
            $category_names[] = $category_name2;
        }
        $category_name3 = $arr[':author'];
        if (!empty($category_name3)) {
            $category_names[] = $arr[':author'];
        }
        $category_name4 = $arr[':publisher'];
        if (!empty($category_name4)) {
            $category_names[] = $arr[':publisher'];
        }

        foreach ($category_names as $category_name) {
            $category = [
                'cat_name' => $category_name,
                'category_description' => $category_name,
                'category_nicename' => $category_name,
                'category_parent' => ''
            ];
            $term = term_exists($category_name, 'category');
            if ($term !== 0 && $term !== null) {
                $category_id = $term['term_id'];
            } else {
                // カテゴリーを作成
                $category_id = wp_insert_category($category);
            }
            $category_ids[] = $category_id;
        }
        return $category_ids;
    }
    public function import_all()
    {
        $file = file_get_contents(NAROU_DIR . '/test/test_info.yml');
        
        $array = Spyc::YAMLLoad($file);
        // pp($array);
        foreach ($array as $arr) {
            // pp($arr[':title']);
            $keyword = $arr[0];
            $author_message = $arr[':author_message'];
            if (is_array($author_message)) {
                $author_message = implode($author_message);
            }
            $category_ids = $this->setup_category($arr);
            $post = [
                'post_content' => $this->setup_content($arr),
                'post_name' => $arr[':isbn'], // slug
                'post_title' => $arr[':title'], // title
                // 'post_status' => 'draft',
                'post_status' => 'publish',
                'post_type' => 'post',
                'comment_status' => 'closed',
                'post_category' => $category_ids,
                'tags_input' => $keyword,
                'meta_input' => [
                    'author_message' => $author_message,
                    'image_src' => $arr[':image_src'],
                    'author' => $arr[':author'],
                    'author_url' => $arr[':author_url'],
                    'book_link' => $arr[':book_link'],
                    'original_title' => $arr[':original_title'],
                    'ncode' => $arr[':ncode'],

                ]
            ];
            // pp($post);
            // pp($arr);
            // カテゴリーを定義
            $post_id = wp_insert_post($post);
            // 画像があるものは画像を登録する
            if (!empty($arr[':image_src'])) {
                $thumbnail_id = $this->setup_media($arr[':image_src'], $post_id);
                set_post_thumbnail($post_id, $thumbnail_id);
            }
        }
    }
    public function setup_media($url, $post_id)
    {
        $target_url = $url;
        //$_FILESを偽装したデータを作る
        $f = [];
        //適当に名前を決める（乱数でも、日時でも、URLからでも）
        //$f['name'] =  microtime(true) . '.jpg';
        $f['name'] = basename($target_url);
        //一時的に保存する
        $f['tmp_name'] = download_url($target_url);
        //メディアに登録してIDを取得
        $id = media_handle_sideload($f, $post_id);
        // //画像IDからURLを取得
        // $url = wp_get_attachment_url($id);
        return $id;
    }
    public function setup_content($arr)
    {
        $content = $arr[':book_content'];
        $story = $arr[':story'];
        if (is_array($content)) {
            $content = implode($content);
        }
        if (is_array($story)) {
            $story = implode($story);
        }

        $html = '';
        $html .= '<img src="' . $arr[':image_src'] . '">';
        if (!empty($content)) {
            $html .= '<h2>本の説明</h2>';
            $html .= $content;
            $html .= '<p>';
            $html .= '<code>';
            $html .= '引用元：https://syosetu.com' . $arr[':href'];
            $html .= '</code>';
            $html .= '</p>';
        }
        
        if (!empty($story)) {
            $html .= '<h2>作品のあらすじ</h2>';
            $html .= $story;
            $html .= '<p>';
            $html .= '<code>';
            $html .= '引用元：' . $arr[':author_url'];
            $html .= '</code>';
            $html .= '</p>';
        }

        $html .= '<blockquote>';
        $html .= '<p>';
        $html .= '更新日時' . date('Y年m月d日');
        $html .= '</p>';
        $html .= '<p>';
        $html .= '作者：' . $arr[':author'];
        $html .= '</p>';

        if (!empty($arr[':author_url'])) {
            $html .= '<p>';
            $html .= '<a href="' . $arr[':author_url'] . '">作者ページ</a>';
            $html .= '</p>';
        }

        if (!empty($arr[':length'])) {
            $html .= '<p>';
            $html .= '長さ：' . $arr[':length'];
            $html .= '</p>';
        }
        if (!empty($arr[':global_point'])) {
            $html .= '<p>';
            $html .= '総合得点：' . $arr[':global_point'];
            $html .= '</p>';
        }

        if (!empty($arr[':global_point'])) {
            $html .= '<p>';
            $html .= '総合得点：' . $arr[':global_point'];
            $html .= '</p>';
        }
        if (!empty($arr[':fav_novel_cnt'])) {
            $html .= '<p>';
            $html .= 'ブックマーク数：' . $arr[':fav_novel_cnt'];
            $html .= '</p>';
        }
        $html .= '</blockquote>';

        $html = str_replace('\r\n', '', $html);

        return $html;
    }
}
