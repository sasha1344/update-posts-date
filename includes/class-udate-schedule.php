<?php
/**
 * No Direct Access
 */
defined( 'ABSPATH' ) or die;

/**
 * Schedule post dates update
 */
class UdateSchedule
{

	/**
	 * Schedule dates update events.
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public static function schedule() {

		// Get rules.
		$rules  = UdateOptionsPage::get_saved_rules();
		$events = get_option( 'udate_scheduled_events', array() );

		// Unschedule previous events.
		foreach ( $events as $hook ) {
			wp_unschedule_hook( $hook );
		}

		// Set Events and intervals.
		$new_events     = array();
		$cron_schedules = array();
		foreach ( $rules as $rule_id => $fields ) {
			if ( empty( $fields['errors'] ) ) {

				// Schedule new event.
				$event_name  = 'udate_event_' . $rule_id;
				$timing_name = 'udate_frequency_' . $rule_id;

				// Add scheduled event to the list.
				$new_events[] = $event_name;

				// Set WP Cron timing.
				$interval = self::get_subfields_seconds( $fields['frequency'] );

				$cron_schedules[ $timing_name ] = array(
					'interval' => $interval,
					'display'  => 'Every ' . round( $interval / 60 ) . ' minutes',
				);
			}
		}

		// Set last scheduled events.
		update_option( 'udate_scheduled_events', $new_events );

		// Set actual cron timing.
		update_option( 'udate_cron_schedule_intervals', $cron_schedules );

		// Schedule event for each rule.
		foreach ( $rules as $rule_id => $fields ) {
			if ( empty( $fields['errors'] ) ) {
				$event_name  = 'udate_event_' . $rule_id;
				$timing_name = 'udate_frequency_' . $rule_id;
				$variables   = array(
					$fields['category'],
					self::get_subfields_seconds( $fields['increment'] ),
					$fields['date_type'],
				);

				wp_schedule_event( strtotime( $fields['first_fire'] ), $timing_name, $event_name, $variables );
			}
		}
	}

	/**
	 * Get subfields seconds.
	 *
	 * @since   1.0.0
	 *
	 * @param  array $subfields  Time subfields array.
	 * @return int               Seconds
	 */
	public static function get_subfields_seconds( $subfields ) {
		$subfields = array_map( 'intval', (array) $subfields );
		$subfields = array_filter( $subfields );

		$seconds = 0;
		foreach ( $subfields as $field_name => $field_value ) {
			switch ( $field_name ) {
				case 'days':
					$seconds += $field_value * 60 * 60 * 24;
					break;

				case 'hours':
					$seconds += $field_value * 60 * 60;
					break;

				case 'minutes':
					$seconds += $field_value * 60;
					break;
			}
		}

		return $seconds;
	}

	/**
	 * Get cron events list.
	 *
	 * @since   1.0.0
	 *
	 * @return array  Cron events list.
	 */
	public static function get_cron_events() {
		return get_option( 'udate_scheduled_events', array() );
	}

	/**
	 * Get cron schedule intervals.
	 *
	 * @since   1.0.0
	 *
	 * @WP_Hook cron_schedules
	 *
	 * @param  array $schedules  Current time intervals.
	 * @return array             Cron schedule intervals.
	 */
	public static function cron_intervals( $schedules ) {
		$udate_intervals = get_option( 'udate_cron_schedule_intervals', array() );

		return array_merge( $schedules, $udate_intervals );
	}
}