<?php

/**
 * @package Report User
 * @copyright (c) 2026 Daniel James
 * @license https://opensource.org/license/gpl-2-0
 */

namespace danieltj\reportuser\notification\type;

class report_closed extends \phpbb\notification\type\base {

	/**
	 * @var phpbb\user_loader
	 */
	protected $user_loader;

	/**
	 * @var danieltj\reportuser\includes\functions
	 */
	protected $functions;

	/**
	 * @var string
	 */
	protected $permission = 'u_viewprofile';

	/**
	 * Notification options data.
	 * 
	 * @var array $notification_option An array of notification data.
	 */
	static public $notification_option = [
		'id'		=> 'danieltj.reportuser.notification.type.report_closed',
		'group'		=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
		'lang'		=> 'REPORT_USER_NOTIFICATIONS_REPORT_CLOSED_SAMPLE',
	];

	/**
	 * Set the user loader object.
	 * 
	 * @param \phpbb\user_loader $user_loader The user_loader object.
	 * 
	 * @return void
	 */
	public function set_user_loader( \phpbb\user_loader $user_loader ) {

		$this->user_loader = $user_loader;

	}

	/**
	 * Set the extension functions object.
	 * 
	 * @param \danieltj\reportuser\includes\functions $functions The functions object.
	 * 
	 * @return void
	 */
	public function set_functions( \danieltj\reportuser\includes\functions $functions ) {

		$this->functions = $functions;

	}

	/**
	 * Returns the type of notification.
	 * 
	 * @uses $notification_option
	 * 
	 * @return string  The type of notification this is.
	 */
	public function get_type() {

		return $this::$notification_option[ 'id' ];

	}

	/**
	 * Return a CSS class name for the notification.
	 * 
	 * @return string  The CSS class name.
	 */
	public function get_style_class() {

		return 'notification-report-closed';

	}

	/**
	 * Returns a boolean value checking if the user can access this notification.
	 * 
	 * @return boolean  Returns true if permission is granted or false if not.
	 */
	public function is_available() {

		return $this->auth->acl_get( $this->permission );

	}

	/**
	 * Return the notification item ID.
	 * 
	 * @param array $data The item data passed to the notification handler.
	 * 
	 * @return integer  The notification item ID.
	 */
	public static function get_item_id( $data ) {

		return (int) $data[ 'report_id' ];

	}

	/**
	 * Return the ID of the parent.
	 * 
	 * @param array $data The item data passed to the notification handler.
	 * 
	 * @return integer  The parent ID of this notification.
	 */
	public static function get_item_parent_id( $data ) {

		return 0;

	}

	/**
	 * Returns a collection of user IDs that want this notification.
	 * 
	 * @param array $data    The array of data for this notification.
	 * @param array $options The array of options for filtering users.
	 * 
	 * @return array  An array of users and their notification preferences.
	 */
	public function find_users_for_notification( $data, $options = [] ) {

		$options = array_merge( [
			'ignore_users' => [ ANONYMOUS ]
		], $options );

		$user_methods = $this->check_user_notification_options( $this->users_to_query( $data ), $options );

		return $user_methods;

	}

	/**
	 * Return the array of users required for this notification.
	 * 
	 * @param array $data The notification data.
	 * 
	 * @return array  The array of user to query later.
	 */
	public function users_to_query( $data = [] ) {

		$user_ids = [];

		/**
		 * Check if the data has actually been set yet because we can't rely on get_data()
		 * if the notification hasn't actually been saved yet which is... annoying.
		 */
		$report_mod_id = ( NULL === $this->get_data( 'report_mod_id' ) ) ? (int) $data[ 'report_mod_id' ] : (int) $this->get_data( 'report_mod_id' );
		$reporter_user_id = ( NULL === $this->get_data( 'reporter_user_id' ) ) ? (int) $data[ 'reporter_user_id' ] : (int) $this->get_data( 'reporter_user_id' );

		// Don't notify the moderator if they made the report.
		if ( $report_mod_id !== $reporter_user_id ) {

			$user_ids[] = $reporter_user_id;

		}

		return $user_ids;

	}

	/**
	 * Return the avatar of the user.
	 * 
	 * @return string  The HTML formatted avatar.
	 */
	public function get_avatar() {

		return $this->user_loader->get_avatar( $this->get_data( 'report_mod_id' ), true, true );

	}

	/**
	 * Return the title of the notification.
	 * 
	 * @return string  The notification title.
	 */
	public function get_title() {

		return $this->language->lang( 'REPORT_USER_NOTIFICATIONS_REPORT_CLOSED_TITLE', $this->user_loader->get_username( $this->get_data( 'report_mod_id' ), 'no_profile', false, false, true ) );

	}

	/**
	 * Return the reference of the notification.
	 * 
	 * @return string  The notification reference.
	 */
	public function get_reference() {

		return $this->language->lang( 'REPORT_USER_NOTIFICATIONS_REPORT_CLOSED_REFERENCE', $this->user_loader->get_username( $this->get_data( 'reported_user_id' ), 'no_profile', false, false, true ) );

	}

	/**
	 * Return the reference of the notification.
	 * 
	 * @return string  The notification reference.
	 */
	public function get_reason() {

		return $this->language->lang( 'NOTIFICATION_REASON', $this->get_data( 'report_text' ) );

	}

	/**
	 * Return the URL of the notification.
	 * 
	 * @return string  The notification URL.
	 */
	public function get_url() {

		return $this->functions->get_user_profile_url( $this->get_data( 'reported_user_id' ) );

	}

	/**
	 * Return the email template.
	 * 
	 * @return string  The name of the email template file.
	 */
	public function get_email_template() {

		return '@danieltj_reportuser/report_closed';

	}

	/**
	 * Return the template variables for the email.
	 * 
	 * @return array  The array of variables required for the template.
	 */
	public function get_email_template_variables() {

		return [
			'MODERATOR_NAME'	=> $this->user_loader->get_username( $this->get_data( 'report_mod_id' ), 'username', false, false, true ),
			'REPORTED_NAME'		=> $this->user_loader->get_username( $this->get_data( 'reported_user_id' ), 'username', false, false, true ),
			'REPORT_REASON'		=> $this->get_data( 'report_text' ),
		];

	}

	/**
	 * Prepare notification data for database insertion.
	 * 
	 * @param array $data            The notification data.
	 * @param array $pre_create_data The array data from `pre_create_insert_array()`.
	 * 
	 * @return void
	 */
	public function create_insert_array( $data, $pre_create_data = [] ) {

		$this->set_data( 'report_id', $data[ 'report_id' ] );
		$this->set_data( 'report_mod_id', $data[ 'report_mod_id' ] );
		$this->set_data( 'reporter_user_id', $data[ 'reporter_user_id' ] );
		$this->set_data( 'reported_user_id', $data[ 'reported_user_id' ] );
		$this->set_data( 'report_text', $data[ 'report_text' ] );

		parent::create_insert_array( $data, $pre_create_data );

	}

	/**
	 * Function for getting the data for insertion in an SQL query.
	 *
	 * @return array  Array of data ready to be inserted into the database.
	 */
	public function get_insert_array() {

		return parent::get_insert_array();

	}

}
