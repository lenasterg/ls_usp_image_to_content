<?php

/**
 * Plugin Name:     Ls_usp_image_to_content
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     ls_usp_image_to_content
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Ls_usp_image_to_content
 */
function ls_include_image_to_content_init() {
    if (function_exists('user_submitted_posts')) {
        add_action('wp', 'ls_remove_usp_auto_display_images');
        add_action('usp_files_after', 'ls_include_image_to_content');
    }
    
}
add_action('plugins_loaded', 'ls_include_image_to_content_init');



/**
 * 
 */
function ls_remove_usp_auto_display_images() {
    remove_filter('the_content', 'usp_auto_display_images');
}

/**
 * 
 * @global type $usp_options
 * @param type $attach_ids
 */
function ls_include_image_to_content($attach_ids) {

    global $usp_options;
    $num = 0;

    $enable = isset($usp_options['auto_display_images']) ? $usp_options['auto_display_images'] : 'disable';

    if ($enable === 'before' || $enable === 'after') {
        foreach ($attach_ids as $attachment) {

            $parent_id = wp_get_post_parent_id($attachment);
            $parent_title = get_the_title($parent_id);
            $content = get_post_field('post_content', $parent_id);
            $author = '';
            $title = '';


            $image = '<div>';
            $markup = isset($usp_options['auto_image_markup']) ? $usp_options['auto_image_markup'] : '';

            $thumb = apply_filters('usp_image_thumb', wp_get_attachment_image_src($attachment, 'thumbnail', false));
            $medium = apply_filters('usp_image_medium', wp_get_attachment_image_src($attachment, 'medium', false));
            $large = apply_filters('usp_image_large', wp_get_attachment_image_src($attachment, 'large', false));
            $full = apply_filters('usp_image_full', wp_get_attachment_image_src($attachment, 'full', false));

            $custom_size = apply_filters('usp_image_custom_size', 'custom');
            $custom = apply_filters('usp_image_custom', wp_get_attachment_image_src($attachment, $custom_size, false));



            $image .= usp_replace_image_vars($markup, $title, $thumb, $medium, $large, $full, $custom, $parent_title, $author);
            $image .= '</div>';
            if ($enable === 'before') {
                $content = $image . $content;
            } elseif ($enable === 'after') {
                $content = $content . $image;
            }

            $my_post = array(
                'ID' => $parent_id,
                'post_content' => $content,
            );
            wp_update_post($my_post);
            delete_post_meta($parent_id, 'user_submit_image', wp_get_attachment_url($attachment));

            //set 1st image as post_thumbnail
            if ($num == 0) {
                if ($usp_options['usp_featured_images'] == 1) {
                    set_post_thumbnail($parent_id, $attachment);
                }
                $num = 1;
            }
        }
    }
}
