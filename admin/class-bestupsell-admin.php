<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.identixweb.com/
 * @since      1.2.0
 *
 * @package    Bestupsell
 * @subpackage Bestupsell/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bestupsell
 * @subpackage Bestupsell/admin
 * @author     identixweb <https://www.identixweb.com/>
 */
class Bestupsell_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.2.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.2.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.2.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.2.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Bestupsell_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Bestupsell_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if( isset($_GET['page']) && $_GET['page'] == 'best-upsell'){
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/bestupsell-admin.css', array(), $this->version, 'all');
        }

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.2.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Bestupsell_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Bestupsell_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/bestupsell-admin.js', array('jquery'), $this->version, false);

    }

    public function add_admin_pages()
    {
        add_menu_page('Best upsell', 'Best upsell', 'manage_options', 'best-upsell', array($this, 'admin_index'), 'dashicons-cart', '110');
    }

    public function admin_index()
    {
        //require template file
        include(plugin_dir_path(__FILE__) . 'partials/templates/bestupsell-admin.php');
    }

    public function settings_links($plugin_actions, $plugin_file)
    {
        $new_actions = array();
        if ('best-upsell-for-woocommerce/best-upsell.php' === $plugin_file ) {
            $new_actions['settings'] = '<a href="admin.php?page=best-upsell">Settings</a>';
        }
        return array_merge($new_actions, $plugin_actions);
    }

    //active plugin redirect to menu page bestupsell
    public function activation_redirecta($plugin_file)
    {
        if ('best-upsell-for-woocommerce/best-upsell.php' === $plugin_file ) {
           wp_redirect(admin_url('/admin.php?page=best-upsell'));
           exit();
        }
    }

}
