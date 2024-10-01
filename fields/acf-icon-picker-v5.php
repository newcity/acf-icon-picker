<?php

if( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('nc_acf_field_icon_picker') ) :

class nc_acf_field_icon_picker extends acf_field {

	function __construct( $settings ) {

		$this->name = 'nc-acf-icon-picker';

		$this->label = __('NC Icon Picker', 'acf-icon-picker');

		$this->category = 'jquery';

		$this->defaults = array(
			'initial_value'	=> '',
		);

		$this->l10n = array(
			'error'	=> __('Error!', 'acf-icon-picker'),
		);

		$this->settings = $settings;

		$this->path_suffix = apply_filters_deprecated( 'acf_icon_path_suffix', array('images/svg-icons'), '', 'nc_acf_icon_path_suffix', 'Version 2 of the acf-icon-picker plugin changed the name of the `acf_icon_path_suffix` to `nc_acf_icon_path_suffix`. Please update your code.' );
		$this->path_suffix = apply_filters( 'nc_acf_icon_path_suffix', 'images/svg-icons' );
		$this->parent_path_suffix = apply_filters_deprecated( 'acf_icon_parent_path_suffix', array(false), '', 'nc_acf_icon_parent_path_suffix', 'Version 2 of the acf-icon-picker plugin changed the name of the `acf_icon_parent_path_suffix` to `nc_acf_icon_parent_path_suffix`. Please update your code.' );
		$this->parent_path_suffix = apply_filters( 'nc_acf_icon_parent_path_suffix', false );

		if (! $this->parent_path_suffix ) {
			$this->parent_path_suffix = $this->path_suffix;
		}

		// If the last character of the path is a slash, remove it
		if (is_string($this->path_suffix) && substr($this->path_suffix, -1) === '/') {
			$this->path_suffix = substr($this->path_suffix, 0, -1);
		}

		$this->path = get_stylesheet_directory() . '/' . $this->path_suffix;
		
		if ( is_dir( $this->path ) ) {
			$this->url = get_stylesheet_directory_uri() . '/' . $this->path_suffix;
		} else {
			$this->path = $this->settings['path'] . $this->path_suffix;
			$this->url = $this->settings['url'] . $this->path_suffix;
		}

		// If the last character of the parent path is a slash, remove it
		if (is_string($this->parent_path_suffix) && substr($this->parent_path_suffix, -1) === '/') {
			$this->parent_path_suffix = substr($this->parent_path_suffix, 0, -1);
		}

		if ($this->parent_path_suffix) {
			$this->parent_path = get_template_directory() . '/' . $this->parent_path_suffix;
		}
		if ( isset($this->parent_path) && is_dir( $this->parent_path ) ) {
			
			$this->parent_url = get_template_directory_uri() . '/' . $this->parent_path_suffix;
		} else {
			$this->parent_path = false;
			$this->parent_url = false;
		}


		$this->svgs = array();

		$is_path = is_dir($this->path);
		
		$files = is_dir($this->path) ? scandir($this->path) : array();
		$files = array_map(function ($file) {
			return $this->path . '/' . $file;
		}, $files);
		
		$parent_files = is_dir($this->parent_path) ? scandir($this->parent_path) : array();
		$parent_files = array_map(function ($file) {
			if ( in_array ($file, array('.', '..'))) {
				return $file;
			}
			return $this->parent_path . '/' . $file;
		}, $parent_files);
		$files = array_replace($parent_files, $files);

		if (count($files)) {
			
			$files = array_diff($files, array('.', '..'));
			foreach ($files as $file) {
				if( pathinfo($file, PATHINFO_EXTENSION) == 'svg' ){
					$name = basename($file, '.svg');
					$icon = array(
						'name' => $name,
						'icon' => $name . '.svg'
					);

					$icon_location = strpos($file, $this->parent_path) !== false ? 'parent' : 'current';

					$icon['location'] = $icon_location;
					if ($icon_location !== 'parent' || ! file_exists($this->path . $name . '.svg')) {
						array_push($this->svgs, $icon);
					}
				}
			}
		}

    	parent::__construct();
	}

	function format_value( $value, $post_id, $field ) {
		if ( ! $value ) {
			return $value;
		}

		$format_type = apply_filters( 'nc_acf_icon_picker_format_type', null );

		if ( ! $format_type ) {
			return $value;
		}

		$parsed_value = json_decode($value, true);

		if ( $format_type === 'string' ) {
			if ( is_array( $parsed_value ) && array_key_exists( 'icon', $parsed_value ) ) {
				return $parsed_value['icon'];
			}
			if ( is_string( $value ) ) {
				return $value;
			}
			return '';
		}

		if ( $format_type === 'array' || $format_type === 'json' ) {
			if ( is_array( $parsed_value ) && array_key_exists( 'icon', $parsed_value ) ) {
				if ( $format_type === 'json' ) {
					return $value;
				}
				return $parsed_value;
			}

			if ( is_string( $value ) ) {
				$value_array = array( 'icon' => $value );

				if ( $format_type === 'json' ) {
					return json_encode( $value_array );
				}
				return $value_array;
			}

			return false;
		}

	}

	function render_field( $field ) {
		$input_value = $field['value'] != "" ? $field['value'] : $field['initial_value'];
		$input_array = json_decode($input_value, JSON_OBJECT_AS_ARRAY);
		$svg = array();
		if ($input_array === NULL) {
			if ($input_value) {
				$input_array = array(
					'icon' => $input_value
				);
			}
		}
		if (isset($input_array['icon'])) {
			$svg['icon'] = basename($input_array['icon'], '.svg');
			$svg['path'] = $this->path . '/' . $svg['icon'] . '.svg';
			if (file_exists($svg['path'])) {
				$svg['url'] = $this->url . '/' . $svg['icon'] . '.svg';
				$svg['location'] = 'current';
			} elseif ( $this->parent_path ) {
				$svg['path'] = $this->parent_path . '/' . $svg['icon'] . '.svg';
				$svg['url'] = $this->parent_url . '/' . $svg['icon'] . '.svg';
				$svg['location'] = 'parent';
			}
			$svg_encoded = json_encode($svg);
		} else {
			$svg_encoded = null;
		}
		
		?>
			<div class="acf-icon-picker">
				<div class="acf-icon-picker__img">
					<?php
						if ( isset( $svg['path'] ) && file_exists( $svg['path'] ) ) {
							echo '<div class="acf-icon-picker__svg">';
						   	echo '<img src="'.$svg['url'].'" alt=""/>';
						    echo '</div>';
						}else{
							echo '<div class="acf-icon-picker__svg">';
							echo '<span class="acf-icon-picker__svg--span">&plus;</span>';
						    echo '</div>';
						}
					?>
					<input type="hidden" readonly name="<?php echo esc_attr($field['name']) ?>" value='<?php echo $svg_encoded ?>'/>
				</div>
				<?php if ( $field['required' ] == false ) { ?>
					<span class="acf-icon-picker__remove">
						Remove
					</span>
				<?php } ?>
			</div>
		<?php
	}

	function input_admin_enqueue_scripts() {

		$url = $this->settings['url'];
		$version = $this->settings['version'];

		wp_register_script( 'acf-input-icon-picker', "{$url}assets/js/input.js", array('acf-input'), $version );
		wp_enqueue_script('acf-input-icon-picker');

		wp_localize_script( 'acf-input-icon-picker', 'iv', array(
			'path' => $this->url,
			'parent_path' => $this->parent_url,
			'svgs' => $this->svgs,
			'no_icons_msg' => sprintf( esc_html__('To add icons, add your svg files in the /%s folder in your theme.', 'acf-icon-picker'), $this->path_suffix)
		) );

		wp_register_style( 'acf-input-icon-picker', "{$url}assets/css/input.css", array('acf-input'), $version );
		wp_enqueue_style('acf-input-icon-picker');
	}
}
new nc_acf_field_icon_picker( $this->settings );

endif;

?>
