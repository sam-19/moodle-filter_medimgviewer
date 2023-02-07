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

/** MEDICAL IMAGING STUDY VIEWER FILTER
 * @package    filter_medimgviewer
 * @copyright  2021-2023 Sampsa Lohi & University of Eastern Finland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/moodlelib.php");

$id = optional_param('id', 0, PARAM_INT); // Course module ID.
$fa = optional_param('filearea', '', PARAM_PATH); // Path to the requested file tree.
// Trim the filepath arguments, as the manually inserted part may have a traling space.
$fp = trim(optional_param('filepath', '', PARAM_PATH)); // Path of the root file.
// Check if file path has a trailing slash and remove it.
if (substr($fp, -1) == '/') {
    $fp = mb_substr($fp, 0, -1);
}

if ($id && $fa) {
    list($course, $cm) = get_course_and_cm_from_cmid($id);
    require_login($course, true, $cm);
    // Check access rights to content.
    $context = context_module::instance($cm->id);
    if (!has_capability('mod/folder:view', $context)) {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        die(json_encode(array('message' => 'User is not authorized to view content.', 'code' => 999)));
    }
    $areaparts = explode('/', trim(urldecode($fa)));
    $fileparts = explode('/', trim(urldecode($fp)));
    $filetree = get_file_storage()->get_area_tree($areaparts[0], $areaparts[1], $areaparts[2], false);
    $dir = $filetree;
    $path = '';
    $dir['path'] = $path;
    foreach ($fileparts as $idx => $part) {
        if (!empty($dir['subdirs']) && array_key_exists($part, $dir['subdirs'])) {
            $dir = $dir['subdirs'][$part];
        } else if (!empty($dir['files']) && array_key_exists($part, $dir['files'])) {
            // The file entries are empty, so add at least the file name.
            $dir['filename'] = $part;
        }
        $path = $path."/".$part;
        $dir['path'] = $path;
    }
    $result = [
        'ft' => $filetree,
        'dir' => $dir,
        'ap' => $areaparts,
        'fp' => $fileparts
    ];
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json');
    echo json_encode($result);
}
