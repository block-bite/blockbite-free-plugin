<?php

namespace Blockbite\Blockbite\Controllers;

use Blockbite\Orm\BlockbiteOrm as Db;
use Blockbite\Blockbite\Controllers\Bites as BitesController;

use WP_REST_Response;
use WP_Error;

class DynamicContent extends Controller
{


   
    public static function get_dynamic_content_overview($request)
    {
        $dynamic_content = Db::table()
            ->where('handle', 'dynamic_content')
            ->get();

        if (empty($dynamic_content)) {
            return [
                'status' => 200,
                'data' =>[],
                'message' => 'No dynamic content found',
            ];
        }
        // Loop through the dynamic content and decode the JSON data
        foreach ($dynamic_content as $content) {
            $content->data = json_decode($content->data);
        }
        

        return [
            'status' => 200,
            'data' => $dynamic_content,
        ];
    }

    public static function update_dynamic_content($request)
    {

        $slug = $request->get_param('slug');
        $title = $request->get_param('title');
       

      
        $data = [
            'title' => $title,
            'slug' => $slug,
            'handle' => 'dynamic_content',
            'data' =>  json_encode($request->get_param('data')),
        ];

        $result = Db::table()->upsert(
            $data,
            ['slug' => $slug, 'handle' => 'dynamic_content']
        );

        return [
            'status' => 200,
            'data' => $result->json(),
        ];
        
        
    }

   

    public static function get_dynamic_designs_by_parent($request)
    {
        $parent = $request->get_param('slug');

        $dynamic_design = Db::table()
        ->where('handle', 'dynamic_design')
        ->where('parent', $parent)
        ->get();


        if (empty($dynamic_design)) {
            return [
                'status' => 200,
                'result' => false,
                'parent' => $parent,
            ];
        }

        return [
            'status' => 200,
            'result' => $dynamic_design,
            'parent' => $parent,
        ];
    }


    /*
        Save multiple records at once
    */
    public static function update_design_dynamic_blocks($request)
    {

        // get all dynamic design blocks within page
        $blocks = $request->get_param('blocks');
        // json decode the blocks
        $blocks = json_decode($blocks, true);

        // loop blocks
        foreach ($blocks as $block) {
            $parent = $block['parent'];
            $content = $block['content'];
            $slug = $block['slug'];
            $title = $block['title'];

            $strip_raw = BitesController::strip_bite($content);
            $parsed_block = parse_blocks($strip_raw);
 
            // if parsed block has length
            if (count($parsed_block) === 0) {
                continue;
            }

            $rendered_block = '';
            foreach ($parsed_block as $block) {
                $rendered_block .= render_block($block);
            }

            if($slug && $parent ){
                $dynamic_content_saved = Db::table()->upsert(
                    [
                        'title' => $title,
                        'content' => $rendered_block,
                        'slug' => $slug,
                        'handle' => 'dynamic_design',
                        'data' => json_encode(["parent" => $parent]),
                    ],
                    ['handle' => 'dynamic_design', 'slug' => $slug]
                );
            }
        }
        return [
            'status' => 200,
            'result' => 'blocks saved',
        ];
    }

    //store_dynamic_content_blocks
    public static function store_dynamic_content_blocks($request)
    {

        // get all dynamic design blocks within page
        $blocks = $request->get_param('blocks');
        // json decode the blocks
        $blocks = json_decode($blocks, true);

        // loop blocks
        foreach ($blocks as $block) {
            $slug = $block['slug'];
            $content = $block['content'];
           
            if($slug){
                Db::table()->upsert(
                    ['content' => $content],
                    ['handle' => 'dynamic_content', 'slug' => $slug]
                );
            }
        }
        return [
            'status' => 200,
            'result' => 'blocks saved',
        ];
    }

    public static function get_dynamic_content($request = null)
    {
        $slug = is_null($request) ? null : $request->get_param('slug');

        // Retrieve the ID dynamically or use it directly if passed to the function
        if (is_null($slug)) {
            return [
                'status' => 400,
                'message' => 'Slug is required',
            ];
        }

    
        $dynamic_content = Db::table()
            ->where(['slug' => $slug, 'handle' => 'dynamic_content'])
            ->firstJson();
        

        if (empty($dynamic_content)) {
            return [
                'status' => 200,
                'data' => [],
                'message' => 'No dynamic content found',
            ];
        }

        return [
            'status' => 200,
            'data' => $dynamic_content,
        ];
    }



    public static function render_dynamic_content_rest($request = null)
    {
        $slug = $request->get_param('contentId');
        $designId = $request->get_param('designId');
        $renderTag = $request->get_param('renderTag');
    
        $dynamicContent = Db::table()
            ->where(['slug' => $slug, 'handle' => 'dynamic_content'])
            ->first();
            
    
        $dynamicDesign = Db::table()
            ->where(['handle' => 'dynamic_design', 'slug' => $designId])
            ->first();
           
            
    
        if (empty($dynamicContent)) {
            return [
                'status' => 404,
                'message' => 'no-content',
                'data' => [],
            ];
        }
    
        if (empty($dynamicDesign)) {
            return [
                'status' => 200,
                'message' => 'no-design',
                'data' => $dynamicContent,
            ];
        }
    
        $renderedContent = self::process_dynamic_content($dynamicContent, $dynamicDesign);
    
    
        return [
            'status' => 200,
            'data' => $renderedContent,
        ];
    }
    
    public static function render_dynamic_content($contentId, $designId, $renderTag)
    {
    
        $dynamicContent = Db::table()
            ->where(['slug' => $contentId, 'handle' => 'dynamic_content'])
            ->first();
        
        $dynamicDesign = Db::table()
            ->where(['handle' => 'dynamic_design', 'slug' => $designId])
            ->first();
        
    
        if (empty($dynamicContent)) {
            error_log('Dynamic content not found');
            return;
        }
    
        if (empty($dynamicDesign)) {
            error_log('Dynamic design not found');
            return;
        }
    
        $renderedContent = self::process_dynamic_content($dynamicContent, $dynamicDesign);
    
        foreach ($renderedContent as $snippet) {
            self::output_snippet($snippet, $renderTag);
        }
    }
    
    /**
     * Shared function to process dynamic content into rendered HTML strings.
     * Returns array of rendered rows.
     */
    private static function process_dynamic_content($dynamicContent, $dynamicDesign)
    {

        if (empty($dynamicContent) || empty($dynamicDesign)) {
            return [];
        }

        $contentArray = json_decode($dynamicContent->data, true);


        if (empty($contentArray) || !isset($contentArray['fieldset'])) {
            return [];
        }

        $fieldset = $contentArray['fieldset'];
        $template = is_string($dynamicDesign->content) ? $dynamicDesign->content : '';
    
        $renderedRows = [];
    
        foreach ((array) $contentArray['content'] as $row) {
            $rendered = $template;
    
            foreach ($fieldset as $field) {
                $value = $row[$field['id']] ?? '';
    
                if ($field['type'] === 'media') {
                    $rendered = self::render_media($rendered, $field['id'], $value);
                } else {
                    $rendered = self::render_text($rendered, $field['id'], $value);
                }
            }
    
            $renderedRows[] = $rendered;
        }
    
        return $renderedRows;
    }
    
    
    
    private static function render_text(string $template, string $fieldId, $value): string
    {
        return preg_replace_callback("/#\{({$fieldId})\}#/", function () use ($value) {
            return htmlspecialchars(is_scalar($value) ? $value : '', ENT_QUOTES, 'UTF-8');
        }, $template);
    }
    
    private static function render_media(string $template, string $fieldId, $media): string
    {
        if (!is_array($media)) {
            return $template;
        }

        $type = $media['type'] ?? 'image';

        // If image, support _thumbnail, _medium, _large
        if ($type === 'image') {
            $template = preg_replace_callback("/#\{{$fieldId}_(thumbnail|medium|large)\}#/", function ($matches) use ($media) {
                $size = $matches[1];
                return isset($media['sizes'][$size])
                    ? htmlspecialchars($media['sizes'][$size], ENT_QUOTES, 'UTF-8')
                    : '';
            }, $template);
           
        }

        // Replace default (no suffix)
        return preg_replace_callback("/#\{{$fieldId}\}#/", function () use ($media) {
            return isset($media['url']) ? htmlspecialchars($media['url'], ENT_QUOTES, 'UTF-8') : '';
        }, $template);
    }


    /**
     * Outputs the rendered snippet wrapped in a slide tag if needed.
     */
    private static function output_snippet(string $output, string $renderTag): void
    {
        ob_start();
        if ($renderTag === 'slide') {
            echo '<swiper-slide>' . $output . '</swiper-slide>';
        } else {
            echo $output;
        }
        echo ob_get_clean();
    }
    
}
