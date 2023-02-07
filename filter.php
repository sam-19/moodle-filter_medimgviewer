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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/moodlelib.php");

/**
 * Medical imaging study viwer filter.
 */
class filter_medimgviewer extends moodle_text_filter {
    /**
     * Apply the filter to convert tagged links into viewer instances.
     * @param string $text
     * @param array $options
     * @return string filtered text.
     */
    public function filter($text, array $options = array()) {
        global $CFG;
        global $PAGE;
        $filtertag = get_config('filter_medimgviewer', 'filtertag');
        if (!is_string($text) || empty($text)) {
            // Non-string data can not be filtered anyway.
            return $text;
        }
        if (stripos($text, '</a>') === false && stripos($text, $filtertag) === false) {
            // Performance shortcut - if there is no </a> tag or filter tag, nothing can match.
            return $text;
        }
        // Match all MedImg viewer media tags.
        $pattern = "/\<a[^\>]+?href=\"(.+?):".$filtertag."\"\>(.+?)\<\/a\>/i";
        if (preg_match_all($pattern, $text, $matches)) {
            // Add resources in one array.
            $resources = [];
            $id = optional_param('id', 0, PARAM_INT); // Course ID.
            $plgfilestr = "/pluginfile.php/";
            foreach ($matches[1] as $idx => $match) {
                // Check if this is a pluginfile link.
                $plgfilepos = strpos($match, $plgfilestr);
                $areapath = '';
                $filepath = '';
                $filetree = [];
                if ($plgfilepos !== false) {
                    // Fetching params with lib/weblib.php:get_file_argument() doesn't seem to work.
                    // Even if I manually set $_SERVER['REQUEST_URI'] to match the file link.
                    $fileargs = explode('/', substr($match, $plgfilepos + strlen($plgfilestr)));
                    if (count($fileargs) > 3) {
                        // Separate area and file parts from the path and decode URL codes from path.
                        $areapart = array_slice($fileargs, 0, 3);
                        $filepart = array_slice($fileargs, 3);
                        foreach ($areapart as &$value) {
                            $value = urldecode($value);
                        }
                        foreach ($filepart as &$value) {
                            $value = urldecode($value);
                        }
                        $areapath = implode('/', $areapart);
                        $filepath = implode('/', $filepart);
                    } else {
                        $areapart = $fileargs;
                        foreach ($areapart as &$value) {
                            $value = urldecode($value);
                        }
                        $areapath = implode('/', $areapart);
                    }
                } else {
                    continue;
                }
                // Replace the placeholder with a hidden div (where the inline app will be loaded as well).
                $returnel = "<div class='medimg-viewer-inline'>
                    <div 'style=display:none' id='medimg-viewer-inline-$idx'></div>
                </div>";
                // Only replace the first match (in case the same resource is linked multiple times).
                $pos = strpos($text, $matches[0][$idx]);
                $text = substr_replace($text, $returnel, $pos, strlen($matches[0][$idx]));
                // Add to list of resources.
                array_push($resources, [
                    'appName' => "inline-$idx",
                    'idSuffix' => "inline-$idx",
                    'areaPath' => $areapath,
                    'filePath' => $filepath,
                ]);
            }
            if (!empty($resources)) {
                // Load the viewer module.
                $PAGE->requires->js_call_amd('filter_medimgviewer/loader', 'init', [
                    'cmId' => $PAGE->cm->id,
                    'resources' => $resources,
                ]);
            }
        }
        return $text;
    }
}
