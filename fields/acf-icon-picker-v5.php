<?php

if( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('acf_field_icon_picker') ) :

class acf_field_icon_picker extends acf_field {

	function __construct( $settings ) {

		$this->name = 'icon-picker';

		$this->label = __('Icon Picker', 'acf-icon-picker');

		$this->category = 'jquery';

		$this->defaults = array(
			'initial_value'	=> '',
		);

		$this->l10n = array(
			'error'	=> __('Error!', 'acf-icon-picker'),
		);

		$this->settings = $settings;

		$this->path_suffix = apply_filters( 'acf_icon_path_suffix', 'assets/img/acf/' );
		$this->parent_path_suffix = apply_filters( 'acf_icon_parent_path_suffix', false );

		$this->path = get_stylesheet_directory() . '/' . $this->path_suffix;

		if ( is_dir( $this->path ) ) {
			$this->url = get_stylesheet_directory_uri() . '/' . $this->path_suffix;
		} else {
			$this->path = $this->settings['path'] . $this->path_suffix;
			$this->url = $this->settings['url'] . $this->path_suffix;
		}

		if ($this->parent_path_suffix) {
			$this->parent_path = get_template_directory() . '/' . $this->parent_path_suffix;
		}
		if ( is_dir( $this->parent_path ) ) {
			$this->parent_url = get_template_directory_uri() . '/' . $this->path_suffix;
		} else {
			$this->parent_path = false;
			$this->parent_url = false;
		}


		$this->svgs = array();

		$is_path = is_dir($this->path);
		
		$files = is_dir($this->path) ? scandir($this->path) : array();
		$files = array_map(function ($file) {
			return $this->path . $file;
		}, $files);
		
		$parent_files = is_dir($this->parent_path) ? scandir($this->parent_path) : array();
		$parent_files = array_map(function ($file) {
			if ( in_array ($file, array('.', '..'))) {
				return $file;
			}
			return $this->parent_path . $file;
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

	function render_field( $field ) {
		$input_value = $field['value'] != "" ? $field['value'] : $field['initial_value'];
		$input_array = json_decode($input_value, JSON_OBJECT_AS_ARRAY);
		$svg = array();
		$svg['icon'] = $this->path . $input_array['icon'] . '.svg';
		if (file_exists($svg['icon'])) {
			$svg['url'] = $this->url . $input_array['icon'] . '.svg';
			$svg['location'] = 'current';
		}
		elseif ( $this->parent_path ) {
			$svg['icon'] = $this->parent_path . $input_array['icon'] . '.svg';
			$svg['url'] = $this->parent_url . $input_array['icon'] . '.svg';
			$svg['location'] = 'parent';
		}
		?>
			<div class="acf-icon-picker">
				<div class="acf-icon-picker__img">
					<?php
						if ( file_exists( $svg['icon'] ) ) {
							echo '<div class="acf-icon-picker__svg">';
						   	echo '<img src="'.$svg['url'].'" alt=""/>';
						    echo '</div>';
						}else{
							echo '<div class="acf-icon-picker__svg">';
							echo '<span class="acf-icon-picker__svg--span">&plus;</span>';
						    echo '</div>';
						}
					?>
					<input type="hidden" readonly name="<?php echo esc_attr($field['name']) ?>" value="<?php json_encode($svg) ?>"/>
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
new acf_field_icon_picker( $this->settings );

endif;

?>
