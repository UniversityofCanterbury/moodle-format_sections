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
 * This file contains general functions for the course format Week
 *
 * @since 2.0
 * @package single sections
 * @copyright 2010 Lei Zhang
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_sections_uses_sections() {
    return true;
}

/**
 * Used to display the course structure for a course where format=sections
 *
 * This is called automatically by {@link load_course()} if the current course
 * format = sections.
 *
 * @param navigation_node $navigation The course node
 * @param array $path An array of keys to the course node
 * @param stdClass $course The course we are loading the section for
 */
function callback_sections_load_content(&$navigation, $course, $coursenode) {
    return $navigation->load_generic_course_sections($course, $coursenode, 'sections');
}


function callback_sections_get_section_name($course, $section) {
    // We can't add a node without any text
    if (!empty($section->name)) {
        return $section->name;
    } else if ($section->section == 0) {
        return get_string('section0name', 'format_sections');
    } else {
        return get_string('section').' '.$section->section;
    }
}
/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_sections_definition() {
    return get_string('section');
}

/**
 * The GET argument variable that is used to identify the section being
 * viewed by the user (if there is one)
 *
 * @return string
 */
function callback_sections_request_key() {
    return 'section';
}

/**
 * Declares support for course AJAX features
 *
 * @see course_format_ajax_support()
 * @return stdClass
 */
function callback_sections_ajax_support() {
    $ajaxsupport = new stdClass();
    $ajaxsupport->capable = true;
    $ajaxsupport->testedbrowsers = array('MSIE' => 6.0, 'Gecko' => 20061111, 'Safari' => 531, 'Chrome' => 6.0);
    return $ajaxsupport;
}
