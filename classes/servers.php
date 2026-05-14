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

/**
 * Single source of truth for all CertifyMe servers.
 *
 * TO ADD A NEW SERVER — only edit this file:
 *   'key' => ['label' => '...', 'url' => 'https://...certifyme.../api/v2/credential', 'signup_url' => 'https://.../auth/register/']
 *
 * The settings dropdown, API routing, and signup button all read from here automatically.
 */
class servers {

    public static function all(): array {
        return [
            'apac' => [
                'label'      => 'APAC  (https://apac.platform.certifyme.dev)',
                'url'        => 'https://apac.platform.certifyme.dev/api/v2/credential',
                'signup_url' => 'https://apac.platform.certifyme.dev/auth/pre-register/moodle@certifyme.online',
            ],
            'eu2' => [
                'label'      => 'EU2   (https://eu2.certifyme.org)',
                'url'        => 'https://eu2.certifyme.org/api/v2/credential',
                'signup_url' => 'https://eu2.certifyme.org/auth/pre-register/moodle@certifyme.online',
            ],
            'us1' => [
                'label'      => 'US1   (https://us1.certifyme.org)',
                'url'        => 'https://us1.certifyme.org/api/v2/credential',
                'signup_url' => 'https://us1.certifyme.org/auth/pre-register/moodle@certifyme.online',
            ],
            'butterfly' => [
                'label'      => 'Butterfly  (https://butterfly.certifyme.org)',
                'url'        => 'https://butterfly.certifyme.org/api/v2/credential',
                'signup_url' => 'https://butterfly.certifyme.org/auth/pre-register/moodle@certifyme.online',
            ],
            // ADD NEW SERVER HERE — nothing else needs to change:
            // 'asia2' => [
            //     'label'      => 'Asia2  (https://asia2.certifyme.org)',
            //     'url'        => 'https://asia2.certifyme.org/api/v2/credential',
            //     'signup_url' => 'https://asia2.certifyme.org/auth/register/',
            // ],
        ];
    }

    public static function dropdown(): array {
        return array_map(fn($s) => $s['label'], self::all());
    }

    public static function endpoint(string $key): string {
        $servers = self::all();
        return $servers[$key]['url'] ?? $servers['apac']['url'];
    }

    public static function signup_url(string $key): string {
        $servers = self::all();
        return $servers[$key]['signup_url'] ?? $servers['apac']['signup_url'];
    }

    /** Returns a JSON object mapping server key → signup_url, for use in JavaScript. */
    public static function signup_urls_json(): string {
        return json_encode(array_map(fn($s) => $s['signup_url'], self::all()));
    }

    public static function default_key(): string {
        return 'apac';
    }
}
