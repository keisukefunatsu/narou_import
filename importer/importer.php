<?php

require_once NAROU_DIR . '/lib/Spyc/Spyc.php';

class NarouImporter
{
    public function __construct()
    {
    }
    public function import_all()
    {
        $file = file_get_contents(NAROU_DIR . '/test/test_info.yml');
        
        $array = Spyc::YAMLLoad($file);
        // pp($array);
        foreach ($array as $arr) {
            // pp($arr[':title']);
            $keyword = $arr[0];
            $content = $arr[':book_content'];

            if (is_array($content)) {
                $content = implode($content);
            }
            
            $post = [
                'post_content' => $content,
                'post_name' => $arr[':isbn'], // slug
                'post_title' => $arr[':title'], // title
                // 'post_status' => 'draft',
                'post_status' => 'publish',
                'post_type' => 'post',
                'comment_status' => 'closed',
                'post_category' => [],
                'tags_input' => $keyword,
                'meta_input' => []
            ];
            // pp($post);
            // pp($arr);
            wp_insert_post($post);
        }
    }
}
