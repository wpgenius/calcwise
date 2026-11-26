<?php
/**
 * Settings page built from one array.
 *
 * To add a setting, add one entry to fields(). It is then registered,
 * sanitised, shown on the page and readable with Api::get_setting( 'key' ).
 * All values are stored together in one option (Api::OPTION).
 *
 * Field keys: type, label, default, and optionally description, options
 * (select/radio), min/max/step (number), placeholder, vars (placeholders
 * shown under email fields).
 *
 * Types: text, textarea, editor, number, checkbox, select, radio, email,
 * url, color, time, password, and limits (a calculator's starting values and
 * limits: 'calculator' => type).
 *
 * @package WPGenius\CalcWise
 */

namespace WPGenius\CalcWise\Admin;

use WPGenius\CalcWise\Api;
use WPGenius\CalcWise\Calculator\Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Settings page.
 */
class Settings extends Api {

	/**
	 * Page slug (Settings → <plugin name>) and settings group.
	 */
	const PAGE = 'calcwise_settings';

	/**
	 * Register the page and its settings.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * The settings, grouped in sections. Edit this to add your own.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function fields() {
		$sections = array(
			'numbers'    => array(
				'title'       => __( 'Numbers and currency', 'calcwise' ),
				'description' => __( 'How amounts are written in every calculator.', 'calcwise' ),
				'fields'      => array(
					'currency_symbol'   => array(
						'type'    => 'text',
						'label'   => __( 'Currency symbol', 'calcwise' ),
						'default' => '₹',
					),
					'currency_position' => array(
						'type'    => 'select',
						'label'   => __( 'Symbol position', 'calcwise' ),
						'default' => 'before',
						'options' => array(
							'before' => __( 'Before the amount (₹1,500)', 'calcwise' ),
							'after'  => __( 'After the amount (1,500 €)', 'calcwise' ),
						),
					),
					'number_system'     => array(
						'type'    => 'select',
						'label'   => __( 'Digit grouping', 'calcwise' ),
						'default' => 'indian',
						'options' => array(
							'indian'        => __( 'Indian: 12,34,567 (lakh, crore)', 'calcwise' ),
							'international' => __( 'International: 1,234,567 (million, billion)', 'calcwise' ),
						),
					),
					'amount_in_words'   => array(
						'type'        => 'checkbox',
						'label'       => __( 'Amounts in words', 'calcwise' ),
						'description' => __( 'Show results in words too, e.g. "Two Lakh Fifty Thousand" or "Two Hundred and Fifty Thousand".', 'calcwise' ),
						'default'     => 1,
					),
				),
			),
			'look'       => array(
				'title'  => __( 'Colours', 'calcwise' ),
				'fields' => array(
					'accent_color'   => array(
						'type'        => 'color',
						'label'       => __( 'Main colour', 'calcwise' ),
						'description' => __( 'Highlighted result, sliders and the first chart series.', 'calcwise' ),
						'default'     => '#2271b1',
					),
					'accent_color_2' => array(
						'type'        => 'color',
						'label'       => __( 'Second colour', 'calcwise' ),
						'description' => __( 'The second chart series.', 'calcwise' ),
						'default'     => '#f6957f',
					),
				),
			),
			'disclaimer' => array(
				'title'  => __( 'Disclaimer', 'calcwise' ),
				'fields' => array(
					'disclaimer' => array(
						'type'        => 'textarea',
						'label'       => __( 'Text under every calculator', 'calcwise' ),
						'description' => __( 'Leave empty to show no disclaimer.', 'calcwise' ),
						'default'     => __( 'Results are estimates based on the values you enter. They are not financial advice.', 'calcwise' ),
					),
				),
			),
		);

		foreach ( Registry::all() as $type => $definition ) {
			$sections[ 'calculator_' . $type ] = array(
				'title'       => $definition['title'],
				'description' => sprintf(
					/* translators: %s: shortcode, e.g. [calcwise type="sip"]. */
					__( 'Starting values and limits for this calculator everywhere (%s and the block). Leave a box empty to keep the built-in value shown in it. A block or shortcode can still change them.', 'calcwise' ),
					'[calcwise type="' . $type . '"]'
				),
				'fields'      => array(
					'limits_' . $type => array(
						'type'       => 'limits',
						'label'      => __( 'Values', 'calcwise' ),
						'calculator' => $type,
						'default'    => array(),
					),
				),
			);
		}

		$sections['advanced'] = array(
			'title'  => __( 'Advanced', 'calcwise' ),
			'fields' => array(
				'delete_data' => array(
					'type'        => 'checkbox',
					'label'       => __( 'Delete data on uninstall', 'calcwise' ),
					'description' => __( 'When the plugin is deleted, also delete its settings.', 'calcwise' ),
					'default'     => 0,
				),
			),
		);

		/**
		 * Filters the settings sections and fields, e.g. to add a field from an add-on.
		 *
		 * @param array $sections Sections with their fields.
		 */
		return apply_filters( 'calcwise_settings_fields', $sections );
	}

	/**
	 * Every field, keyed by field key, without the sections.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function flat_fields() {
		$fields = array();
		foreach ( self::fields() as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				$fields[ $key ] = $field;
			}
		}
		return $fields;
	}

	/**
	 * Default value of every field.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array_map(
			static function ( $field ) {
				return isset( $field['default'] ) ? $field['default'] : '';
			},
			self::flat_fields()
		);
	}

	/**
	 * Add Settings → <plugin name>.
	 *
	 * @return void
	 */
	public function add_page() {
		add_options_page(
			__( 'CalcWise', 'calcwise' ),
			__( 'CalcWise', 'calcwise' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register the option, the sections and the fields from fields().
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::PAGE,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		foreach ( self::fields() as $section_id => $section ) {
			add_settings_section(
				'calcwise_' . $section_id,
				$section['title'],
				static function () use ( $section ) {
					if ( ! empty( $section['description'] ) ) {
						echo '<p>' . esc_html( $section['description'] ) . '</p>';
					}
				},
				self::PAGE
			);

			foreach ( $section['fields'] as $key => $field ) {
				$field['key'] = $key;
				add_settings_field(
					'calcwise_' . $key,
					esc_html( $field['label'] ),
					array( $this, 'render_field' ),
					self::PAGE,
					'calcwise_' . $section_id,
					array(
						'field'     => $field,
						'label_for' => in_array( $field['type'], array( 'radio', 'editor' ), true ) ? '' : 'calcwise_' . $key,
					)
				);
			}
		}
	}

	/**
	 * Sanitise the whole settings array before it is saved.
	 * A field missing from the input keeps its saved value.
	 *
	 * @param mixed $input Submitted values.
	 * @return array<string, mixed>
	 */
	public function sanitize( $input ) {
		$input   = is_array( $input ) ? $input : array();
		$current = self::get_settings();
		$clean   = array();

		foreach ( self::flat_fields() as $key => $field ) {
			$clean[ $key ] = array_key_exists( $key, $input )
				? self::sanitize_value( $field, $input[ $key ] ) // options.php has already unslashed the input.
				: $current[ $key ];
		}

		return $clean;
	}

	/**
	 * Sanitise one value by its field type. Invalid values fall back to the default.
	 *
	 * @param array<string, mixed> $field Field definition.
	 * @param mixed                $value Submitted value.
	 * @return mixed
	 */
	public static function sanitize_value( array $field, $value ) {
		$default = isset( $field['default'] ) ? $field['default'] : '';
		if ( 'limits' === $field['type'] ) {
			return self::sanitize_limits( $field, $value );
		}
		$value = is_scalar( $value ) ? (string) $value : '';

		switch ( $field['type'] ) {
			case 'checkbox':
				return '1' === $value ? 1 : 0;

			case 'number':
				if ( ! is_numeric( $value ) ) {
					return $default;
				}
				$number = ( isset( $field['step'] ) && floor( $field['step'] ) !== (float) $field['step'] ) ? (float) $value : (int) $value;
				if ( isset( $field['min'] ) ) {
					$number = max( $field['min'], $number );
				}
				if ( isset( $field['max'] ) ) {
					$number = min( $field['max'], $number );
				}
				return $number;

			case 'select':
			case 'radio':
				return isset( $field['options'][ $value ] ) ? $value : $default;

			case 'email':
				return '' === $value ? '' : sanitize_email( $value );

			case 'url':
				return esc_url_raw( $value );

			case 'color':
				$color = sanitize_hex_color( $value );
				return $color ? $color : $default;

			case 'time':
				return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : $default;

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'editor':
				return wp_kses_post( $value );

			default: // text, password.
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Sanitise a calculator's starting values and limits: only its own fields,
	 * only numbers, empty boxes dropped. The renderer checks min < max and
	 * keeps the starting value within the limits.
	 *
	 * @param array<string, mixed> $field Field definition with 'calculator'.
	 * @param mixed                $value Submitted values: field => prop => number.
	 * @return array<string, array<string, float>>
	 */
	public static function sanitize_limits( array $field, $value ) {
		$definition = isset( $field['calculator'] ) ? Registry::get( (string) $field['calculator'] ) : null;
		if ( null === $definition || ! is_array( $value ) ) {
			return array();
		}
		$clean = array();
		foreach ( array_keys( $definition['fields'] ) as $key ) {
			if ( ! isset( $value[ $key ] ) || ! is_array( $value[ $key ] ) ) {
				continue;
			}
			foreach ( array( 'default', 'min', 'max', 'step' ) as $prop ) {
				$raw = isset( $value[ $key ][ $prop ] ) && is_scalar( $value[ $key ][ $prop ] ) ? trim( (string) $value[ $key ][ $prop ] ) : '';
				if ( '' !== $raw && is_numeric( $raw ) ) {
					$clean[ $key ][ $prop ] = (float) $raw;
				}
			}
		}
		return $clean;
	}

	/**
	 * Output the settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap calcwise-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Output one field. Called by add_settings_field() with array( 'field' => ... ).
	 *
	 * @param array<string, mixed> $args Field arguments.
	 * @return void
	 */
	public function render_field( $args ) {
		$field = $args['field'];
		$key   = $field['key'];
		$id    = 'calcwise_' . $key;
		$name  = self::OPTION . '[' . $key . ']';
		$value = self::get_setting( $key );
		$ph    = isset( $field['placeholder'] ) ? $field['placeholder'] : '';

		switch ( $field['type'] ) {
			case 'checkbox':
				printf( '<input type="hidden" name="%s" value="0">', esc_attr( $name ) );
				printf(
					'<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> %4$s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( 1, (int) $value, false ),
					isset( $field['description'] ) ? esc_html( $field['description'] ) : ''
				);
				unset( $field['description'] ); // Already shown next to the box.
				break;

			case 'select':
				printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ( $field['options'] as $option => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $option ), selected( $value, $option, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'radio':
				echo '<fieldset>';
				foreach ( $field['options'] as $option => $label ) {
					printf(
						'<label><input type="radio" name="%s" value="%s" %s> %s</label><br>',
						esc_attr( $name ),
						esc_attr( $option ),
						checked( $value, $option, false ),
						esc_html( $label )
					);
				}
				echo '</fieldset>';
				break;

			case 'textarea':
				printf(
					'<textarea id="%s" name="%s" rows="5" class="large-text" placeholder="%s">%s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $ph ),
					esc_textarea( (string) $value )
				);
				break;

			case 'editor':
				wp_editor(
					(string) $value,
					$id,
					array(
						'textarea_name' => $name,
						'textarea_rows' => 8,
						'media_buttons' => false,
					)
				);
				break;

			case 'number':
				printf(
					'<input type="number" id="%s" name="%s" value="%s" class="small-text" %s %s %s>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					isset( $field['min'] ) ? 'min="' . esc_attr( $field['min'] ) . '"' : '',
					isset( $field['max'] ) ? 'max="' . esc_attr( $field['max'] ) . '"' : '',
					isset( $field['step'] ) ? 'step="' . esc_attr( $field['step'] ) . '"' : ''
				);
				break;

			case 'limits':
				self::render_limits( $field, $id, $name, is_array( $value ) ? $value : array() );
				break;

			case 'color':
				printf(
					'<input type="text" id="%s" name="%s" value="%s" class="calcwise-color-field" data-default-color="%s">',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					esc_attr( (string) $field['default'] )
				);
				break;

			default: // text, email, url, time, password.
				printf(
					'<input type="%s" id="%s" name="%s" value="%s" class="%s" placeholder="%s">',
					esc_attr( $field['type'] ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( (string) $value ),
					'time' === $field['type'] ? '' : 'regular-text',
					esc_attr( $ph )
				);
		}

		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}

		if ( ! empty( $field['vars'] ) ) {
			echo '<p class="description">' . esc_html__( 'You can use:', 'calcwise' ) . ' <code>' . implode( '</code> <code>', array_map( 'esc_html', $field['vars'] ) ) . '</code></p>';
		}
	}

	/**
	 * A calculator's starting values and limits as a small table: one row per
	 * field, empty boxes show the built-in value.
	 *
	 * @param array<string, mixed>                $field Field definition with 'calculator'.
	 * @param string                              $id    Field ID.
	 * @param string                              $name  Input name prefix.
	 * @param array<string, array<string, float>> $value Saved values.
	 * @return void
	 */
	private static function render_limits( array $field, $id, $name, array $value ) {
		$definition = Registry::get( (string) $field['calculator'] );
		if ( null === $definition ) {
			return;
		}
		$columns = array(
			'default' => __( 'Starting value', 'calcwise' ),
			'min'     => __( 'Lowest', 'calcwise' ),
			'max'     => __( 'Highest', 'calcwise' ),
			'step'    => __( 'Step', 'calcwise' ),
		);

		printf( '<table class="calcwise-limits widefat striped" id="%s"><thead><tr><th scope="col">%s</th>', esc_attr( $id ), esc_html__( 'Field', 'calcwise' ) );
		foreach ( $columns as $label ) {
			echo '<th scope="col">' . esc_html( $label ) . '</th>';
		}
		echo '</tr></thead><tbody>';

		foreach ( $definition['fields'] as $key => $def ) {
			echo '<tr><th scope="row">' . esc_html( $def['label'] ) . '</th>';
			foreach ( $columns as $prop => $label ) {
				$saved = isset( $value[ $key ][ $prop ] ) ? (string) ( 0 + $value[ $key ][ $prop ] ) : '';
				printf(
					'<td><input type="number" step="any" class="small-text" name="%1$s" value="%2$s" placeholder="%3$s" aria-label="%4$s"></td>',
					esc_attr( $name . '[' . $key . '][' . $prop . ']' ),
					esc_attr( $saved ),
					esc_attr( (string) ( 0 + $def[ $prop ] ) ),
					/* translators: 1: field name, e.g. "Monthly investment"; 2: column, e.g. "Lowest". */
					esc_attr( sprintf( __( '%1$s: %2$s', 'calcwise' ), $def['label'], $label ) )
				);
			}
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}
