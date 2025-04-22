<?php

namespace Blockbite\Blockbite\Controllers;

use Blockbite\Orm\BlockbiteOrm as Db;


class BlockSupport extends Controller
{


    public static function update_block_support($request)
    {
        $blocks = $request->get_param('blocks');

        Db::table()->upsertHandle(
            ['data' => json_encode($blocks)],
            'block_support'
        );


        return [
            'disallowed' => $blocks,
        ];
    }

    public static function filtered_dynamic_blocklist()
    {
        $dynamic_blocks = get_dynamic_block_names();
        $undo_list = ['core/heading', 'core/image'];

        foreach ($undo_list as $block) {
            if (($key = array_search($block, $dynamic_blocks)) !== false) {
                unset($dynamic_blocks[$key]);
            }
        }

        // Reindex the array to ensure it's a standard indexed array
        return array_values($dynamic_blocks);
    }


    public static function get_block_support()
    {
        $disallowed = Db::table()
            ->where('handle', 'block_support')
            ->first();
    
        $allowed_dynamic = Db::table()
            ->where('handle', 'dynamic_block_support')
            ->first();

        if (isset($disallowed->data)) {
            $disallowed = json_decode($disallowed->data);
        }
        if (isset($allowed_dynamic->data)) {
            $allowed_dynamic = json_decode($allowed_dynamic->data);
        }

        return [
            'disallowed' => $disallowed,
            'dynamic' => self::filtered_dynamic_blocklist(),
            'allowed_dynamic' => $allowed_dynamic,
        ];
    }

    public static function update_dynamic_block_support($request)
    {
        $blockname = $request->get_param('blockname') ?? '';
        $allowed = $request->get_param('allowed') ?? false;
        $dynamic_blocks = self::fetch_dynamic_blocks();

        if (empty($blockname)) {
            return [
                'saved' => false,
                'allowed_dynamic' => $dynamic_blocks,
            ];
        }

        $dynamic_blocks = self::update_dynamic_block_array($dynamic_blocks, $blockname, $allowed);
        $saved = self::save_dynamic_blocks($dynamic_blocks);

        return [
            'saved' => $saved,
            'allowed_dynamic' => $dynamic_blocks,
        ];
    }

    private static function fetch_dynamic_blocks()
    {
      
        $result = Db::table()
        ->where('handle', 'dynamic_block_support')
        ->first();

        return is_object($result) && isset($result->data)
            ? json_decode($result->data, true)
            : [];
    }

    private static function update_dynamic_block_array($dynamic_blocks, $blockname, $allowed)
    {
        if ($allowed) {
            if (!in_array($blockname, $dynamic_blocks)) {
                $dynamic_blocks[] = $blockname;
            }
        } else {
            $dynamic_blocks = array_diff($dynamic_blocks, [$blockname]);
        }

        return $dynamic_blocks;
    }

    private static function save_dynamic_blocks($dynamic_blocks)
    {
        return Db::table()->upsertHandle(
            ['data' => json_encode($dynamic_blocks)],
            'dynamic_block_support'
        );
    }
}
