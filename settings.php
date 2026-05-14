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

    // -------------------------------------------------------------------------
    // CONNECTION
    // -------------------------------------------------------------------------

    $settings->add(new admin_setting_configselect(
        'local_certifyme/server',
        get_string('server', 'local_certifyme'),
        get_string('server_desc', 'local_certifyme'),
        \local_certifyme\servers::default_key(),
        \local_certifyme\servers::dropdown()
    ));

    // Sign-up button — resolves to the currently-selected server's /auth/register/ URL.
    $settings->add(new class(
        'local_certifyme/signup_info',
        get_string('signup_heading', 'local_certifyme'),
        get_string('signup_desc', 'local_certifyme'),
        ''
    ) extends admin_setting {
        public function get_setting() {
            return true;
        }
        public function write_setting($data) {
            return '';
        }
        public function output_html($data, $query = '') {
            $currentkey  = get_config('local_certifyme', 'server') ?: \local_certifyme\servers::default_key();
            $signupurl   = \local_certifyme\servers::signup_url($currentkey);
            $allurls     = \local_certifyme\servers::signup_urls_json();
            $heading     = $this->visiblename;
            $description = $this->description;

            $html  = '<div class="form-item row">';
            $html .= '<div class="form-label col-sm-4 col-form-label">';
            $html .= '<label>' . s($heading) . '</label>';
            $html .= '</div>';
            $html .= '<div class="form-setting col-sm-8">';
            $html .= '<p class="form-control-static">' . s($description) . '</p>';
            $html .= '<a id="certifyme-signup-btn" href="' . s($signupurl) . '" target="_blank" rel="noopener noreferrer" '
                   . 'class="btn btn-primary">'
                   . get_string('signup_button', 'local_certifyme') . '</a>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<script>';
            $html .= '(function(){';
            $html .= 'var urls=' . $allurls . ';';
            $html .= 'var sel=document.getElementById("id_s_local_certifyme_server");';
            $html .= 'var btn=document.getElementById("certifyme-signup-btn");';
            $html .= 'if(sel&&btn){';
            $html .= 'sel.addEventListener("change",function(){if(urls[sel.value])btn.href=urls[sel.value];});';
            $html .= '}';
            $html .= '})();';
            $html .= '</script>';

            return $html;
        }
    });

    $settings->add(new admin_setting_configtext(
        'local_certifyme/apitoken',
        get_string('apitoken', 'local_certifyme'),
        get_string('apitoken_desc', 'local_certifyme'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_certifyme/templateid',
        get_string('templateid', 'local_certifyme'),
        get_string('templateid_desc', 'local_certifyme'),
        ''
    ));

    // -------------------------------------------------------------------------
    // STANDARD API FIELDS
    // -------------------------------------------------------------------------

    // text — free label shown on the credential (e.g. job title, organisation)
    $settings->add(new admin_setting_configtext(
        'local_certifyme/text',
        get_string('text', 'local_certifyme'),
        get_string('text_desc', 'local_certifyme'),
        ''
    ));

    // verify_mode — must match one of the four values CertifyMe accepts
    $settings->add(new admin_setting_configselect(
        'local_certifyme/verify_mode',
        get_string('verify_mode', 'local_certifyme'),
        get_string('verify_mode_desc', 'local_certifyme'),
        'None',
        [
            'None'            => get_string('verify_mode_none', 'local_certifyme'),
            'SSN'             => get_string('verify_mode_ssn', 'local_certifyme'),
            'Code'            => get_string('verify_mode_code', 'local_certifyme'),
            'Passport Number' => get_string('verify_mode_passport', 'local_certifyme'),
        ]
    ));

    // verify_code — required only when verify_mode is not "None"
    $settings->add(new admin_setting_configtext(
        'local_certifyme/verify_code',
        get_string('verify_code', 'local_certifyme'),
        get_string('verify_code_desc', 'local_certifyme'),
        ''
    ));

    // license_number
    $settings->add(new admin_setting_configtext(
        'local_certifyme/license_number',
        get_string('license_number', 'local_certifyme'),
        get_string('license_number_desc', 'local_certifyme'),
        ''
    ));

    // -------------------------------------------------------------------------
    // CUSTOM FIELDS  (template-specific — one per line: FieldName=value)
    // -------------------------------------------------------------------------

    $settings->add(new admin_setting_configtextarea(
        'local_certifyme/customfields',
        get_string('customfields', 'local_certifyme'),
        get_string('customfields_desc', 'local_certifyme'),
        ''
    ));
}
