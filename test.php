<?php
// This file is part of the Accredible Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

set_time_limit(0);

/**
 * Handles viewing a certificate
 *
 * @package    mod_certifyme
 * @author     Faisal Kaleeem <faisal@wizcoders.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once("locallib.php");

$id = required_param('id', PARAM_INT); // Course Module ID.

$cm = get_coursemodule_from_id('certifyme', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$modcertify = $DB->get_record('certifyme', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course->id, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/certifyme:manage', $context);

// Initialize $PAGE, compute blocks.
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/certifyme/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($modcertify->name));
$PAGE->set_heading(format_string($course->fullname));

// Echo the page.
echo $OUTPUT->header();

// $response = trigger_certifyme($modcertify, $USER, 70, $course->id);
// $context = context_module::instance(90);
// print_object($context);
$event = \mod_quiz\event\attempt_submitted::create([
    'objectid' => 55, // attempt id
// 'quiz' => 15,
    'courseid' => 11,
    'userid' => 3,
    'relateduserid' => 3,
    'context' => context_module::instance(90),
    'other' => ['submitterid' => 3, 'quizid' => 15]
]);
$event->trigger();

// $certificateevent = \mod_certifyme\event\certificate_created::create(array(
// 'objectid' => $modcertify->id,
// 'context' => context_module::instance($context->instanceid),
// 'relateduserid' => 2
// ));
// $certificateevent->trigger();

// var_dump($response);

echo $OUTPUT->footer($course);
