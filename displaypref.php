<?php
// This file is part of Moodle - http://moodle.org/
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

/**
 * Sets user's format_sections_coursedisplaypref.
 *
 * @package format_sections
 * @copyright 2022 Catalist IT
 * @author noemie.ariste@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once('lib.php');
global $CFG, $USER;


$courseid = optional_param('id', 0, PARAM_INT);
$displaypref = optional_param('displaypref', 0, PARAM_BOOL);


// TODO? Check if user is enrolled in course.

if ($displaypref == COURSE_DISPLAY_MULTIPAGE) {
    $newdisplaypref = COURSE_DISPLAY_SINGLEPAGE;
} else {
    $newdisplaypref = COURSE_DISPLAY_MULTIPAGE;
}

set_user_preference('format_sections_coursedisplaypref', $newdisplaypref);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
redirect($courseurl);
