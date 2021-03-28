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

defined('MOODLE_INTERNAL') || die();

class filter_medigiviewer extends moodle_text_filter {
    public function filter($text, array $options = array()) {
        $filtertag = get_config('filter_medigiviewer', 'filtertag');
        $extensions = get_config('filter_medigiviewer', 'extensions').explode(',');
        if (!is_string($text) or empty($text)) {
            // Non-string data can not be filtered anyway.
            return $text;
        }
        if (stripos($text, '</a>') === false && stripos($text, `<!--$filtertag`) === false) {
            // Performance shortcut - if there is no </a> tag or filter tag, nothing can match.
            return $text;
        }
    }
}
