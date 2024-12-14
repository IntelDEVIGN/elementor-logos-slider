<?php
/**
 * Plugin Name: Elementor Logos Slider
 * Description: A customizable logo slider widget for Elementor
 * Version: 1.0.0
 * Author: Adalberto H. Vega
 * Author URI: https://linkedin.com/in/ahvega
 * Text Domain: elementor-logos-slider
 */

if (!defined('ABSPATH')) exit;php
// Function to optimize images using ImageMagick
function optimize_logo_image($image_path) {
    // Check if ImageMagick is installed
    if (class_exists('Imagick')) {
        $image = new Imagick($image_path);
        $image->stripImage(); // Remove unnecessary metadata
        $image->setImageCompression(Imagick::COMPRESSION_JPEG); // Set compression type
        $image->setImageCompressionQuality(80); // Set compression quality
        $image->writeImage($image_path); // Overwrite the original image
    }
}

// Call the function when a new logo is uploaded
add_action('add_attachment', 'optimize_logo_image');

final class Elementor_Logos_Slider {
    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        // Load plugin text domain
        load_plugin_textdomain(
            'elementor-logos-slider',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );

        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'widget_styles']);
    }

    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'elementor-logos-slider'),
            '<strong>' . esc_html__('Elementor Logos Slider', 'elementor-logos-slider') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'elementor-logos-slider') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function widget_scripts() {
        wp_register_script(
            'elementor-logos-slider',
            plugins_url('assets/js/logos-slider.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );
    }

    public function widget_styles() {
        wp_register_style(
            'elementor-logos-slider',
            plugins_url('assets/css/logos-slider.css', __FILE__),
            [],
            '1.0.0'
        );
    }

    public function register_widgets() {
        require_once(__DIR__ . '/includes/widgets/logos-slider.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor_Logos_Slider_Widget());
    }

    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'custom-logos',
            [
                'title' => esc_html__('Logo Widgets', 'elementor-logos-slider'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
}

Elementor_Logos_Slider::instance(); 