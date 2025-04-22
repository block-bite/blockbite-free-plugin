<?php

namespace Blockbite\Blockbite\Controllers;

use WP_REST_Response;
use WP_Error;

use Blockbite\Blockbite\Controllers\Database as DbController;
use Blockbite\Orm\BlockbiteOrm as Db;



class Items extends Controller
{


    protected $public_url = '';



    private static $error_messages = [
        'invalid_handle' => 'Handle is required and must be a string',
        'invalid_id' => 'ID is required and must be numeric',
        'invalid_is_default' => 'is_default is required',
        'invalid_blockname' => 'blockname is required'
    ];




    private static function validate($data)
    {
        $fields_schema = [
            'id' => 'int',
            'handle' => 'string',
            'category' => 'string',
            'blockname' => 'string',
            'is_default' => 'boolean',
            'platform' => 'string',
            'title' => 'string',
            'slug' => 'string',
            'version' => 'string',
            'summary' => 'string',
            'content' => 'text',
            'post_id' => 'int',
            'parent' => 'int',
            'updated_at' => 'timestamp',
            'data' => 'json',
        ];

        $validated_data = [];

        foreach ($fields_schema as $field => $type) {
            switch ($type) {
                case 'int':
                    if (isset($data[$field]) && is_numeric($data[$field])) {
                        $validated_data[$field] = intval($data[$field]);
                    }
                    break;

                case 'string':
                    $validated_data[$field] = isset($data[$field]) && is_string($data[$field])
                        ? sanitize_text_field($data[$field])
                        : '';
                    break;

                case 'boolean':
                    $validated_data[$field] = isset($data[$field])
                        ? (bool) $data[$field]
                        : false;
                    break;

                case 'text':
                    $validated_data[$field] = isset($data[$field])
                        ? sanitize_textarea_field($data[$field])
                        : '';
                    break;

                case 'json':
                    $validated_data[$field] = isset($data[$field]) && is_array($data[$field])
                        ? json_encode($data[$field])
                        : '{}';
                    break;

                case 'timestamp':
                    $validated_data[$field] = isset($data[$field]) && strtotime($data[$field])
                        ? date('Y-m-d H:i:s', strtotime($data[$field]))
                        : date('Y-m-d H:i:s');
                    break;

                default:
                    // Handle unexpected types if needed.
                    $validated_data[$field] = isset($data[$field])
                        ? sanitize_text_field($data[$field])
                        : '';
                    break;
            }
        }

        // Specific validation for required fields
        if (empty($validated_data['handle'])) {
            return new WP_Error('invalid_handle', self::$error_messages['invalid_handle'], ['status' => 400]);
        }

        return $validated_data;
    }



    public static function upsert_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);


        error_log(print_r($validated_data, true));


        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $upsert = Db::table()->upsert(
            $validated_data,
            ['handle' => $validated_data['handle'], 'id' => $validated_data['id']]
        );

        return new WP_REST_Response([
            'status' => 200,
            'message' => $validated_data['id'] . ' saved',
            'data' => $upsert
        ], 200);
    }



    public static function upsert_item_handle($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);


        if (is_wp_error($validated_data)) {
            return $validated_data;
        }


        $upsert = Db::table()->upsertHandle($validated_data, $validated_data['handle'])->json();

  
        return new WP_REST_Response([
            'status' => 200,
            'message' => $upsert->id . ' saved',
            'data' => $upsert
        ], 200);
    }




    public static function delete_item($request = null)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $id = $validated_data['id'];

        $deleted = Db::deleteById($id);

        return new WP_REST_Response([
            'status' => 200,
            'message' => $id . ' deleted',
            'data' => $deleted
        ], 200);
    }

    public static function get_items($request)
    {
        $data = $request->get_params();
        $validated_data = self::validate($data);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $handle = $validated_data['handle'];

        $result = Db::table()
        ->where('handle', $handle)
        ->get();

        $result = array_map(function ($item) {
            $item->is_default = intval($item->is_default);
            // decode data
            $item->data = json_decode($item->data);
            return $item;
        }, $result);




        return new WP_REST_Response([
            'status' => 200,
            'message' => $handle . ' fetched',
            'data' => $result
        ], 200);
    }

    public static function get_item($request)
    {
        $data = $request->get_params();



        $validated_data = self::validate($data, ['handle']);

        if (is_wp_error($validated_data)) {
            return $validated_data;
        }

        $handle = $validated_data['handle'];

        $result = Db::table()
        ->where('handle', $handle)
        ->firstJson();


        return new WP_REST_Response([
            'status' => 200,
            'message' => $handle . ' fetched',
            'data' => $result
        ], 200);
    }

}
