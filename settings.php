<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// @package    local_certifyme
// @copyright  2026 CertifyMe (https://www.certifyme.online)
// @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_certifyme',
        get_string('pluginname', 'local_certifyme')
    );
    $ADMIN->add('localplugins', $settings);

    // SERVER SELECTOR — list is driven by classes/servers.php
    $settings->add(new admin_setting_configselect(
        'local_certifyme/server',
        get_string('server', 'local_certifyme'),
        get_string('server_desc', 'local_certifyme'),
        \local_certifyme\servers::default_key(),
        \local_certifyme\servers::dropdown()
    ));

    // API TOKEN
    $settings->add(new admin_setting_configtext(
        'local_certifyme/apitoken',
        get_string('apitoken', 'local_certifyme'),
        get_string('apitoken_desc', 'local_certifyme'),
        ''
    ));

    // TEMPLATE ID
    $settings->add(new admin_setting_configtext(
        'local_certifyme/templateid',
        get_string('templateid', 'local_certifyme'),
        get_string('templateid_desc', 'local_certifyme'),
        ''
    ));
}
