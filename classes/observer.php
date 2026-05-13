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

namespace local_certifyme;

defined('MOODLE_INTERNAL') || die();

class observer {

    public static function course_completed(\core\event\course_completed $event) {

        $apitoken       = get_config('local_certifyme', 'apitoken');
        $templateid     = get_config('local_certifyme', 'templateid');
        $server         = get_config('local_certifyme', 'server');
        $text           = get_config('local_certifyme', 'text');
        $verify_mode    = get_config('local_certifyme', 'verify_mode') ?: 'None';
        $verify_code    = get_config('local_certifyme', 'verify_code');
        $license_number = get_config('local_certifyme', 'license_number');
        $customfields   = get_config('local_certifyme', 'customfields');

        if (empty($apitoken) || empty($templateid)) {
            debugging('CertifyMe: API token or template ID not set.', DEBUG_DEVELOPER);
            return;
        }

        $user   = \core_user::get_user($event->relateduserid);
        $course = get_course($event->courseid);

        if (!$user || !$course) {
            return;
        }

        $data = [
            'name'        => fullname($user),
            'email'       => $user->email,
            'template_ID' => $templateid,
            'text'        => (string) $text,
            'verify_mode' => $verify_mode,
        ];

        if (!empty($verify_code)) {
            $data['verify_code'] = $verify_code;
        }
        if (!empty($license_number)) {
            $data['license_number'] = $license_number;
        }

        foreach (self::parse_custom_fields((string) $customfields, $user, $course) as $key => $value) {
            $data[$key] = $value;
        }

        // Use Moodle's curl wrapper — respects proxy settings and SSL config.
        $curl = new \curl();
        $curl->setHeader([
            'Authorization: ' . $apitoken,
            'Content-Type: application/json',
            'accept: application/json',
        ]);

        $response = $curl->post(servers::endpoint($server), json_encode($data), [
            'CURLOPT_TIMEOUT' => 15,
        ]);

        $info     = $curl->get_info();
        $httpcode = $info['http_code'] ?? 0;

        if ($curl->get_errno()) {
            debugging('CertifyMe: network error — ' . $curl->error, DEBUG_DEVELOPER);
            return;
        }

        if ($httpcode !== 200) {
            debugging('CertifyMe [' . $server . '] error [' . $httpcode . ']: ' . $response, DEBUG_DEVELOPER);
        }
    }

    /**
     * Parse the admin's custom fields textarea into an array ready for the API payload.
     *
     * Format (one per line):  FieldName=value
     * "Custom." is prepended automatically to every field name.
     *
     * Supported tokens in values:
     *   {course_name}    — Moodle course full name
     *   {student_name}   — Student full name
     *   {student_email}  — Student email address
     *   {date}           — Today's date in "dd Mon YYYY" format
     */
    private static function parse_custom_fields(string $raw, object $user, object $course): array {
        $fields = [];
        $tokens = [
            '{course_name}'   => $course->fullname,
            '{student_name}'  => fullname($user),
            '{student_email}' => $user->email,
            '{date}'          => date('d M Y'),
        ];

        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value);

            if ($name === '') {
                continue;
            }

            // Strip "Custom." prefix if admin already typed it, then re-add it cleanly.
            // Cannot use ltrim() here — it strips individual characters, not substrings.
            if (strpos($name, 'Custom.') === 0) {
                $name = substr($name, strlen('Custom.'));
            }
            $name = 'Custom.' . $name;

            $fields[$name] = strtr($value, $tokens);
        }

        return $fields;
    }
}
