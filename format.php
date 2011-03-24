<?php

//
// Display the whole course as "sections" made of of modules
// This is based on "topics" format, defaults to showing 1 section at a time.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/ajax/ajaxlib.php');
require_once($CFG->libdir . '/completionlib.php');

$section = 1;
while ($section <= $course->numsections) {

    if (empty($sections[$section])) {
        $sections[$section] = new object();
        $sections[$section]->course = $course->id;   // Create a new section structure
        $sections[$section]->section = $section;
        $sections[$section]->summary = '';
        $sections[$section]->visible = 1;
        if (!$sections[$section]->id = $DB->insert_record('course_sections', $sections[$section])) {
            notify('Error inserting new topic!');
        }
    }
    $section++;
}

$home = optional_param('home', 0, PARAM_INT);
if (!empty($home)) {
    $topic = -1;
    $_SESSION['current_topic'] = null;
} else {
    $topic = optional_param('topic', -1, PARAM_INT);
    if ($topic == -1 && !empty($_SESSION['current_topic'])) {
        $sesstop = explode(':', $_SESSION['current_topic']);
        if ($sesstop[0] == $course->id) {
            $topic = $sesstop[1];
        }
    } else {
        $_SESSION['current_topic'] = $course->id . ':' . $topic;
    }
}



if ($topic != -1) {
    $displaysection = course_set_display($course->id, $topic);
} else {
    $displaysection = course_set_display($course->id, 0);
}

$context = get_context_instance(CONTEXT_COURSE, $course->id);

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    if (!set_field("course", "marker", $marker, "id", $course->id)) {
        error("Could not mark that topic for this course");
    }
}

$streditsummary = get_string('editsummary');
$stradd = get_string('add');
$stractivities = get_string('activities');
$strshowalltopics = get_string('showalltopics');
$strtopic = get_string('topic');
$strgroups = get_string('groups');
$strgroupmy = get_string('groupmy');
$editing = $PAGE->user_is_editing();

if ($editing) {
    $strstudents = moodle_strtolower(role_get_name($DB->get_record('role', array('shortname' => 'student')), $context));
    //$strstudents = moodle_strtolower($course->students);
    $strtopichide = get_string('topichide', '', $strstudents);
    $strtopicshow = get_string('topicshow', '', $strstudents);
    $strmarkthistopic = get_string('markthistopic');
    $strmarkedthistopic = get_string('markedthistopic');
    $strmoveup = get_string('moveup');
    $strmovedown = get_string('movedown');
}


// Print the Your progress icon if the track completion is enabled
$completioninfo = new completion_info($course);
echo $completioninfo->display_help_icon();

/// Layout the whole page as three big columns.
//echo '<table id="layout-table" cellspacing="0" summary="'.get_string('layouttable').'"><tr>';
/// The left column ...
$lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
foreach ($lt as $column) {
    switch ($column) {
        case 'left':



            break;
        case 'middle':
/// Start main column
            //echo '<td id="middle-column">';
            print_container_start();
            echo skip_main_destination();
            echo'<h2 class="headingblock header">' . $course->fullname . '</h2>';
            //print_heading_block(get_string('topicoutline'), 'outline');

            echo '<table class="topics" width="100%" summary="' . get_string('layouttable') . '">';

/// If currently moving a file then show the current clipboard
            if (ismoving($course->id)) {
                $stractivityclipboard = strip_tags(get_string('activityclipboard', '', addslashes($USER->activitycopyname)));
                $strcancel = get_string('cancel');
                echo '<tr class="clipboard">';
                echo '<td colspan="3">';
                echo $stractivityclipboard . '&nbsp;&nbsp;(<a href="mod.php?cancelcopy=true&amp;sesskey=' . $USER->sesskey . '">' . $strcancel . '</a>)';
                echo '</td>';
                echo '</tr>';
            }

/// Print Section 0
            if ($topic < 1) {
                $section = 0;
                $thissection = $sections[$section];

                if ($thissection->summary or $thissection->sequence or $PAGE->user_is_editing()) {
                    echo '<tr id="section-0" class="section main">';
                    echo '<td class="left side">&nbsp;</td>';
                    echo '<td class="content">';
                    if (!is_null($thissection->name)) {
                        echo $OUTPUT->heading($thissection->name, 3, 'sectionname');
                    }
                    echo '<div class="summary">';
                    $summaryformatoptions->noclean = true;
                    echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

                    if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                        echo '<a title="' . $streditsummary . '" ' .
                        ' href="editsection.php?id=' . $thissection->id . '"><img src="' . $OUTPUT->pix_url('t/edit') . '" ' .
                        ' alt="' . $streditsummary . '" /></a><br /><br />';
                    }
                    echo '</div>';
                    //we want to get rid of the news forum link as news is displayed by default
                    $newsforum = forum_get_course_forum($COURSE->id, 'news');
                    foreach ($mods as $key => $value) {
                        if ($value->instance == $newsforum->id) {
                            $mods[$key] = '';
                        }
                    }

                    print_section($course, $thissection, $mods, $modnamesused);

                    if ($PAGE->user_is_editing()) {
                        print_section_add_menus($course, $section, $modnames);
                    }

                    echo '</td>';
                    echo '<td class="right side">&nbsp;</td>';
                    echo '</tr>';
                    echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
                }
            }

/// Now all the normal modules by topic (section)
/// Everything below uses "section" terminology - each "section" is a topic.

            if ($topic != -1) {
                $timenow = time();
                $section = 1;
                $sectionmenu = array();
                while ($section <= $course->numsections) {
                    $thissection = $sections[$section];

                    $showsection = (has_capability('moodle/course:viewhiddensections', $context) or $thissection->visible or !$course->hiddensections);

                    if (!empty($displaysection) and $displaysection != $section) {
                        if ($showsection) {
                            $strsummary = strip_tags(format_string($thissection->summary, true));
                            if (strlen($strsummary) < 57) {
                                $strsummary = ' - ' . $strsummary;
                            } else {
                                $strsummary = ' - ' . substr($strsummary, 0, 60) . '&hellip;';
                            }
                            $sectionmenu['topic=' . $section] = s($section . $strsummary);
                        }
                        $section++;
                        continue;
                    }

                    if ($showsection) {

                        $currenttopic = ($course->marker == $section);

                        $currenttext = '';
                        if (!$thissection->visible) {
                            $sectionstyle = ' hidden';
                        } else if ($currenttopic) {
                            $sectionstyle = ' current';
                            $currenttext = get_accesshide(get_string('currenttopic', 'access'));
                        } else {
                            $sectionstyle = '';
                        }

                        echo '<tr id="section-' . $section . '" class="section main' . $sectionstyle . '">';
                        echo '<td class="left side" style="width:5px"></td>';

                        echo '<td class="content">';
                        if (!has_capability('moodle/course:viewhiddensections', $context) and !$thissection->visible) {   // Hidden for students
                            echo get_string('notavailable');
                        } else {
                            if (!is_null($thissection->name)) {
                                echo $OUTPUT->heading($thissection->name, 3, 'sectionname');
                            }
                            echo '<div class="summary">';
                            $summaryformatoptions->noclean = true;
                            if (empty($thissection->summary)) {
                                //var_dump($thissection);
                                $thissection->summary = get_string('name' . $course->format, 'format_sections') . ' ' . $thissection->section;
                            }
                            echo format_text($thissection->summary, FORMAT_HTML, $summaryformatoptions);

                            if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                                echo ' <a title="' . $streditsummary . '" href="editsection.php?id=' . $thissection->id . '">' .
                                '<img src="' . $OUTPUT->pix_url('/t/edit') . '" alt="' . $streditsummary . '" /></a><br /><br />';
                            }
                            echo '</div>';

                            print_section($course, $thissection, $mods, $modnamesused);

                            if ($PAGE->user_is_editing()) {
                                print_section_add_menus($course, $section, $modnames);
                            }
                        }
                        echo '</td>';

                        echo '<td class="right side">';
                        if ($displaysection == $section) {      // Show the zoom boxes
                            echo '<a href="view.php?id=' . $course->id . '&amp;topic=0#section-' . $section . '" title="' . $strshowalltopics . '">' .
                            '<img src="' . $OUTPUT->pix_url('/i/all') . '" alt="' . $strshowalltopics . '" /></a><br />';
                        } else {
                            $strshowonlytopic = get_string('showonlytopic', '', $section);
                            echo '<a href="view.php?id=' . $course->id . '&amp;topic=' . $section . '" title="' . $strshowonlytopic . '">' .
                            '<img src="' . $OUTPUT->pix_url('/i/one') . '" alt="' . $strshowonlytopic . '" /></a><br />';
                        }

                        if ($PAGE->user_is_editing() && has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $course->id))) {
                            if ($course->marker == $section) {  // Show the "light globe" on/off
                                echo '<a href="view.php?id=' . $course->id . '&amp;marker=0&amp;sesskey=' . $USER->sesskey . '#section-' . $section . '" title="' . $strmarkedthistopic . '">' .
                                '<img src="' . $OUTPUT->pix_url('/i/marked') . '" alt="' . $strmarkedthistopic . '" /></a><br />';
                            } else {
                                echo '<a href="view.php?id=' . $course->id . '&amp;marker=' . $section . '&amp;sesskey=' . $USER->sesskey . '#section-' . $section . '" title="' . $strmarkthistopic . '">' .
                                '<img src="' . $OUTPUT->pix_url('/i/marker') . '" alt="' . $strmarkthistopic . '" /></a><br />';
                            }

                            if ($thissection->visible) {        // Show the hide/show eye
                                echo '<a href="view.php?id=' . $course->id . '&amp;hide=' . $section . '&amp;sesskey=' . $USER->sesskey . '#section-' . $section . '" title="' . $strtopichide . '">' .
                                '<img src="' . $OUTPUT->pix_url('/i/hide') . '" alt="' . $strtopichide . '" /></a><br />';
                            } else {
                                echo '<a href="view.php?id=' . $course->id . '&amp;show=' . $section . '&amp;sesskey=' . $USER->sesskey . '#section-' . $section . '" title="' . $strtopicshow . '">' .
                                '<img src="' . $OUTPUT->pix_url('/i/show') . '" alt="' . $strtopicshow . '" /></a><br />';
                            }

                            if ($section > 1) {                       // Add a arrow to move section up
                                echo '<a href="view.php?id=' . $course->id . '&amp;random=' . rand(1, 10000) . '&amp;section=' . $section . '&amp;move=-1&amp;sesskey=' . $USER->sesskey . '#section-' . ($section - 1) . '" title="' . $strmoveup . '">' .
                                '<img src="' . $OUTPUT->pix_url('/t/up') . '" alt="' . $strmoveup . '" /></a><br />';
                            }

                            if ($section < $course->numsections) {    // Add a arrow to move section down
                                echo '<a href="view.php?id=' . $course->id . '&amp;random=' . rand(1, 10000) . '&amp;section=' . $section . '&amp;move=1&amp;sesskey=' . $USER->sesskey . '#section-' . ($section + 1) . '" title="' . $strmovedown . '">' .
                                '<img src="' . $OUTPUT->pix_url('/t/down') . '" alt="' . $strmovedown . '" /></a><br />';
                            }
                        }

                        echo '</td></tr>';
                        echo '<tr class="section separator"><td colspan="3" class="spacer"></td></tr>';
                    }

                    $section++;
                }
            }
            echo '</table>';
            if ($topic == -1) {
                require_once($CFG->dirroot . '/mod/forum/lib.php');

                if (!$newsforum = forum_get_course_forum($COURSE->id, 'news')) {
                    error('Could not find or create a main news forum for the site');
                }

                if (!empty($USER->id)) {
                    $SESSION->fromdiscussion = $CFG->wwwroot;
                    if (forum_is_subscribed($USER->id, $newsforum->id)) {
                        $subtext = get_string('unsubscribe', 'forum');
                    } else {
                        $subtext = get_string('subscribe', 'forum');
                    }
                    echo '<h2>News</h2>';
                    //print_heading_block(get_string('news'));
                    //echo '<div class="subscribelink"><a href="mod/forum/subscribe.php?id='.$newsforum->id.'">'.$subtext.'</a></div>';
                } else {
                    //print_heading_block($newsforum->name);
                }
                $SESSION->fromdiscussion = "{$CFG->wwwroot}/course/view.php?id={$course->id}";

                $news = forum_print_latest_discussions($course, $newsforum, $SITE->newsitems, 'plain', 'p.modified DESC');
                $news = str_replace("Add a new topic", '', $news);
            }

            print_container_end();
            //echo '</td>';

            break;
        case 'right':
            break;
    }
}
//echo '</tr></table>';