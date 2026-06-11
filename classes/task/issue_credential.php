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

namespace local_certifyme\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Adhoc task: issue a CertifyMe credential for one completed enrolment.
 *
 * Queued by the event observer so the HTTP call never blocks the user request.
 * Custom data: {userid, courseid}
 */
class issue_credential extends \core\task\adhoc_task {

    public function execute() {
        $data     = $this->get_custom_data();
        $userid   = $data->userid;
        $courseid = $data->courseid;

        $apitoken       = get_config('local_certifyme', 'apitoken');
        $templateid     = get_config('local_certifyme', 'templateid');
        $server         = get_config('local_certifyme', 'server');
        $text           = get_config('local_certifyme', 'text');
        $verify_mode    = get_config('local_certifyme', 'verify_mode') ?: 'None';
        $verify_code    = get_config('local_certifyme', 'verify_code');
        $license_number = get_config('local_certifyme', 'license_number');
        $customfields   = get_config('local_certifyme', 'customfields');

        if (empty($apitoken) || empty($templateid)) {
            mtrace('CertifyMe: API token or template ID not configured — skipping credential issuance.');
            return;
        }

        $user   = \core_user::get_user($userid);
        $course = get_course($courseid);

        if (!$user || !$course) {
            return;
        }

        $payload = [
            'name'        => fullname($user),
            'email'       => $user->email,
            'template_ID' => $templateid,
            'text'        => (string) $text,
            'verify_mode' => $verify_mode,
        ];

        if (!empty($verify_code)) {
            $payload['verify_code'] = $verify_code;
        }
        if (!empty($license_number)) {
            $payload['license_number'] = $license_number;
        }

        foreach (self::parse_custom_fields((string) $customfields, $user, $course) as $key => $value) {
            $payload[$key] = $value;
        }

        $curl = new \curl();
        $curl->setHeader([
            'Authorization: ' . $apitoken,
            'Content-Type: application/json',
            'accept: application/json',
        ]);

        $response = $curl->post(\local_certifyme\servers::endpoint($server), json_encode($payload), [
            'CURLOPT_TIMEOUT' => 15,
        ]);

        $info     = $curl->get_info();
        $httpcode = $info['http_code'] ?? 0;

        if ($curl->get_errno()) {
            // Throwing here lets Moodle's task infrastructure retry the task.
            throw new \moodle_exception('generalexception', 'error', '', null,
                'CertifyMe: network error — ' . $curl->error);
        }

        if ($httpcode !== 200) {
            throw new \moodle_exception('generalexception', 'error', '', null,
                'CertifyMe [' . $server . '] HTTP ' . $httpcode . ': ' . $response);
        }
    }

    /**
     * Parse admin's custom-fields textarea into API payload entries.
     *
     * Format (one per line):  FieldName=value
     * "Custom." is prepended automatically to every field name.
     *
     * Supported tokens:
     *   {course_name}   {student_name}   {student_email}   {date}
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

            if (strpos($name, 'Custom.') === 0) {
                $name = substr($name, strlen('Custom.'));
            }
            $name = 'Custom.' . $name;

            $fields[$name] = strtr($value, $tokens);
        }

        return $fields;
    }
}
