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
 * Renderer for outputting the Single Sections course format.
 *
 * @package format_sections
 * @copyright 2012 Paul Nicholls
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

/**
 * Basic renderer for Single Sections format.
 *
 * @copyright 2012 Paul Nicholls
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_sections_renderer extends format_section_renderer_base {

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();

        if ($section->section) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $strmarkedthistopic = get_string('highlightoff');
                $controls['highlight'] = array(
                    'url' => $url,
                    'icon' => 'i/marked',
                    'name' => $strmarkedthistopic,
                    'pixattr' => array('class' => '', 'alt' => $strmarkedthistopic),
                    'attr' => array('class' => 'icon editing_highlight', 'title' => $strmarkedthistopic));
            } else {
                $url->param('marker', $section->section);
                $strmarkthistopic = get_string('highlight');
                $controls['highlight'] = array(
                    'url' => $url,
                    'icon' => 'i/marker',
                    'name' => $strmarkthistopic,
                    'pixattr' => array('class' => '', 'alt' => $strmarkthistopic),
                    'attr' => array('class' => 'icon editing_highlight', 'title' => $strmarkthistopic));
            }
        }

        return array_merge($controls, parent::section_edit_control_items($course, $section, $onsectionpage));
    }

    /**
     * Output the html for a course homepage
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods used for print_section()
     * @param array $modnames used for print_section()
     * @param array $modnamesused used for print_section()
     */
    public function print_course_home_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE, $USER, $SESSION, $CFG;
        require_once($CFG->dirroot . '/mod/forum/lib.php');

        $modinfo = get_fast_modinfo($course);

        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course);

        // Now the list of sections..
        echo $this->start_section_list();

        // General section if non-empty.
        $thissection = $modinfo->get_section_info(0);

        //we want to get rid of the news forum link as news is displayed by default
        $newsforum = forum_get_course_forum($course->id, 'news');

        if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing()) {
            echo $this->section_header($thissection, $course, false);
            echo $this->courserenderer->course_section_cm_list($course, $thissection);
            if ($PAGE->user_is_editing()) {
                echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
            }
            echo $this->section_footer();
        }

        echo $this->end_section_list();

        if (!$newsforum) {
            error('Could not find or create a main news forum for the site');
        }

        echo '<h2>News</h2>';
        $SESSION->fromdiscussion = "{$CFG->wwwroot}/course/view.php?id={$course->id}";

        forum_print_latest_discussions($course, $newsforum, $course->newsitems, 'plain', 'p.modified DESC');
    }
}
