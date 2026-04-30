<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Custom TCPDF class for quiz PDF output.
 *
 * @package   quiz_downloadquiz
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\local;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/tcpdf/tcpdf.php');

/**
 * Custom TCPDF class for quiz PDF output.
 */
class quiz_downloadquiz_pdf extends \TCPDF {
    /**
     * Warning data used in the page header.
     *
     * @var array
     */
    protected array $warningdata = [];

    /**
     * Set warning data.
     *
     * @param array $data
     * @return void
     */
    public function set_warning_data(array $data): void {
        $this->warningdata = $data;
    }

    /**
     * Return the appropriate PDF font family based on the current language direction.
     *
     * @return string The TCPDF font family name.
     */
    protected function get_pdf_font_family(): string {
        return right_to_left() ? 'amirib' : 'freesans';
    }

    /**
     * Return the appropriate text alignment based on the current language direction.
     *
     * @return string The TCPDF alignment value.
     */
    protected function get_pdf_alignment(): string {
        return right_to_left() ? 'R' : 'L';
    }

    /**
     * PDF header.
     *
     * @return void
     */
    public function header(): void {
        if (empty($this->warningdata) || $this->getPage() === 1) {
            return;
        }

        $fullname = $this->warningdata['userfullname'] ?? '';
        $email = $this->warningdata['useremail'] ?? '';
        $generatedat = $this->warningdata['generatedat'] ?? '';
        $servertimezone = $this->warningdata['servertimezone'] ?? '';

        $userdetails = trim($fullname . ($email !== '' ? ' (' . $email . ')' : ''));
        if ($generatedat !== '') {
            $userdetails .= ' ' . get_string('generatedon', 'quiz_downloadquiz') . ' ' . $generatedat;
        }
        if ($servertimezone !== '') {
            $userdetails .= ' (' . $servertimezone . ')';
        }

        $text = get_string('confidentialnoticeprefix', 'quiz_downloadquiz') . ': ' .
            get_string('confidentialnoticefull', 'quiz_downloadquiz', (object) [
                'user' => $userdetails,
            ]);

        $this->StartTransform();
        $this->SetAlpha(0.12);
        $this->SetTextColor(245, 245, 245);
        $this->SetFont($this->get_pdf_font_family(), '', 7);

        $x = 10;
        $y = 250;

        $this->Rotate(90, $x, $y);
        $this->MultiCell(
            180,
            0,
            $text,
            0,
            $this->get_pdf_alignment(),
            false,
            1,
            $x,
            $y,
            true,
            0,
            false,
            true,
            0,
            'T'
        );

        $this->StopTransform();
        $this->SetAlpha(1);
        $this->SetTextColor(0, 0, 0);
    }
}
