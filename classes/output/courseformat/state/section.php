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

namespace format_sections\output\courseformat\state;

use core_courseformat\output\local\state\section as section_base;
use stdClass;

/**
 * Contains the ajax update section structure.
 *
 * @package   format_sections
 * @copyright 2021 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {

    /**
     * Export this data so it can be used as state object in the course editor.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {

        $data = parent::export_for_template($output);

        $format = $this->format;
        $course = $format->get_course();
        $options = $format->get_format_options();

        if (empty($options['coursedisplay'])) {
            // Absolute URL: a root-relative path breaks when Moodle is installed in a subdirectory.
            $data->sectionurl = (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false) . '#section-' . $data->section;
        }

        return $data;
    }
}
