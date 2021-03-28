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

class filter_medigiviewer extends moodle_text_filter {
    public function filter($text, array $options = array()) {
        global $CFG;
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
            $filesys = array();
            $check = array();
            foreach ($matches[0] as $idx => $match) {
                // Fetch contents of the data resource directory
                $dirlist = get_directory_list($CFG->dataroot.'/repository/imaging/radiology/dicom/example1/');
                foreach ($dirlist as $key => $value) {
                    // By drapeko https://www.php.net/manual/en/ref.filesystem.php#91075
                    // TODO: FOR TESTING PURPOSES ONLY! THIS ABSOLUTELY HAS TO BE CHANGED FOR PRODUCTION!
                    // Using eval() even in such a restricted context is a no go.
                    $path = '[\''.str_replace('/', '\'][\'', $value).'\']';
                    foreach($check as $ck) {
                        if (strpos($ck, $path) !== false) {
                            continue;
                        }
                    }
                    array_push($check, $path);
                    eval('$filesys'.$path.' = array("type" => "file");');
                    continue;
                    $path = explode($value, '/');
                    foreach ($path as $idy => $dir) {
                        if ($idy < len($path) - 1) {
                            // Still in a parent directory
                            if (!array_key_exists($dir, $filesys)) {

                            }
                        }
                    }
                }
                $text = str_replace($match, "<div id='medigi-viewer-".$key."' data-resource-url='".json_encode($filesys)."'></div>", $text);
            }
        }
        return $text;
    }
}
