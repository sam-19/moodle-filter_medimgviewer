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

/** MEDIGI VIEWER FILTER
 * @package    medigi-viewer
 * @copyright  2021 Sampsa Lohi
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/moodlelib.php");
//require_once("$CFG->libdir/weblib.php");

class filter_medigiviewer extends moodle_text_filter {
    public function filter($text, array $options = array()) {
        global $CFG;
        global $PAGE;
        $filtertag = get_config('filter_medigiviewer', 'filtertag');
        $extensions = explode(',', get_config('filter_medigiviewer', 'extensions'));
        if (!is_string($text) or empty($text)) {
            // Non-string data can not be filtered anyway.
            return $text;
        }
        if (stripos($text, '</a>') === false && stripos($text, '<!--'.$filtertag) === false) {
            // Performance shortcut - if there is no </a> tag or filter tag, nothing can match.
            return $text;
        }
        // Match all MEDigi viewer media tags
        $pattern = "/<!--".$filtertag."(.+?)-->/i";
        if (preg_match_all($pattern, $text, $matches)) {
            foreach ($matches[1] as $idx => $match) {
                // Check if this is a pluginfile link
                $plgfilestr = "/pluginfile.php/";
                $plgfilepos = strpos($match, $plgfilestr);
                $result = [
                    'areaPath' => false,
                    'filePath' => false,
                    'fileTree' => []
                ];
                if ($plgfilepos !== false) {
                    // Fetching params with lib/weblib.php:get_file_argument() doesn't seem to work even
                    // if I manually set $_SERVER['REQUEST_URI'] to match the file link
                    $fileargs = explode('/', substr($match, $plgfilepos + strlen($plgfilestr)));
                    if (count($fileargs) > 3) {
                        // Separate area and file path and decode URL codes from path
                        $areapath = array_slice($fileargs, 0, 3);
                        $filepath = array_slice($fileargs, 3);
                        foreach ($areapath as &$value) { $value = urldecode($value); }
                        foreach ($filepath as &$value) { $value = urldecode($value); }
                        $result['areaPath'] = $areapath;
                        $result['filePath'] = $filepath;
                        // Get file area tree with lib/filestorage/file_storage.php:get_area_tree()
                        $result['fileTree'] = get_file_storage()->get_area_tree($fileargs[0], $fileargs[1], $fileargs[2], false);
                    }
                }
                // Replace the placeholder with a hidden div (where the inline app will be loaded as well)
                $return_el = "<div style='display:none' id='medigi-viewer-inline-$idx' data-resource='".json_encode($result)."'></div>";
                $text = str_replace($matches[0][$idx], $return_el, $text);
                // Load Vue frontend
                $PAGE->requires->js_call_amd('filter_medigiviewer/viewer', 'createMEDigiViewerInstance', [
                    'config' => json_encode([
                        'appName' => "inline-$idx", 
                        'autoStart' => true,
                        'environment' => 'moodle',
                        'idSuffix' => "inline-$idx",
                        'resource' => $result['fileTree'],
                        'wpPublicPath' => "$CFG->wwwroot/filter/medigiviewer/amd/"
                    ]),
                    'jsonConfig' => true,
                ]);
            }
        }
        return $text;
    }
}
