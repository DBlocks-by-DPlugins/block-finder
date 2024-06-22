<?php
/*
Plugin Name: Block Finder
Description: Displays all blocks grouped by name, listing posts and pages where each block is used.
Version: 1.0.0
Author: DPlugins
Author URI:        https://dplugins.com/
Update URI:        https://github.com/krstivoja/block-finder
Text Domain:       dp-block-finder
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class BlockFinder {
    public function __construct() {
        add_action('admin_menu', array($this, 'bf_add_admin_menu'));
        add_action('admin_init', array($this, 'bf_settings_init'));
        add_action('admin_enqueue_scripts', array($this, 'bf_enqueue_scripts'));
    }

    public function bf_add_admin_menu() {
        add_menu_page('Block Finder', 'Block Finder', 'manage_options', 'block_finder', array($this, 'bf_options_page'));
    }

    public function bf_settings_init() {
        register_setting('pluginPage', 'bf_settings');
    }

    public function bf_enqueue_scripts($hook) {
        if ($hook != 'toplevel_page_block_finder') {
            return;
        }
        wp_enqueue_script('bf-script', plugin_dir_url(__FILE__) . 'bf-script.js', array('jquery'), '1.0', true);
        wp_enqueue_style('bf-style', plugin_dir_url(__FILE__) . 'bf-style-min.css');
    }

    public function bf_options_page() {
        ?>
        <div class="wrap">
            <header>
                <h1>Block Finder</h1>
                <?php $this->bf_display_search_form(); ?>
                <!-- <button>Toggle</button> -->
            </header>
            <div id="bf-blocks-container">
                <div id="bf-no-results" >🔥 No blocks found.</div>
                <?php $this->bf_display_blocks(); ?>
            </div>
        </div>
        <?php
    }

    private function bf_display_search_form() {
        ?>
        <form id="bf-search-form" method="get" action="">
            <input type="hidden" name="page" value="block_finder">
            <input type="text" id="bf-search-input" placeholder="Search blocks..." autofocus>
            <button type="button" id="bf-clear-button">
                <svg width="100pt" height="100pt" version="1.1" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <path d="m50 8.332c-22.977 0-41.668 18.691-41.668 41.668s18.691 41.668 41.668 41.668 41.668-18.691 41.668-41.668-18.691-41.668-41.668-41.668zm16.961 54.531l-4.1016 4.1016-12.859-12.863-12.863 12.859-4.0977-4.1016 12.859-12.859-12.859-12.863 4.0977-4.1016 12.863 12.863 12.863-12.863 4.1016 4.1016-12.863 12.863z"/>
                </svg>
            </button>
        </form>
        <?php
    }

    private function bf_display_blocks() {
        $posts = get_posts(array('numberposts' => -1, 'post_type' => array('post', 'page')));
        $blocks = array();

        foreach ($posts as $post) {
            if (has_blocks($post->post_content)) {
                $post_blocks = parse_blocks($post->post_content);
                $this->bf_get_blocks($post_blocks, $blocks, $post);
            }
        }

        foreach ($blocks as $block_title => $block_posts) {
            echo '<div class="bf-block-group" data-block-title="' . esc_attr($block_title) . '">';
            echo '<div class="title-wrap"><h2>' . esc_html($block_title) . '</h2></div>';
            echo '<ul>';
            foreach ($block_posts as $post_id => $post_title) {
                if (!empty($post_title)) {
                    $post_type = get_post_type($post_id);
                    $post_type_obj = get_post_type_object($post_type);
                    $dashicon = $post_type_obj->menu_icon ? $post_type_obj->menu_icon : 'dashicons-admin-post';
                    echo '<li><a target="_blank" href="' . get_edit_post_link($post_id) . '"><span class="dashicons ' . esc_attr($dashicon) . '"></span> ' . esc_html($post_title) . ' <span class="external-link" aria-hidden="true">→</span></a></li>';
                }
            }
            echo '</ul>';
            echo '</div>';
        }
    }

    private function bf_get_blocks($post_blocks, &$blocks, $post) {
        foreach ($post_blocks as $block) {
            if (isset($block['blockName'])) {
                $block_title = $this->bf_get_block_title($block['blockName']);
                if (!isset($blocks[$block_title])) {
                    $blocks[$block_title] = array();
                }
                if (!isset($blocks[$block_title][$post->ID])) {
                    $blocks[$block_title][$post->ID] = $post->post_title;
                }
            }

            if (isset($block['innerBlocks']) && !empty($block['innerBlocks'])) {
                $this->bf_get_blocks($block['innerBlocks'], $blocks, $post);
            }
        }
    }

    private function bf_get_block_title($block_name) {
        $block_registry = WP_Block_Type_Registry::get_instance();
        $block_type = $block_registry->get_registered($block_name);

        return $block_type ? $block_type->title : $block_name;
    }
}

new BlockFinder();