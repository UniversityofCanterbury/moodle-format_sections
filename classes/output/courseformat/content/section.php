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
 * Contains the default section controls output class.
 *
 * @package   format_sections
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_sections\output\courseformat\content;

use core_courseformat\base as course_format;
use core_courseformat\output\local\content\section as section_base;
use stdClass;

/**
 * Base class to render a course section.
 *
 * @package   format_sections
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {

    /** @var course_format the course format */
    protected $format;

    /**
     * Template name for this exporter
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_sections/local/content/section';
    }

    public function export_for_template(\renderer_base $output): stdClass {
        global $PAGE;

        $format = $this->format;
        $course = $format->get_course();
        $options = $format->get_format_options();
        $singlesectionid = $format->get_sectionid();

        $data = parent::export_for_template($output);

        if (!$this->format->get_sectionnum() && !$this->section->is_delegated()) {
            $addsectionclass = $format->get_output_classname('content\\addsection');
            $addsection = new $addsectionclass($format);
            $renderer = $PAGE->get_renderer('format_sections');
            if ($data->num === 0) {
                if ($options['shownews']) {
                    $data->newsforum = $renderer->display_forum($course);
                }
                if ($options['usercoursedisplaypref'] && is_null($singlesectionid)) {
                    $coursedisplay = $format->get_course_display();
                    $data->usercoursedisplaypref = $renderer->course_display_action_link($coursedisplay, $course);
                }
            }
            $data->numsections = $addsection->export_for_template($output);
            $data->insertafter = true;
        }

        return $data;
    }
}
