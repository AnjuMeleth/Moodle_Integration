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

$observers = [
    [
        'eventname'  => '\core\event\course_completed',
        'callback'   => '\local_certifyme\observer::course_completed',
        'priority'   => 0,
        'internal'   => false,
    ],
];
