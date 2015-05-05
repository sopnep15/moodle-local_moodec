<?php
/**
 * Moodec Settings file
 *
 * @package     local
 * @subpackage  local_moodec
 * @author   	Thomas Threadgold
 * @copyright   2015 LearningWords Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
	// needs this condition or there is error on login page

	$ADMIN->add('root', new admin_category('moodec', get_string('pluginname', 'local_moodec')));

	$ADMIN->add('moodec', new admin_externalpage('moodecsettings', 'Moodec Settings',
		$CFG->wwwroot . '/admin/settings.php?section=local_moodec', 'moodle/course:update'));

	$settings = new admin_settingpage('local_moodec', 'Moodec');
	$ADMIN->add('localplugins', $settings);

	$settings->add(new admin_setting_configtext('local_moodec/text',
		'Test', '', '', PARAM_RAW_TRIMMED));

}