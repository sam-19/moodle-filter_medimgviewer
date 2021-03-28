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

class medigiviewer_filter_local_settings_form extends filter_local_settings_form {
    protected function definition_inner($mform) {
        // Fetch global config options
        $globalconf = get_config('filter_medigiviewer');
        // Use automatic filter
        $mform->addElement(
            'advcheckbox',
            'automatic',
            get_string('automatic', 'filter_medigiviewer'),
            '',
            array('group' => 1),
            array(0, 1)
        );
        $mform->setType('automatic', PARAM_INT);
        $mform->setDefault('automatic',
            // Use global configuration default or true if global conf is not set
            ($globalconf && property_exists($globalconf, 'automatic')) ? $globalconf->automatic : 1
        );
    }
}
