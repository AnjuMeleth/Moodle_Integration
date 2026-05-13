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

namespace local_certifyme\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;

class provider implements \core_privacy\local\metadata\provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'certifyme_api',
            [
                'name'   => 'privacy:metadata:certifyme_api:name',
                'email'  => 'privacy:metadata:certifyme_api:email',
                'course' => 'privacy:metadata:certifyme_api:course',
            ],
            'privacy:metadata:certifyme_api'
        );
        return $collection;
    }
}
