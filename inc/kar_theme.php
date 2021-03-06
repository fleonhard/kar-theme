<?php
/**
 *  Copyright (c) 2019 Herborn Software
 *
 * @package kar
 */

require get_template_directory() . '/inc/kar_woocommerce.php';
require get_template_directory() . '/inc/kar_walker_nav_menu.php';
require get_template_directory() . '/inc/kar_walker_comment.php';
require get_template_directory() . '/inc/post-types/KAR_Podcast_Plugin.php';
require get_template_directory() . '/inc/post-types/KAR_Travel_Plugin.php';
require get_template_directory() . '/inc/post-types/KAR_Art_Plugin.php';

defined('ABSPATH') || exit;

if (!class_exists('KAR_Theme')) {
    final class KAR_Theme
    {
        private static $POST_VIEW_META = 'kar_post_views';
        private $theme;
        private $DOMAIN = "kar";

        public static function install()
        {
            return new KAR_Theme();
        }

        private function __construct()
        {
            $this->theme = wp_get_theme();

            add_theme_support('custom-header');
            add_theme_support('custom-background');
            add_theme_support('post-thumbnails');
            add_theme_support('post-formats', array(/*'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'*/));
            add_theme_support('html5', array('audio', 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));

            add_image_size('thumbnail-s', 100, 100);
            add_image_size('thumbnail-m', 500, 500);
            add_image_size('thumbnail-l', 1000, 1000);

            add_action('wp_enqueue_scripts', array($this, 'add_frontend_scripts'));
            add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'));

            add_action('pre_get_posts', array($this, 'register_query_post_types'));
            add_action('get_header', array($this, 'save_header_name'));
            add_action('get_footer', array($this, 'save_footer_name'));

            add_action('after_setup_theme', array($this, 'register_lang'));
            add_action('after_setup_theme', array($this, 'register_navs'));
            add_action('after_setup_theme', array($this, 'register_sidebars'));
            add_action('after_setup_theme', array($this, 'register_plugin_support'));

            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

            add_action('kar_increase_post_views', array($this, 'increase_post_views'));

            add_action('kar_get_template', array($this, 'get_template'));

            add_action('admin_menu', array($this, 'add_admin_menu'));

            add_filter('excerpt_more', array($this, 'create_excerpt_more'));
        }

        function create_excerpt_more($more)
        {
            return ' ...';
        }

        function add_admin_menu()
        {
            add_options_page(__("Theme Settings"), __("Theme Settings"), 'manage_options', 'theme-settings', array($this, 'render_options_page'));
            $this->settings_init();
        }


        function render_options_page()
        {
            echo '<form action="options.php" method="post">';
            echo '<h2>' . __("Theme Settings") . '</h2>';
            settings_fields('kar_settings');
            do_settings_sections('kar_settings');
            submit_button();
            echo '</form>';
        }


        function settings_init()
        {
            $settings_page = 'kar_settings';


            add_settings_section('particles', __('Particles', 'kar'), null, $settings_page);

            $snow_option = 'snow';
            register_setting($settings_page, $snow_option);

            add_settings_field($snow_option, __('Snow', 'kar'), function () use ($snow_option) {
                echo '<input type="checkbox" name="' . $snow_option . '" value="1" ' . checked(1, get_option($snow_option), false) . '/>';
            }, $settings_page, 'particles');
        }

        function get_template($template, $post = false)
        {
            if (!$post) {
                global $post;
                global $wp_query;
            }
            $taxonomy = $wp_query->get('taxonomy');

            if ($taxonomy) {
                get_template_part('templates/' . $template, $taxonomy);
            } else if (get_post_type($post) != 'post') {
                get_template_part('templates/' . $template, get_post_type($post));
            } else {
                get_template_part('templates/' . $template, get_post_format($post));
            }
        }

        function get_post_views($post_id = false)
        {
            if (!$post_id) {
                $post_id = get_the_ID();
            }
            $views = get_post_meta($post_id, self::$POST_VIEW_META, true);
            return empty($views) ? 0 : $views;
        }


        function increase_post_views($post_id = false)
        {
            if (!$post_id) {
                $post_id = get_the_ID();
            }
            update_post_meta($post_id, self::$POST_VIEW_META, $this->get_post_views($post_id) + 1);
        }

        function register_plugin_support()
        {
            KAR_Woocommerce::install();
            KAR_Podcast_Plugin::init();
            KAR_Travel_Plugin::init();
            KAR_Art_Plugin::init();
        }

        function register_lang()
        {
            load_theme_textdomain('kar', get_template_directory() . '/language');
        }

        function register_navs()
        {
            register_nav_menu('primary', 'Header Navigation Menu');
        }

        private function register_sidebar($id, $name, $description)
        {
            register_sidebar(
                array(
                    'name' => $name,
                    'id' => $id,
                    'description' => $description,
                    'before_widget' => '<section id="%1$s" class="kar-widget col-12 %2$s">',
                    'after_widget' => '</section>',
                    'before_title' => '<h2 class="kar-widget-title">',
                    'after_title' => '</h2>'
                )
            );
        }

        function register_sidebars()
        {
            $this->register_sidebar('desktop_left', __('Sidebar Desktop Left', 'kar'), __('Sidebar is only displayed on Desktop on the left side', 'kar'));
            $this->register_sidebar('desktop_right', __('Sidebar Desktop Right', 'kar'), __('Sidebar is only displayed on Desktop on the right side', 'kar'));
            $this->register_sidebar('desktop_top', __('Sidebar Desktop Top', 'kar'), __('Sidebar is only displayed on Desktop on top', 'kar'));
            $this->register_sidebar('desktop_bottom', __('Sidebar Desktop Bottom', 'kar'), __('Sidebar is only displayed on Desktop on bottom', 'kar'));
            $this->register_sidebar('mobile_top', __('Sidebar Mobile Top', 'kar'), __('Sidebar is only displayed on Mobile on the top', 'kar'));
            $this->register_sidebar('mobile_bottom', __('Sidebar Mobile Bottom', 'kar'), __('Sidebar is only displayed on Mobile on the bottom', 'kar'));
        }

        function add_frontend_scripts()
        {
            wp_enqueue_script('app', get_template_directory_uri() . '/public/js/app.js', array(), $this->theme->get('Version'), true);

            wp_enqueue_script('animations', get_template_directory_uri() . '/public/js/animations.js', array(), $this->theme->get('Version'), true);
            wp_localize_script('animations', 'animations', array(
                'assets_dir' => get_template_directory_uri() . '/assets/',
                'snow' => get_template_directory_uri() . '/assets/snow_config',
                'snow_img' => get_template_directory_uri() . '/assets/snow.png',
                'is_snowing' => $this->is_snowing()
            ));

            wp_enqueue_style('app', get_template_directory_uri() . '/public/css/app.css', array(), $this->theme->get('Version'), 'all');
            wp_enqueue_style('raleway', 'https://fonts.googleapis.com/css?family=Raleway:200,300,500');
            wp_enqueue_style('playfair_display', 'https://fonts.googleapis.com/css?family=Playfair+Display:400,700,900&display=swap');
        }

        function is_snowing()
        {
            return get_option('snow', false);
        }

        function add_admin_scripts()
        {
            wp_enqueue_style('app', get_template_directory_uri() . '/public/css/admin.css', array(), $this->theme->get('Version'), 'all');
            wp_enqueue_script('app', get_template_directory_uri() . '/public/js/admin.js', array(), $this->theme->get('Version'), true);
            wp_localize_script('app', 'kar_data', array(
                'frame_title' => __('Upload Audio File', 'kar'),
                'button_text' => __('Use this File', 'kar')
            ));
        }

        function register_query_post_types(\WP_Query $query)
        {
            if (is_home() && $query->is_main_query()) {
                $query->set('post_type', array('post', 'podcast', 'podcast_episode', 'travel_trip', 'travel_milestone', 'art_image'));
            }
            return $query;
        }

        function save_header_name($name)
        {
            add_filter('current_header', function () use ($name) {
                return (string)$name;
            });
        }

        function save_footer_name($name)
        {
            add_filter('current_footer', function () use ($name) {
                return (string)$name;
            });
        }
    }
}
