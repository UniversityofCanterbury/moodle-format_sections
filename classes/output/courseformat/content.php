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
 * Contains the default content output class.
 *
 * @package   format_sections
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_sections\output\courseformat;

use core_courseformat\output\local\content as content_base;
use course_modinfo;

/**
 * Base class to render a course content.
 *
 * @package   format_sections
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {

    /**
     * @var bool Sections format has Add section at the bottom after existing sections.
     *
     * The responsible for the button is core_courseformat\output\local\content\section.
     */
    protected $hasaddsection = true;

    /**
     * Template name for this exporter
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_sections/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;
        $format = $this->format;
        $options = $format->get_format_options();

        $data = parent::export_for_template($output);
        $PAGE->requires->js_call_amd('format_sections/mutations', 'init');
        $PAGE->requires->js_call_amd('format_sections/section', 'init');

        // Course layout 'Show one section per page' is selected.
        if(!empty($options['coursedisplay'])) {
            $data->sections = array_shift($data->sections);
            $data->hasaddsection = false;
            $data->numsections = [];
        }

        return $data;
    }

    /**
     * Export sections array data.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return array data context for a mustache template
     */
    protected function export_sections(\renderer_base $output): array {

        $format = $this->format;
        $course = $format->get_course();
        $modinfo = $this->format->get_modinfo();

        // Generate section list.
        $sections = [];
        $stealthsections = [];
        foreach ($this->get_sections_to_display($modinfo) as $sectionnum => $thissection) {
            // The course/view.php check the section existence but the output can be called
            // from other parts so we need to check it.
            if (!$thissection) {
                throw new \moodle_exception('unknowncoursesection', 'error', course_get_url($course),
                    format_string($course->fullname));
            }

            if (!$format->is_section_visible($thissection)) {
                continue;
            }

            /** @var \core_courseformat\output\local\content\section $section */
            $section = new $this->sectionclass($format, $thissection);

            if ($section->is_stealth()) {
                // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                if (!empty($modinfo->sections[$sectionnum])) {
                    $stealthsections[] = $section->export_for_template($output);
                }
                continue;
            }

            $sections[] = $section->export_for_template($output);
        }
        if (!empty($stealthsections)) {
            $sections = array_merge($sections, $stealthsections);
        }
        return $sections;
    }

    /**
     * Return an array of sections to display.
     *
     * This method is used to differentiate between display a specific section
     * or a list of them.
     *
     * @param course_modinfo $modinfo the current course modinfo object
     * @return section_info[] an array of section_info to display
     */
    private function get_sections_to_display(course_modinfo $modinfo): array {
        $singlesectionid = $this->format->get_sectionid();
        $section0 = $this->format->get_section(0);
        $options = $this->format->get_format_options();
        if ($singlesectionid) {
            if (!$options['section0display'] && $singlesectionid !== (int)$section0->id) {
                // Display section 0 in a single section page.
                return [
                    $modinfo->get_section_info_by_id($singlesectionid),
                    $modinfo->get_section_info_by_id($section0->id),
                ];
            }
            // Display section 0 once in its own single section page.
            return [
                $modinfo->get_section_info_by_id($singlesectionid),
            ];
        }
        // Display all sections.
        return $modinfo->get_listed_section_info_all();
    }
}
