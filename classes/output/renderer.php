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

namespace format_sections\output;

use core_courseformat\output\section_renderer;
use moodle_page;

/**
 * Basic renderer for sections format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer {

    /**
     * Constructor method, calls the parent constructor.
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_sections_renderer::section_edit_control_items() only displays the 'Highlight' control
        // when editing mode is on we need to be sure that the link 'Turn editing mode on' is available for a user
        // who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param int|stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Function to display News forum on page.
     *
     * @param \stdClass $course
     * @return void
     */
    public function display_forum($course) {
        global $USER;
        $pageno = optional_param('p', 0, PARAM_INT);

        $forum = forum_get_course_forum($course->id, 'news');
        if (empty($forum)) {
            return;
        }

        $coursemodule = get_coursemodule_from_instance('forum', $forum->id);
        $modcontext = \context_module::instance($coursemodule->id);

        $entityfactory = \mod_forum\local\container::get_entity_factory();
        $forumentity = $entityfactory->get_forum_from_stdclass($forum, $modcontext, $coursemodule, $course);

        $html = "<h4>".format_string($forum->name)."</h4>";

        $numdiscussions = course_get_format($course)->get_course()->newsitems;
        if ($numdiscussions < 1) {
            // Make sure that the value is at least one.
            $numdiscussions = 1;
        }

        $rendererfactory = \mod_forum\local\container::get_renderer_factory();
        $discussionsrenderer = $rendererfactory->get_social_discussion_list_renderer($forumentity);
        $cm = \cm_info::create($coursemodule);
        $html .= $discussionsrenderer->render($USER, $cm, null, null, $pageno, $numdiscussions, null, true);

        return $html;
    }

    /**
     * Function to display action link controlling course display on page.
     *
     * @param stdClass $course
     * @return void
     */
    public function course_display_action_link($issinglesection, $course) {

        if ($issinglesection) {
            $linkname = get_string('coursedisplay_single','format_sections');
            $class = 'coursedisplay_single';
        } else {
            $linkname = get_string('coursedisplay_multi','format_sections');
            $class = 'coursedisplay_multi multi';
        }

        $html = "<a href='./format/sections/displaypref.php?id=".$course->id."&displaypref=".$issinglesection.
            "' class='aabtn ".$class."'>".$linkname."</a>";

        return $html;
    }
}
