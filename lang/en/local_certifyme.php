<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// @package    local_certifyme
// @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

defined('MOODLE_INTERNAL') || die();

$string['pluginname']       = 'CertifyMe';
$string['signup_heading']   = 'New to CertifyMe?';
$string['signup_desc']      = 'Don\'t have a CertifyMe account yet? Create one on your selected server — it\'s free to get started.';
$string['signup_button']    = 'Sign Up on CertifyMe';
$string['server']           = 'CertifyMe Server';
$string['server_desc']      = 'Select the CertifyMe server your account is hosted on.';
$string['apitoken']         = 'CertifyMe API Token';
$string['apitoken_desc']    = 'Enter your CertifyMe API token from the CertifyMe dashboard.';
$string['templateid']       = 'Credential Template ID';
$string['templateid_desc']  = 'Enter the template ID of the credential to issue on course completion.';
$string['text']              = 'Text';
$string['text_desc']         = 'Optional text shown on the credential — e.g. a job title or organisation name (e.g. VP Quadralogics). Leave blank to send empty.';
$string['verify_mode']          = 'Verification Mode';
$string['verify_mode_desc']     = 'Select the verification method required by your CertifyMe template.';
$string['verify_mode_none']     = 'None';
$string['verify_mode_ssn']      = 'SSN';
$string['verify_mode_code']     = 'Code';
$string['verify_mode_passport'] = 'Passport Number';
$string['verify_code']          = 'Verification Code';
$string['verify_code_desc']     = 'Required when Verification Mode is not "None" (e.g. 13678AJKJY678JHGP0). Leave blank if not used.';
$string['license_number']       = 'License Number';
$string['license_number_desc']  = 'Optional license number to attach to the credential (e.g. TPR-1267Af23). Leave blank if not used.';
$string['customfields']      = 'Custom Fields';
$string['customfields_desc'] = 'Map your CertifyMe template custom fields to values. One entry per line in the format: FieldName=value

"Custom." is added automatically — just enter the part after it.

Available tokens for values:
  {course_name}    — Moodle course name
  {student_name}   — Student full name
  {student_email}  — Student email
  {date}           — Completion date (e.g. 13 May 2026)

Example:
  CourseName={course_name}
  eventdate={date}
  Organization=Acme University
  Country=India';
$string['privacy:metadata:certifyme_api']         = 'CertifyMe API';
$string['privacy:metadata:certifyme_api:name']    = 'Student full name sent to CertifyMe';
$string['privacy:metadata:certifyme_api:email']   = 'Student email sent to CertifyMe';
$string['privacy:metadata:certifyme_api:course']  = 'Course name sent to CertifyMe';
