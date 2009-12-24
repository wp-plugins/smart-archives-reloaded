<?php

class SAR_Settings extends scbAdminPage {
	function __construct($file, $options) {
		$this->textdomain = 'smart-archives-reloaded';

		$this->args = array(
			'page_title' => __('Smart Archives Settings', $this->textdomain),
			'menu_title' => __('Smart Archives', $this->textdomain),
			'page_slug' => 'smart-archives'
		);

		parent::__construct($file, $options);
	}

	// Page methods
	function page_head() {
		wp_enqueue_script('sar-admin', $this->plugin_url . 'inc/admin.js', array('jquery'), '1.7', true);
		echo $this->css_wrap('h3 {margin-bottom: 0 !important}');
	}

	function validate($new_options, $old_options) {
		// Validate numeric
		if ( $new_options['format'] == 'list' )
			$new_options['block_numeric'] = false;

		// Validate anchors
		if ( $new_options['format'] != 'both' )
			$new_options['anchors'] = false;

		// Validate cat ids
		$ids = array();
		foreach ( explode(', ', $new_options['exclude_cat']) as $id ) {
			$id = intval($id);
			if ( $id > 0 )
				$ids[] = $id;
		}
		$new_options['exclude_cat'] = array_unique($ids);

		// List format
		$new_options['list_format'] = trim($new_options['list_format']);
		if ( empty($new_options['list_format']) )
			$new_options['list_format'] = $this->options->defaults['list_format'];

		return $new_options;
	}

	function form_handler() {
		if ( isset($_POST['action']) && $_POST['action'] == __('Clear', $this->textdomain) ) {
			SAR_Core::$override_cron = true;
			SAR_Core::update_cache();
			$this->admin_msg(__('Cache <strong>cleared</strong>.', $this->textdomain));
		} else {
			parent::form_handler();
		}
	}

	function page_content() {
		foreach ( SAR_Core::get_available_tags() as $tag )
			$tags .= "\n\t" . html('li', html('em', $tag));

		$default_date = __('Default', $this->textdomain) . ': F j, Y (' . date_i18n("F j, Y") . ')';

		$output = $this->_subsection(__('General settings', $this->textdomain), 'general', array(
			array(
				'title' => __('Exclude Categories by ID', $this->textdomain),
				'desc' => __('(comma separated)', $this->textdomain),
				'type' => 'text',
				'name' => 'exclude_cat',
				'value' => implode(', ', (array) $this->options->exclude_cat)
			),

			array(
				'title' => __('Format', $this->textdomain),
				'type' => 'radio',
				'name' => 'format',
				'value' => array('block', 'list', 'both', 'fancy'),
				'desc' => array(
					__('block', $this->textdomain),
					__('list', $this->textdomain),
					__('both', $this->textdomain),
					__('fancy', $this->textdomain),
				)
			),
		))

		.$this->_subsection(__('Specific settings', $this->textdomain), 'specific', array(
			array(
				'title' => __('List format', $this->textdomain),
				'desc' => html('p', __('Available substitution tags', $this->textdomain))
					.html('ul', $tags),
				'type' => 'text',
				'name' => 'list_format',
			),

			array(
				'title' => sprintf(__('%s format', $this->textdomain), '%date%'),
				'desc' => html('p', $default_date)
					.html('p', 
						html('em', __('See available date formatting characters <a href="http://php.net/date" target="_blank">here</a>.'
						, $this->textdomain))
					),
				'type' => 'text',
				'name' => 'date_format',
			),

			array(
				'title' => __('Numeric months in block', $this->textdomain),
				'desc' => __('The month links in the block will be shown as numbers', $this->textdomain),
				'type' => 'checkbox',
				'name' => 'block_numeric',
			),

			array(
				'title' => __('Use anchor links in block', $this->textdomain),
				'desc' => __('The month links in the block will point to the month links in the list', $this->textdomain),
				'type' => 'checkbox',
				'name' => 'anchors',
			),
		))

		.$this->_subsection(__('Cache control', $this->textdomain), 'cache', array(
			array(
				'title' => __('Use wp-cron', $this->textdomain),
				'type' => 'checkbox',
				'name' => 'cron'
			),

			array(
				'title' => __('Clear cache', $this->textdomain),
				'type' => 'submit',
				'name' => 'action',
				'value' => __('Clear', $this->textdomain),
				'extra' => 'class="button no-ajax"',
			),
		));

		echo $this->form_wrap($output);
	}

	function _subsection($title, $id, $rows) {
		return html("div id='$id'", 
			"\n" . html('h3', $title)
			."\n" . $this->table($rows)
		, "\n");
	}
}

