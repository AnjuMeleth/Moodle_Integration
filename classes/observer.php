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

    private static $server_urls = [
        'apac'      => 'https://apac.platform.certifyme.dev/api/v2/credential',
        'eu2'       => 'https://eu2.certifyme.org/api/v2/credential',
        'us1'       => 'https://us1.certifyme.org/api/v2/credential',
        'butterfly' => 'https://butterfly.certifyme.org/api/v2/credential',
    ];

    public static function course_completed(\core\event\course_completed $event) {

        $server     = get_config('local_certifyme', 'server');
        $apitoken   = get_config('local_certifyme', 'apitoken');
        $templateid = get_config('local_certifyme', 'templateid');

        if (empty($apitoken) || empty($templateid)) {
            debugging('CertifyMe: API token or template ID not set.', DEBUG_DEVELOPER);
            return;
        }

        $endpoint = self::$server_urls[$server] ?? self::$server_urls['apac'];

        $userid   = $event->relateduserid;
        $courseid = $event->courseid;
        $user     = \core_user::get_user($userid);
        $course   = get_course($courseid);

        if (!$user || !$course) {
            return;
        }

        $payload = json_encode([
            'name'              => fullname($user),
            'email'             => $user->email,
            'template_ID'       => $templateid,
            'text'              => '',
            'verify_mode'       => 'None',
            'Custom.CourseName' => $course->fullname,
            'Custom.eventdate'  => date('d M Y'),
        ]);

        $curl = curl_init($endpoint);
        curl_setopt_array($curl, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $apitoken,
                'Content-Type: application/json',
                'accept: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode !== 200) {
            debugging('CertifyMe [' . $server . '] error [' . $httpcode . ']: ' . $response, DEBUG_DEVELOPER);
        }
    }
}
