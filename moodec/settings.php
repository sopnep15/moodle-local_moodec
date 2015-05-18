<?php
/**
 * Moodec Settings file
 *
 * @package     local
 * @subpackage  local_moodec
 * @author   	Thomas Threadgold
 * @copyright   2015 LearningWorks Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once $CFG->dirroot . '/local/moodec/lib.php';

if ($hassiteconfig) {
	// needs this condition or there is error on login page

	//
	// Add category to root
	//
	$ADMIN->add(
		'root',
		new admin_category(
			'moodec',
			get_string(
				'pluginname',
				'local_moodec'
			)
		)
	);

	//
	// Add settings link to root category
	//
	$ADMIN->add(
		'moodec',
		new admin_externalpage(
			'moodecsettings',
			get_string(
				'moodec_settings',
				'local_moodec'
			),
			$CFG->wwwroot . '/admin/settings.php?section=local_moodec_settings',
			'moodle/course:update'
		)
	);

	$ADMIN->add(
		'moodec',
		new admin_externalpage(
			'moodecpages',
			get_string(
				'moodec_pages',
				'local_moodec'
			),
			$CFG->wwwroot . '/admin/settings.php?section=local_moodec_pages',
			'moodle/course:update'
		)
	);

	//
	// Add category to local plugins category
	//
	$ADMIN->add(
		'localplugins',
		new admin_category(
			'local_moodec',
			get_string(
				'pluginname',
				'local_moodec'
			)
		)
	);

	//
	// Add generic settings page
	//
	$settings = new admin_settingpage(
		'local_moodec_settings',
		get_string(
			'moodec_settings',
			'local_moodec'
		)
	);
	$ADMIN->add('local_moodec', $settings);

	$settings->add(
		new admin_setting_configtext(
			'local_moodec/paypalbusiness',
			get_string(
				'businessemail',
				'local_moodec'
			),
			get_string(
				'businessemail_desc',
				'local_moodec'
			),
			'',
			PARAM_EMAIL
		)
	);

	$paypalcurrencies = local_moodec_get_currencies();
	$settings->add(
		new admin_setting_configselect(
			'local_moodec/currency',
			get_string(
				'currency',
				'local_moodec'
			),
			'',
			'USD',
			$paypalcurrencies
		)
	);

	$settings->add(
		new admin_setting_configtext(
			'local_moodec/pagination',
			get_string(
				'pagination',
				'local_moodec'
			),
			get_string(
				'pagination_desc',
				'local_moodec'
			),
			10,
			PARAM_INT
		)
	);

	//
	// Add page settings
	//
	$pages = new admin_settingpage(
		'local_moodec_pages',
		get_string(
			'moodec_pages',
			'local_moodec'
		)
	);
	$ADMIN->add('local_moodec', $pages);

	// Catalogue Page
	$pages->add(
		new admin_setting_heading(
			'local_moodec/page_setting_heading_catalogue',
			get_string(
				'page_setting_heading_catalogue',
				'local_moodec'
			),
			''
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_catalogue_show_description',
			get_string(
				'page_catalogue_show_description',
				'local_moodec'
			),
			get_string(
				'page_catalogue_show_description_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_catalogue_show_additional_description',
			get_string(
				'page_catalogue_show_additional_description',
				'local_moodec'
			),
			get_string(
				'page_catalogue_show_additional_description_desc',
				'local_moodec'
			),
			0
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_catalogue_show_duration',
			get_string(
				'page_catalogue_show_duration',
				'local_moodec'
			),
			get_string(
				'page_catalogue_show_duration_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_catalogue_show_image',
			get_string(
				'page_catalogue_show_image',
				'local_moodec'
			),
			get_string(
				'page_catalogue_show_image_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_catalogue_show_category',
			get_string(
				'page_catalogue_show_category',
				'local_moodec'
			),
			get_string(
				'page_catalogue_show_category_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_catalogue_show_price',
			get_string(
				'page_catalogue_show_price',
				'local_moodec'
			),
			get_string(
				'page_catalogue_show_price_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_catalogue_show_button',
			get_string(
				'page_catalogue_show_button',
				'local_moodec'
			),
			get_string(
				'page_catalogue_show_button_desc',
				'local_moodec'
			),
			1
		)
	);

	// Product page
	$pages->add(
		new admin_setting_heading(
			'local_moodec/page_setting_heading_product',
			get_string(
				'page_setting_heading_product',
				'local_moodec'
			),
			''
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_product_enable',
			get_string(
				'page_product_enable',
				'local_moodec'
			),
			get_string(
				'page_product_enable_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_product_show_image',
			get_string(
				'page_product_show_image',
				'local_moodec'
			),
			get_string(
				'page_product_show_image_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_product_show_description',
			get_string(
				'page_product_show_description',
				'local_moodec'
			),
			get_string(
				'page_product_show_description_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_product_show_additional_description',
			get_string(
				'page_product_show_additional_description',
				'local_moodec'
			),
			get_string(
				'page_product_show_additional_description_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_product_show_category',
			get_string(
				'page_product_show_category',
				'local_moodec'
			),
			get_string(
				'page_product_show_category_desc',
				'local_moodec'
			),
			1
		)
	);

	$pages->add(
		new admin_setting_configcheckbox(
			'local_moodec/page_product_show_related_products',
			get_string(
				'page_product_show_related_products',
				'local_moodec'
			),
			get_string(
				'page_product_show_related_products_desc',
				'local_moodec'
			),
			1
		)
	);
}