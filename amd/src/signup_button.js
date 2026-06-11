// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// @module     local_certifyme/signup_button
// @copyright  2026 CertifyMe (https://www.certifyme.online)
// @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/**
 * Updates the sign-up button href when the server selector changes.
 *
 * @param {Object} urls  Map of server key → sign-up URL.
 */
define([], function() {
    return {
        init: function(urls) {
            var sel = document.getElementById('id_s_local_certifyme_server');
            var btn = document.getElementById('certifyme-signup-btn');
            if (sel && btn) {
                sel.addEventListener('change', function() {
                    if (urls[sel.value]) {
                        btn.href = urls[sel.value];
                    }
                });
            }
        }
    };
});
