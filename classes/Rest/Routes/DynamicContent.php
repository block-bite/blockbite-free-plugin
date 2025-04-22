<?php

namespace Blockbite\Blockbite\Rest\Routes;

use Blockbite\Blockbite\Plugin;
use Blockbite\Blockbite\Controllers\DynamicContent as DynamicContentController;
use Blockbite\Blockbite\Rest\Api;

class DynamicContent extends Api
{


    protected $namespace  = 'blockbite/v1';

    public function Register()
    {


        $dynamicContentController = new DynamicContentController($this->plugin);




        register_rest_route($this->namespace, '/dynamic-content', [

            [
                'methods' => 'POST',
                'callback' => [$dynamicContentController, 'update_dynamic_content'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'slug' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ],
                    'data' => [
                        'required' => false,
                        'type' => 'json',
                    ],
                    'title' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'summary' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                    'css' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/dynamic-content/content/(?P<slug>[A-Za-z0-9_-]+)', [
            [
                'methods' => 'GET',
                'callback' => [$dynamicContentController, 'get_dynamic_content'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'slug' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'text',
                    ],
                ]
            ],
        ]);


        // get all dynamic content items
        register_rest_route($this->namespace, '/dynamic-content', [
            [
                'methods' => 'GET',
                'callback' => [$dynamicContentController, 'get_dynamic_content_overview'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
            ],
        ]);


        //render_dynamic_content 
        register_rest_route(
            $this->namespace,
            '/dynamic-content/display/',
            [
                [
                    'methods' => 'GET',
                    'callback' => [$dynamicContentController, 'render_dynamic_content_rest'],
                    'permission_callback' => [$dynamicContentController, 'authorize'],
                    'args' => [
                        'designId' => [
                            'required' => false,
                            'type' => 'string',
                        ],
                        'renderTag' => [
                            'required' => false,
                            'type' => 'string',
                        ],
                        'contentId' => [
                            'required' => false,
                            'type' => 'string',
                        ],
                    ]
                ],
            ]
        );


        // save design dynamic content
        register_rest_route($this->namespace, '/dynamic-content/design', [
            [
                'methods' => 'POST',
                'callback' => [$dynamicContentController, 'update_design_dynamic_blocks'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'blocks' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

        register_rest_route($this->namespace, '/dynamic-content/designs/(?P<slug>[A-Za-z0-9_-]+)', [
            [
                'methods' => 'GET',
                'callback' => [$dynamicContentController, 'get_dynamic_designs_by_parent'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'slug' => [
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'type' => 'string',
                    ],
                ]
            ],
        ]);


           // store the whole dynamic content block
           register_rest_route($this->namespace, '/dynamic-content/store-blocks', [
            [
                'methods' => 'POST',
                'callback' => [$dynamicContentController, 'store_dynamic_content_blocks'],
                'permission_callback' => [$dynamicContentController, 'authorize'],
                'args' => [
                    'blocks' => [
                        'required' => false,
                        'type' => 'string',
                    ],
                ]
            ],
        ]);

    }
}
