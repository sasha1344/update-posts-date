<?php
/**
 * No Direct Access
 */
defined( 'ABSPATH' ) or die;

/**
 * Options Page for Plugin
 */
class UdateOptionsPage
{
	/**
	 * Options page Slug.
	 *
	 * @since   1.0.0
	 * @access  private
	 * @var     string   $page_slug  Options page Slug.
	 */
	private $page_slug = 'update-posts-date';

	/**
	 * Register options page in admin menu.
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function register_menu() {
		add_menu_page( 'Update Posts Date', 'Update Posts Date', 'manage_options', $this->page_slug, array( $this, 'options_page' ), 'dashicons-calendar-alt', 100 );
	}

	/**
	 * Register the JavaScript & Styles for the admin area.
	 *
	 * @since   1.0.0
	 * @param string $hook Page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_update-posts-date' === $hook ) {

			// Scripts.
			wp_enqueue_script( $this->page_slug, UDATE_PLUGIN_URL . '/assets/js/udate.js', array( 'jquery' ), UDATE_VERSION, false );

			// Styles.
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_style( $this->page_slug, UDATE_PLUGIN_URL . '/assets/css/udate.css', array(), UDATE_VERSION, 'all' );
		}
	}

	/**
	 * Get default Fields.
	 *
	 * @since  1.0.0
	 */
	private static function get_default_fields() {
		$fire_time = date_create('+ 10 minutes');

		return array(
			'category' => '',
			'frequency'  => array(
				'days'    => '',
				'hours'   => '',
				'minutes' => '',
			),
			'increment'  => array(
				'days'    => '',
				'hours'   => '',
				'minutes' => '',
			),
			'date_type'  => 'updated',
			'first_fire' => date_format( $fire_time, 'Y-m-d H:i:s'),
			'errors'     => array(),
		);
	}

	/**
	 * Get errors html.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $errors  List of row errros
	 * @return string         Errors HTML.
	 */
	private function get_errors_html( $errors, $column ) {
		if ( is_array( $errors ) && isset( $errors[ $column ] ) ) {
			$html = sprintf('<div class="udate-error">%s</div>', (string) $errors[ $column ] );

			// Return filtered HTML.
			$allowed_tags = array(
				'div' => array(
					'class' => true,
				),
				'strong' => array(),
				'em'     => array(),
			);

			return wp_kses( $html, $allowed_tags );
		}

		return '';
	}

	/**
	 * Options page content.
	 *
	 * @since  1.0.0
	 */
	public function options_page() {
		?>
		<div class="wrap udate-wrap">
			<h1><?php esc_html_e( 'Auto Update Posts Date', 'udate' ); ?></h1>

			<?php
			// Save rules on form saving.
			$this->save_options();
			?>

			<form class="udate-rules-form" method="post">
				<h2><?php esc_html_e( 'Server Time', 'udate' ); ?></h2>

				<div class="server-time"><?php echo date('Y-m-d H:i:s'); ?></div>

				<h2><?php esc_html_e( 'Rules', 'udate' ); ?></h2>

				<table class="udate-table">
					<?php
					$counter = 0;
					foreach ( $this->get_saved_rules(true) as $row_id => $fields ) {
						if ( 'clone' !== $row_id ) {
							$counter++;
						}

						$fields = array_merge( self::get_default_fields(), (array) $fields );
						?>
						<tr class="udate-row <?php echo esc_attr( 'clone' === $row_id ? 'rule-clone-row' : 'rule-row' ); ?>">
							<td class="col-action col-num" scope="row">
								<div class="counter"><?php echo esc_attr( $counter ); ?></div>
							</td>

							<td>
								<!-- Category -->
								<div class="udate-field category-field">
									<label class="udate-label"><?php esc_html_e( 'Category', 'udate' ); ?></label>
									<div class="udate-input">
										<select name="udate_rules[<?php echo esc_attr( $row_id ); ?>][category]">
											<option value="">-</option>
											<?php
											$items = get_categories( array(
												'hide_empty' => 0,
												'fields'     => 'id=>name',
											) );

											foreach ( $items as $item_value => $item_name ) {
												printf(
													'<option value="%1$s" %2$s>%3$s</option>',
													esc_attr( $item_value ),
													selected( $item_value, $fields['category'] ),
													esc_attr( $item_name )
												);
											}
											?>
										</select>
									</div>

									<?php echo $this->get_errors_html( $fields['errors'], 'category' ); ?>
								</div>
							</td>

							<td>
								<!-- Frequency -->
								<div class="udate-field frequency-field">
									<label class="udate-label"><?php esc_html_e( 'Frequency', 'udate' ); ?></label>
									<div class="udate-input">
										<div class="subfield-wrap">
											<span>Days</span>
											<input name="udate_rules[<?php echo esc_attr( $row_id ); ?>][frequency][days]" type="number" min="0" value="<?php echo esc_attr( $fields['frequency']['days'] ); ?>" />
										</div>

										<div class="subfield-wrap">
											<span>Hours</span>
											<select name="udate_rules[<?php echo esc_attr( $row_id ); ?>][frequency][hours]">
												<option value="">-</option>
												<?php
												for ( $i = 1; $i <= 24; $i++ ) {
													printf( '<option value="%1$d" %2$s>%1$d</option>', $i, selected( $i, $fields['frequency']['hours'] ) );
												}
												?>
											</select>
										</div>

										<div class="subfield-wrap">
											<span>Minutes</span>
											<select name="udate_rules[<?php echo esc_attr( $row_id ); ?>][frequency][minutes]">
												<option value="">-</option>
												<?php
												for ( $i = 1; $i <= 60; $i++ ) {
													printf( '<option value="%1$d" %2$s>%1$d</option>', $i, selected( $i, $fields['frequency']['minutes'] ) );
												}
												?>
											</select>
										</div>
									</div>

									<p class="description">How often to update the date of the posts? Once a..</p>

									<?php echo $this->get_errors_html( $fields['errors'], 'frequency' ); ?>
								</div>
							</td>

							<td>
								<!-- Increment Post Dates -->
								<div class="udate-field increment-field">
									<label class="udate-label"><?php esc_html_e( 'Increment Date', 'udate' ); ?></label>
									<div class="udate-input">
										<div class="subfield-wrap">
											<span>Days</span>
											<input name="udate_rules[<?php echo esc_attr( $row_id ); ?>][increment][days]" type="number" min="0" value="<?php echo esc_attr( $fields['increment']['days'] ); ?>" />
										</div>

										<div class="subfield-wrap">
											<span>Hours</span>
											<select name="udate_rules[<?php echo esc_attr( $row_id ); ?>][increment][hours]">
												<option value="">-</option>
												<?php
												for ( $i = 1; $i <= 24; $i++ ) {
													printf( '<option value="%1$d" %2$s>%1$d</option>', $i, selected( $i, $fields['increment']['hours'] ) );
												}
												?>
											</select>
										</div>

										<div class="subfield-wrap">
											<span>Minutes</span>
											<select name="udate_rules[<?php echo esc_attr( $row_id ); ?>][increment][minutes]">
												<option value="">-</option>
												<?php
												for ( $i = 1; $i <= 60; $i++ ) {
													printf( '<option value="%1$d" %2$s>%1$d</option>', $i, selected( $i, $fields['increment']['minutes'] ) );
												}
												?>
											</select>
										</div>
									</div>

									<p class="description">How many time to increment for the date of each post?</p>

									<?php echo $this->get_errors_html( $fields['errors'], 'increment' ); ?>
								</div>
							</td>

							<td>
								<!-- Date Type -->
								<div class="udate-field date-type-field">
									<label class="udate-label"><?php esc_html_e( 'Date Type', 'udate' ); ?></label>
									<div class="udate-input">
										<select name="udate_rules[<?php echo esc_attr( $row_id ); ?>][date_type]">
											<option value="updated" <?php selected( 'updated', $fields['date_type'] ); ?>>Updated date</option>
											<option value="published" <?php selected( 'published', $fields['date_type'] ); ?>>Published date</option>
											<option value="both" <?php selected( 'both', $fields['date_type'] ); ?>>Both Dates</option>
										</select>
									</div>

									<p class="description">Which dates to update?</p>

									<?php echo $this->get_errors_html( $fields['errors'], 'date_type' ); ?>
								</div>
							</td>

							<td>
								<!-- First Fire at -->
								<div class="udate-field date-type-field">
									<label class="udate-label"><?php esc_html_e( 'First Fire at', 'udate' ); ?></label>
									<div class="udate-input">
										<input name="udate_rules[<?php echo esc_attr( $row_id ); ?>][first_fire]" type="text" value="<?php echo esc_attr( $fields['first_fire'] ); ?>" />
									</div>

									<div class="description">
										Start cron from this server time.<br>
										Any date format, for example:<br>
										<ul>
											<li>+ 10 minutes</li>
											<li>2019-07-15 15:30:00</li>
										</ul>
									</div>

									<?php echo $this->get_errors_html( $fields['errors'], 'first_fire' ); ?>
								</div>
							</td>

							<td class="col-action col-delete" data-box="action">
								<a class="udate-delete-icon" href="#" data-event="remove-row" title="Delete row">-</a>
							</td>
						</tr>
					<?php } // End foreach. ?>
				</table>

				<div class="udate-not-found" style="display: <?php echo esc_attr( count( $this->get_saved_rules() ) > 0 ? 'none' : 'block' ); ?>">
					<?php esc_html_e( 'Rules Not Found. Add your first rule..', 'udate' ); ?>
				</div>

				<div class="udate-actions">
					<a class="udate-button button button-primary add-rule" href="#"><?php esc_html_e( 'Add Rule', 'udate' ); ?></a>
				</div>

				<div class="udate-submit">
					<?php wp_nonce_field( 'udate_saving', 'udate_snonce' ); ?>
					<input class="button button-primary" name="save_content_rules" type="submit" value="<?php esc_html_e( 'Save changes', 'udate' ); ?>" />
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Get Saved Rules
	 *
	 * @since 1.0.0
	 *
	 * @param bool $clone    Prepend clone field.
	 *
	 * @return array $rules  Rules args.
	 */
	public static function get_saved_rules( $clone = false ) {

		// Get Rules.
		$rules = get_option( 'udate_rules', array() );

		if ( $clone ) {
			$clone_row = array(
				'clone' => self::get_default_fields(),
			);

			$rules = $clone_row + $rules;
		}

		return $rules;
	}

	/**
	 * Save Options
	 *
	 * @since 1.0.0
	 */
	private function save_options() {
		if ( isset( $_POST['udate_saving'] ) && ! wp_verify_nonce( wp_unslash( $_POST['udate_saving'] ), 'udate_snonce' ) ) {
			return;
		}

		if ( isset( $_POST['udate_rules'] ) && is_array( $_POST['udate_rules'] ) ) {
			$rules = $_POST['udate_rules'];

			// Remove clone row.
			if ( isset( $rules['clone'] ) ) {
				unset( $rules['clone'] );
			}

			// Validate fields and set errors.
			foreach ( $rules as $row_id => $fields ) {
				$fields = array_merge( self::get_default_fields(), (array) $fields );

				// Find errors.
				$errors = array();
				if ( ! $fields['category'] || ! term_exists( (int) $fields['category'], 'category' ) ) {
					$errors['category'] = 'Invalid Category';
				}

				$frequency = array_map( 'intval', (array) $fields['frequency'] );
				$frequency = array_filter( $frequency );
				if ( empty( $frequency ) ) {
					$errors['frequency'] = 'Invalid frequency';
				}

				$increment = array_map( 'intval', (array) $fields['increment'] );
				$increment = array_filter( $increment );
				if ( empty( $increment ) ) {
					$errors['increment'] = 'Invalid increment';
				}

				$allowed_date_types = array( 'updated', 'published', 'both' );
				if ( ! in_array( (string) $fields['date_type'], $allowed_date_types, true ) ) {
					$errors['increment'] = 'Invalid increment';
				}

				if ( ! strtotime( (string) $fields['first_fire'] ) ) {
					$errors['first_fire'] = 'Invalid first fire date';
				}

				// Set errors to the row.
				$rules[ $row_id ]['errors'] = $errors;
			}

			// Save options to database.
			update_option( 'udate_rules', $rules );

			// Schedule new events.
			UdateSchedule::schedule();

			// Success message.
			printf( '<div id="message" class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html__( 'The options are successfully saved.', 'udate' ) );
		}
	}
}
