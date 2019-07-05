<?php
/**
 * Plugin Name: Update Posts Date
 * Plugin URI:  https://github.com/sasha1344/update-posts-date
 * Description: Auto update posts date in selected categories using cron.
 * Version:     1.0.0
 * Author:      sasha1344
 * Author URI:  https://github.com/sasha1344
 * Text Domain: udate
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

/**
 * No Direct Access
 */
defined( 'ABSPATH' ) or die;

/**
 * Define Constants
 */
define( 'UDATE_VERSION', '1.0.0' );

if ( !defined( 'UDATE_PLUGIN_PATH' ) ) {
	define( 'UDATE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'UDATE_PLUGIN_URL') ) {
	define( 'UDATE_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
}

/**
 * Update Posts Date Class
 */
class UpdatePostsDate
{
	/**
	 * Static property to hold class instance
	 */
	static $instance = false;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->define_admin_hooks();

		// WP Activate & Deactivate Hooks.
		register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'plugin_deactivate' ) );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @return  void
	 */
	private function load_dependencies() {

		// Options Page.
		require_once UDATE_PLUGIN_PATH . '/includes/class-udate-options-page.php';

		// Schedule.
		require_once UDATE_PLUGIN_PATH . '/includes/class-udate-schedule.php';

		// Update Dates.
		require_once UDATE_PLUGIN_PATH . '/includes/class-udate-posts-date.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @return  void
	 */
	private function define_admin_hooks() {
		add_action( 'plugins_loaded', array( $this, 'set_locale' ) );

		// Options Page.
		$optionsPage = new UdateOptionsPage();
		add_action( 'admin_menu', array( $optionsPage, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $optionsPage, 'enqueue_scripts' ) );

		// Cron Schedules.
		add_filter( 'cron_schedules', array( 'UdateSchedule', 'cron_intervals' ) );

		// Posts Update Cron Actions.
		foreach ( UdateSchedule::get_cron_events() as $event_name ) {
			add_action( $event_name, array( 'UdatePostsDate', 'increment' ), 10, 3);
		}
	}

	/**
	 * Load plugin locale (textdomain).
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function set_locale() {
		load_plugin_textdomain( 'udate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Plugin activation hook.
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function plugin_activate() {
		// Do something.
	}

	/**
	 * Plugin deactivation hook.
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function plugin_deactivate() {
		// Do something.
	}

	/**
	 * If an instance exists, this returns it.
	 *
	 * @since   1.0.0
	 * @return  UpdatePostsDate
	 */
	public static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}

// Initialize the Plugin.
$UpdatePostsDate = UpdatePostsDate::getInstance();