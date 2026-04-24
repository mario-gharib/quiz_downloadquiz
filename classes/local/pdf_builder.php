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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * PDF builder for quiz_downloadquiz.
 *
 * @package     quiz_downloadquiz
 * @copyright   2026 Center for Digital Innovation and Artificial Intelligence
 * @author      Center for Digital Innovation and Artificial Intelligence
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_downloadquiz\local;

/**
 * PDF builder.
 */
final class pdf_builder {
    /**
     * Main page left margin.
     */
    private const PAGE_MARGIN_LEFT = 20;

    /**
     * Main page top margin.
     */
    private const PAGE_MARGIN_TOP = 20;

    /**
     * Main page right margin.
     */
    private const PAGE_MARGIN_RIGHT = 20;

    /**
     * Main page bottom margin.
     */
    private const PAGE_MARGIN_BOTTOM = 20;

    /**
     * Footer margin.
     */
    private const FOOTER_MARGIN = 10;

    /**
     * Main content box width.
     */
    private const BOX_WIDTH = 175.0;

    /**
     * Start Y position for pages.
     */
    private const PAGE_START_Y = 20.0;

    /**
     * Conservative content bottom limit.
     */
    private const PAGE_CONTENT_BOTTOM_LIMIT = 270.0;

    /**
     * Question box inner padding.
     */
    private const BOX_PADDING = 4.0;

    /**
     * Watermark text.
     */
    private const WATERMARK_TEXT = 'CONFIDENTIAL';

    /**
     * Build the PDF content and return it as a binary string.
     *
     * @param array $data
     * @return string
     */
    public function build_pdf_content(array $data): string {
        $pdf = $this->create_pdf_instance($data);

        $this->render_cover_page($pdf, $data);
        $this->render_question_pages($pdf, $data);
        $this->apply_watermark_to_all_pages($pdf);

        while (ob_get_level()) {
            ob_end_clean();
        }

        return $pdf->Output('', 'S');
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
     * Wrap text for proper RTL/LTR rendering.
     *
     * @param string $text
     * @return string
     */
    private function bidi(string $text): string {
        if (!right_to_left()) {
            return $text;
        }

        // Unicode RTL embedding.
        return "\u{202B}" . $text . "\u{202C}";
    }

    /**
     * Create and configure the PDF instance.
     *
     * @param array $data
     * @return \quiz_downloadquiz\local\quiz_downloadquiz_pdf
     */
    private function create_pdf_instance(array $data): \quiz_downloadquiz\local\quiz_downloadquiz_pdf {
        $pdf = new \quiz_downloadquiz\local\quiz_downloadquiz_pdf(
            PDF_PAGE_ORIENTATION,
            PDF_UNIT,
            PDF_PAGE_FORMAT,
            true,
            'UTF-8',
            false
        );

        $pdf->setLanguageArray([
            'a_meta_charset' => 'UTF-8',
            'a_meta_dir' => 'rtl',
            'a_meta_language' => 'ar',
            'w_page' => 'page',
        ]);

        $pdf->set_warning_data($data);

        $this->apply_pdf_protection($pdf, (string) ($data['pdfpassword'] ?? ''));

        $pdf->SetCreator('Moodle');
        $pdf->SetAuthor('Moodle Quiz Report DownloadQuiz');
        $pdf->SetTitle($this->txt($data['quizname'] ?? 'quiz'));
        $pdf->SetSubject(get_string('revisioncopy', 'quiz_downloadquiz'));

        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);
        $pdf->SetFooterMargin(self::FOOTER_MARGIN);
        $pdf->SetMargins(
            self::PAGE_MARGIN_LEFT,
            self::PAGE_MARGIN_TOP,
            self::PAGE_MARGIN_RIGHT
        );
        $pdf->SetAutoPageBreak(true, self::PAGE_MARGIN_BOTTOM);
        $pdf->setFontSubsetting(false);

        $isrtl = right_to_left();
        $pdf->setRTL($isrtl);
        $pdf->SetFont($this->get_pdf_font_family(), '', 10);

        return $pdf;
    }


    /**
     * Render the first page of the PDF.
     *
     * @param \TCPDF $pdf
     * @param array $data
     * @return void
     */
    private function render_cover_page(\TCPDF $pdf, array $data): void {
        $pdf->AddPage();
        $pdf->SetY(self::PAGE_START_Y);

        $this->draw_confidential_warning_box($pdf, $data);

        $this->line(
            $pdf,
            get_string('quiztitle', 'quiz_downloadquiz') . $this->txt($data['quizname'] ?? 'quiz'),
            'B',
            14
        );

        $pdf->Ln(3);

        $this->draw_metadata_box($pdf, $data);
        $this->draw_intentionally_blank_notice($pdf);
    }

    /**
     * Render the question pages.
     *
     * @param \TCPDF $pdf
     * @param array $data
     * @return void
     */
    private function render_question_pages(\TCPDF $pdf, array $data): void {
        $pdf->AddPage();
        $pdf->SetY(self::PAGE_START_Y);

        $currentpage = null;
        $currentsectionid = null;

        foreach (($data['questions'] ?? []) as $question) {
            $page = (int) ($question['page'] ?? 0);
            $sectionid = (int) ($question['sectionid'] ?? 0);
            $questionheight = $this->estimate_question_height($question);

            if ($currentsectionid !== $sectionid) {
                $this->ensure_space_for_section_header($pdf, $questionheight);

                $currentsectionid = $sectionid;
                $currentpage = null;

                if ($pdf->GetY() > 22) {
                    $pdf->Ln(6);
                }

                $this->draw_section_header($pdf, $question);
            }

            if ($currentpage !== $page) {
                $this->ensure_space_for_page_header($pdf, $questionheight);
                $currentpage = $page;
                $this->draw_page_header($pdf, $currentpage);
            }

            $this->draw_question_box($pdf, $question);
        }
    }

    /**
     * Apply password protection to the PDF.
     *
     * @param \TCPDF $pdf
     * @param string $userpassword
     * @return void
     */
    private function apply_pdf_protection(\TCPDF $pdf, string $userpassword): void {
        $userpassword = trim($userpassword);

        if ($userpassword === '') {
            return;
        }

        $ownerpassword = bin2hex(random_bytes(16));

        $pdf->SetProtection(
            ['print', 'copy'],
            $userpassword,
            $ownerpassword
        );
    }

    /**
     * Draw watermark on the current page.
     *
     * @param \TCPDF $pdf
     * @return void
     */
    private function draw_watermark(\TCPDF $pdf): void {
        $pdf->StartTransform();
        $pdf->SetAlpha(0.28);
        $pdf->SetFont($this->get_pdf_font_family(), 'B', 60);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Rotate(45, 105, 148);
        $pdf->Text(25, 130, self::WATERMARK_TEXT);
        $pdf->StopTransform();

        $pdf->SetAlpha(1);
        $pdf->SetTextColor(0, 0, 0);
    }

    /**
     * Apply watermark to all pages.
     *
     * @param \TCPDF $pdf
     * @return void
     */
    private function apply_watermark_to_all_pages(\TCPDF $pdf): void {
        $pagecount = $pdf->getNumPages();

        for ($page = 1; $page <= $pagecount; $page++) {
            $pdf->setPage($page);
            $this->draw_watermark($pdf);
        }
    }

    /**
     * Convert mixed content into safe plain text.
     *
     * @param mixed $value
     * @return string
     */
    private function txt($value): string {
        if ($value === null || is_array($value) || is_object($value)) {
            return '';
        }

        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    /**
     * Write one line of text.
     *
     * @param \TCPDF $pdf
     * @param string $text
     * @param string $style
     * @param int $size
     * @return void
     */
    private function line(\TCPDF $pdf, string $text, string $style = '', int $size = 10): void {
        if ($text === '') {
            return;
        }

        $pdf->SetFont($this->get_pdf_font_family(), $style, $size);
        $pdf->MultiCell(0, 0, $this->bidi($text), 0, $this->get_pdf_alignment(), false, 1);
    }

    /**
     * Ensure space for a section header and the next question.
     *
     * @param \TCPDF $pdf
     * @param float $questionheight
     * @return void
     */
    private function ensure_space_for_section_header(\TCPDF $pdf, float $questionheight): void {
        $requiredheight = 12 + 12 + $questionheight;

        if ($pdf->GetY() + $requiredheight > self::PAGE_CONTENT_BOTTOM_LIMIT) {
            $pdf->AddPage();
            $pdf->SetY(self::PAGE_START_Y);
        }
    }

    /**
     * Ensure space for a page header and the next question.
     *
     * @param \TCPDF $pdf
     * @param float $questionheight
     * @return void
     */
    private function ensure_space_for_page_header(\TCPDF $pdf, float $questionheight): void {
        $requiredheight = 12 + $questionheight;

        if ($pdf->GetY() + $requiredheight > self::PAGE_CONTENT_BOTTOM_LIMIT) {
            $pdf->AddPage();
            $pdf->SetY(self::PAGE_START_Y);
        }
    }

    /**
     * Draw a section header.
     *
     * @param \TCPDF $pdf
     * @param array $question
     * @return void
     */
    private function draw_section_header(\TCPDF $pdf, array $question): void {
        $sectiontitle = trim((string) ($question['sectiontitle'] ?? ''));

        if ($sectiontitle === '') {
            $sectiontitle = get_string('untitledsection', 'quiz_downloadquiz');
        }

        $sectionline = get_string('section', 'quiz_downloadquiz') . ': ' . $sectiontitle .
            ' | ' .
            get_string('sectionshuffle', 'quiz_downloadquiz') . ': ' .
            (string) ($question['sectionshuffle'] ?? get_string('no', 'quiz_downloadquiz'));

        $pdf->SetX(self::PAGE_MARGIN_LEFT);
        $pdf->SetFont($this->get_pdf_font_family(), 'B', 13);
        $pdf->MultiCell(0, 0, $sectionline, 0, $this->get_pdf_alignment(), false, 1);
        $pdf->Ln(2);
    }

    /**
     * Draw a quiz page header.
     *
     * @param \TCPDF $pdf
     * @param int $page
     * @return void
     */
    private function draw_page_header(\TCPDF $pdf, int $page): void {
        $this->line(
            $pdf,
            get_string('page', 'quiz_downloadquiz') . ' ' . $page . ' ' .
            get_string('ofthequiz', 'quiz_downloadquiz'),
            'B',
            11
        );

        $pdf->Ln(2);
    }

    /**
     * Estimate ddmarker overlay height using the real image aspect ratio.
     *
     * @param array $question
     * @param float $boxwidth
     * @return float
     */
    private function estimate_ddmarker_overlay_height(array $question, float $boxwidth): float {
        $backgroundurl = (string) ($question['ddmarkeroverlay']['backgroundimage'] ?? '');
        if ($backgroundurl === '') {
            return 0;
        }

        $tempfile = $this->pluginfile_url_to_tempfile($backgroundurl);
        if ($tempfile === '' || !file_exists($tempfile)) {
            return 85;
        }

        $size = @getimagesize($tempfile);
        if (!$size || empty($size[0]) || empty($size[1])) {
            return 85;
        }

        $realwidth = (float) $size[0];
        $realheight = (float) $size[1];
        $renderwidth = $boxwidth - 8;
        $renderheight = ($realheight / $realwidth) * $renderwidth;

        return $renderheight + 8;
    }

    /**
     * Estimate ddimageortext overlay height using the real image aspect ratio.
     *
     * @param array $question
     * @param float $boxwidth
     * @return float
     */
    private function estimate_ddimage_overlay_height(array $question, float $boxwidth): float {
        $backgroundurl = (string) ($question['ddimageortextoverlay']['backgroundimage'] ?? '');
        if ($backgroundurl === '') {
            return 0;
        }

        $tempfile = $this->pluginfile_url_to_tempfile($backgroundurl);
        if ($tempfile === '' || !file_exists($tempfile)) {
            return 85;
        }

        $size = @getimagesize($tempfile);
        if (!$size || empty($size[0]) || empty($size[1])) {
            return 85;
        }

        $realwidth = (float) $size[0];
        $realheight = (float) $size[1];
        $renderwidth = $boxwidth - 8;
        $renderheight = ($realheight / $realwidth) * $renderwidth;

        return $renderheight + 8;
    }

    /**
     * Estimate a question block height.
     *
     * @param array $question
     * @return float
     */
    private function estimate_question_height(array $question): float {
        $height = 22;

        $questiontext = $this->txt($question['questionhtml'] ?? '');
        $height += max(5, ceil(strlen($questiontext) / 95) * 4.2);

        if (!empty($question['answers']) && is_array($question['answers'])) {
            $height += 8;

            foreach ($question['answers'] as $answer) {
                $label = $this->txt($answer['label'] ?? '');
                $height += max(4, ceil(strlen($label) / 90) * 4.2);
            }
        }

        if (!empty($question['notes']) && is_array($question['notes'])) {
            foreach ($question['notes'] as $note) {
                $height += max(3, ceil(strlen($this->txt($note)) / 100) * 3.5);
            }
        }

        if (!empty($question['questionimages']) && is_array($question['questionimages'])) {
            $height += count($question['questionimages']) * 85;
        }

        if (($question['qtype'] ?? '') === 'ddmarker' && !empty($question['ddmarkeroverlay']['backgroundimage'])) {
            $height += $this->estimate_ddmarker_overlay_height($question, self::BOX_WIDTH);
        }

        if (
                ($question['qtype'] ?? '') === 'ddimageortext' &&
                !empty($question['ddimageortextoverlay']['backgroundimage'])
        ) {
            $height += $this->estimate_ddimage_overlay_height($question, self::BOX_WIDTH);
        }

        return $height + 6;
    }

    /**
     * Draw the confidentiality warning box.
     *
     * @param \TCPDF $pdf
     * @param array $data
     * @return void
     */
    private function draw_confidential_warning_box(\TCPDF $pdf, array $data): void {
        $x = self::PAGE_MARGIN_LEFT;
        $y = $pdf->GetY();
        $w = self::BOX_WIDTH;
        $padding = 5;

        $fullname = $this->txt($data['userfullname'] ?? '');
        $email = $this->txt($data['useremail'] ?? '');
        $generatedat = $this->txt($data['generatedat'] ?? '');
        $servertimezone = $this->txt($data['servertimezone'] ?? '');

        $userdetails = trim($fullname . ($email !== '' ? ' (' . $email . ')' : ''));
        if ($generatedat !== '') {
            $userdetails .= ' ' . get_string('generatedon', 'quiz_downloadquiz') . ' ' . $generatedat;
        }
        if ($servertimezone !== '') {
            $userdetails .= ' (' . $servertimezone . ')';
        }

        $body = get_string('confidentialnoticefull', 'quiz_downloadquiz', (object) [
            'user' => $userdetails,
        ]);

        $heading = get_string('confidentialnoticeprefix', 'quiz_downloadquiz') . ':';

        $pdf->SetFont($this->get_pdf_font_family(), 'B', 11);
        $headingheight = $pdf->getStringHeight($w - ($padding * 2), $heading, false, true, '', 1);

        $pdf->SetFont($this->get_pdf_font_family(), '', 11);
        $bodyheight = $pdf->getStringHeight($w - ($padding * 2), $body, false, true, '', 1);

        $height = ($padding * 2) + $headingheight + 3 + $bodyheight;

        $pdf->SetFillColor(248, 215, 218);
        $pdf->SetDrawColor(220, 53, 69);
        $pdf->RoundedRect($x, $y, $w, $height, 2, '1111', 'DF');

        $pdf->SetTextColor(180, 0, 0);

        $pdf->SetXY($x + $padding, $y + $padding);
        $pdf->SetFont($this->get_pdf_font_family(), 'B', 11);
        $pdf->MultiCell($w - ($padding * 2), 0, $heading, 0, $this->get_pdf_alignment(), false, 1);

        $pdf->Ln(2);

        $pdf->SetX($x + $padding);
        $pdf->SetFont($this->get_pdf_font_family(), '', 11);
        $pdf->MultiCell($w - ($padding * 2), 0, $body, 0, $this->get_pdf_alignment(), false, 1);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetY($y + $height + 5);
    }

    /**
     * Draw metadata box.
     *
     * @param \TCPDF $pdf
     * @param array $data
     * @return void
     */
    private function draw_metadata_box(\TCPDF $pdf, array $data): void {
        $x = self::PAGE_MARGIN_LEFT;
        $y = $pdf->GetY();
        $w = self::BOX_WIDTH;
        $padding = 4;
        $sectionspacing = 4;
        $linegap = 1.2;

        $generalrows = [
            [get_string('course', 'quiz_downloadquiz') . ':', $this->txt($data['coursefullname'] ?? '')],
            [get_string('quizstartdate', 'quiz_downloadquiz') . ':', $this->txt($data['quizstartdate'] ?? '')],
            [get_string('quizenddate', 'quiz_downloadquiz') . ':', $this->txt($data['quizenddate'] ?? '')],
            [get_string('timelimit', 'quiz_downloadquiz') . ':', $this->txt($data['timelimit'] ?? '')],
            [get_string('attemptsallowed', 'quiz_downloadquiz') . ':', $this->txt($data['attemptsallowed'] ?? '')],
            [get_string('quizmaximumgrade', 'quiz_downloadquiz') . ':', $this->txt($data['quizmaximumgrade'] ?? '')],
            [get_string('gradetopass', 'quiz_downloadquiz') . ':', $this->txt($data['gradetopass'] ?? '')],
            [get_string('navigationmethod', 'quiz_downloadquiz') . ':', $this->txt($data['navigationmethod'] ?? '')],
            [get_string('shufflewithinquestions', 'quiz_downloadquiz') . ':', $this->txt($data['shufflewithinquestions'] ?? '')],
        ];

        $compositionrows = $this->build_composition_rows($data);

        $securityrows = [
            [get_string('quizpassword', 'quiz_downloadquiz'), $this->txt($data['quizpassword'] ?? '')],
            [get_string('blockconcurrentconnections', 'quiz_downloadquiz'), $this->txt($data['blockconcurrentconnections'] ?? '')],
            [get_string('networkrestriction', 'quiz_downloadquiz'), $this->txt($data['networkrestriction'] ?? '')],
            [get_string('safeexambrowser', 'quiz_downloadquiz'), $this->txt($data['safeexambrowser'] ?? '')],
        ];

        $sections = [
            get_string('quizgeneralinformation', 'quiz_downloadquiz') => $generalrows,
            get_string('assessmentcomposition', 'quiz_downloadquiz') => $compositionrows,
            get_string('quizsecurityinformation', 'quiz_downloadquiz') => $securityrows,
        ];

        if (right_to_left()) {
            $height = $this->estimate_metadata_box_height_arabic($pdf, $sections, $w, $padding, $linegap, $sectionspacing);
            $pdf->RoundedRect($x, $y, $w, $height, 2, '1111', 'D');

            $currenty = $y + $padding;

            $rightcolw = 78;
            $leftcolw = 78;
            $midgap = 10;

            $rightx = $x + $w - $padding - $rightcolw;
            $leftx = $x + $padding;

            foreach ($sections as $sectiontitle => $sectionrows) {
                $pdf->SetXY($x + $padding, $currenty);
                $pdf->SetFont($this->get_pdf_font_family(), 'B', 11);
                $pdf->MultiCell(
                    $w - ($padding * 2),
                    0,
                    $sectiontitle,
                    0,
                    'R',
                    false,
                    1
                );

                $currenty = $pdf->GetY() + 1.5;

                foreach ($sectionrows as $row) {
                    $label = '• ' . $this->txt($row[0]);
                    $value = $this->txt($row[1]);

                    $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
                    $labelheight = $pdf->getStringHeight($rightcolw, $label, false, true, '', 1);

                    $pdf->SetFont($this->get_pdf_font_family(), '', 10);
                    $valueheight = $pdf->getStringHeight($leftcolw, $value, false, true, '', 1);

                    $rowheight = max($labelheight, $valueheight, 5.2);

                    $pdf->SetXY($rightx, $currenty);
                    $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
                    $pdf->MultiCell(
                        $rightcolw,
                        $rowheight,
                        $this->bidi($value),
                        0,
                        'R',
                        false,
                        0,
                        '',
                        '',
                        true,
                        0,
                        false,
                        true,
                        $rowheight,
                        'T'
                    );

                    $pdf->SetXY($leftx, $currenty);
                    $pdf->SetFont($this->get_pdf_font_family(), '', 10);
                    $pdf->MultiCell(
                        $leftcolw,
                        $rowheight,
                        $this->bidi($label),
                        0,
                        'R',
                        false,
                        1,
                        '',
                        '',
                        true,
                        0,
                        false,
                        true,
                        $rowheight,
                        'T'
                    );

                    $currenty += $rowheight + $linegap;
                }

                $currenty += $sectionspacing;
            }

            $pdf->SetY($y + $height + 8);
            return;
        }

        $bulletwidth = 6;
        $labelwidth = 66;
        $valuewidth = $w - ($padding * 2) - $bulletwidth - $labelwidth;
        $lineheight = 5.2;

        $height = $this->estimate_metadata_box_height(
            $pdf,
            $sections,
            $labelwidth,
            $valuewidth,
            $lineheight,
            $padding
        );

        $pdf->RoundedRect($x, $y, $w, $height, 2, '1111', 'D');

        $currenty = $y + $padding;
        $sectioncount = 0;

        foreach ($sections as $sectiontitle => $sectionrows) {
            $pdf->SetXY($x + $padding, $currenty);
            $pdf->SetFont($this->get_pdf_font_family(), 'B', 11);
            $pdf->MultiCell($w - ($padding * 2), 0, $sectiontitle, 0, 'L', false, 1);

            $currenty = $pdf->GetY() + 1.5;

            foreach ($sectionrows as $row) {
                $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
                $labelheight = $pdf->getStringHeight($labelwidth, $row[0], false, true, '', 1);

                $pdf->SetFont($this->get_pdf_font_family(), '', 10);
                $valueheight = $pdf->getStringHeight($valuewidth, $row[1], false, true, '', 1);

                $rowheight = max($lineheight, $labelheight, $valueheight);

                $bulletx = $x + $padding;
                $labelx = $bulletx + $bulletwidth;
                $valuex = $labelx + $labelwidth;

                $pdf->SetXY($bulletx, $currenty);
                $pdf->SetFont($this->get_pdf_font_family(), '', 10);
                $pdf->Cell($bulletwidth, $rowheight, '•', 0, 0, 'L');

                $pdf->SetXY($labelx, $currenty);
                $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
                $pdf->MultiCell(
                    $labelwidth,
                    $rowheight,
                    $row[0],
                    0,
                    'L',
                    false,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $rowheight,
                    'T'
                );

                $pdf->SetXY($valuex, $currenty);
                $pdf->SetFont($this->get_pdf_font_family(), '', 10);
                $pdf->MultiCell(
                    $valuewidth,
                    $rowheight,
                    $row[1],
                    0,
                    'L',
                    false,
                    1,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $rowheight,
                    'T'
                );

                $currenty += $rowheight;
            }

            $sectioncount++;
            if ($sectioncount < count($sections)) {
                $currenty += 3;
            }
        }

        $pdf->SetY($y + $height + 8);
    }

    /**
     * Build assessment composition rows.
     *
     * @param array $data
     * @return array
     */
    private function build_composition_rows(array $data): array {
        $composition = $data['assessmentcomposition'] ?? [
            'totalquestions' => 0,
            'types' => [],
            'totalmarks' => 0.0,
        ];

        $rows = [];
        $rows[] = [
            get_string('totalquestions', 'quiz_downloadquiz') . ':',
            (string) ($composition['totalquestions'] ?? 0),
        ];

        $qtypelabels = [
            'essay' => get_string('qtypeessay', 'quiz_downloadquiz'),
            'multichoice' => get_string('qtypemultichoice', 'quiz_downloadquiz'),
            'truefalse' => get_string('qtypetruefalse', 'quiz_downloadquiz'),
            'shortanswer' => get_string('qtypeshortanswer', 'quiz_downloadquiz'),
            'match' => get_string('qtypematch', 'quiz_downloadquiz'),
            'numerical' => get_string('qtypenumerical', 'quiz_downloadquiz'),
        ];

        $percentages = $this->calculate_question_type_percentages($composition);

        foreach (($composition['types'] ?? []) as $qtype => $stats) {
            $count = (int) ($stats['count'] ?? 0);
            $percentage = $percentages[$qtype] ?? 0.0;
            $label = $qtypelabels[$qtype] ?? ucfirst((string) $qtype);

            $rows[] = [
                $label . ':',
                $count . ' (' . format_float($percentage, 1) . '%)',
            ];
        }

        return $rows;
    }

    /**
     * Calculate question type percentages with consistent rounded totals.
     *
     * @param array $composition
     * @return array
     */
    private function calculate_question_type_percentages(array $composition): array {
        $totalquestions = (int) ($composition['totalquestions'] ?? 0);
        $rounded = [];
        $remainders = [];

        foreach (($composition['types'] ?? []) as $qtype => $stats) {
            $count = (int) ($stats['count'] ?? 0);
            $exact = $totalquestions > 0 ? ($count / $totalquestions) * 100 : 0.0;
            $floor = floor($exact * 10) / 10;

            $rounded[$qtype] = $floor;
            $remainders[$qtype] = $exact - $floor;
        }

        $currenttotal = array_sum($rounded);
        $diff = round(100 - $currenttotal, 1);
        $steps = (int) round($diff * 10);

        arsort($remainders);

        foreach ($remainders as $qtype => $remainder) {
            if ($steps <= 0) {
                break;
            }

            $rounded[$qtype] += 0.1;
            $steps--;
        }

        return $rounded;
    }

    /**
     * Estimate metadata box height.
     *
     * @param \TCPDF $pdf
     * @param array $sections
     * @param float $labelwidth
     * @param float $valuewidth
     * @param float $lineheight
     * @param float $padding
     * @return float
     */
    private function estimate_metadata_box_height(
        \TCPDF $pdf,
        array $sections,
        float $labelwidth,
        float $valuewidth,
        float $lineheight,
        float $padding
    ): float {
        $sectiontitleheight = 7;
        $contentheight = 0;
        $sectionindex = 0;
        $sectioncount = count($sections);

        foreach ($sections as $rows) {
            $contentheight += $sectiontitleheight;

            foreach ($rows as $row) {
                $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
                $labelheight = $pdf->getStringHeight($labelwidth, $row[0], false, true, '', 1);

                $pdf->SetFont($this->get_pdf_font_family(), '', 10);
                $valueheight = $pdf->getStringHeight($valuewidth, $row[1], false, true, '', 1);

                $contentheight += max($lineheight, $labelheight, $valueheight);
            }

            $sectionindex++;
            if ($sectionindex < $sectioncount) {
                $contentheight += 3;
            }
        }

        return ($padding * 2) + $contentheight;
    }


    /**
     * Estimate metadata box height for Arabic split-column layout.
     *
     * @param \TCPDF $pdf
     * @param array $sections
     * @param float $boxwidth
     * @param float $padding
     * @param float $linegap
     * @param float $sectionspacing
     * @return float
     */
    private function estimate_metadata_box_height_arabic(
        \TCPDF $pdf,
        array $sections,
        float $boxwidth,
        float $padding,
        float $linegap,
        float $sectionspacing
    ): float {
        $rightcolw = 78;
        $leftcolw = 78;

        $height = $padding * 2;
        $sectionindex = 0;
        $sectioncount = count($sections);

        foreach ($sections as $sectiontitle => $rows) {
            $pdf->SetFont($this->get_pdf_font_family(), 'B', 11);
            $height += $pdf->getStringHeight($boxwidth - ($padding * 2), $sectiontitle, false, true, '', 1);
            $height += 1.5;

            foreach ($rows as $row) {
                $label = '• ' . $this->txt($row[0]);
                $value = $this->txt($row[1]);

                $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
                $labelheight = $pdf->getStringHeight($rightcolw, $label, false, true, '', 1);

                $pdf->SetFont($this->get_pdf_font_family(), '', 10);
                $valueheight = $pdf->getStringHeight($leftcolw, $value, false, true, '', 1);

                $height += max($labelheight, $valueheight, 5.2) + $linegap;
            }

            $sectionindex++;
            if ($sectionindex < $sectioncount) {
                $height += $sectionspacing;
            }
        }

        return $height;
    }

    /**
     * Draw the intentional blank-area notice.
     *
     * @param \TCPDF $pdf
     * @return void
     */
    private function draw_intentionally_blank_notice(\TCPDF $pdf): void {
        $x = self::PAGE_MARGIN_LEFT;
        $y = $pdf->GetY();
        $w = self::BOX_WIDTH;
        $padding = 5;
        $bottommargin = self::PAGE_MARGIN_BOTTOM;

        $message = get_string('intentionallyblanknotice', 'quiz_downloadquiz');
        $pageheight = $pdf->getPageHeight();
        $height = $pageheight - $y - $bottommargin;

        if ($height < 20) {
            return;
        }

        $pdf->SetFillColor(250, 250, 250);
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->RoundedRect($x, $y, $w, $height, 2, '1111', 'D');

        $pdf->SetFont($this->get_pdf_font_family(), 'I', 11);
        $textheight = $pdf->getStringHeight($w - ($padding * 2), $message, false, true, '', 1);
        $texty = $y + max($padding, (($height - $textheight) / 2));

        $pdf->SetXY($x + $padding, $texty);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetFont($this->get_pdf_font_family(), 'I', 11);
        $pdf->MultiCell($w - ($padding * 2), 0, $message, 0, 'C', false, 1);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetY($y + $height);
    }

    /**
     * Draw ddmarker answer image with labels inside the image.
     *
     * @param \TCPDF $pdf
     * @param array $overlay
     * @param float $x
     * @param float $w
     * @return void
     */
    private function draw_ddmarker_answer_image(\TCPDF $pdf, array $overlay, float $x, float $w): void {
        if (empty($overlay['backgroundimage'])) {
            return;
        }

        $tempfile = $this->pluginfile_url_to_tempfile((string) $overlay['backgroundimage']);
        if ($tempfile === '' || !file_exists($tempfile)) {
            return;
        }

        $size = @getimagesize($tempfile);
        if (!$size || empty($size[0]) || empty($size[1])) {
            return;
        }

        $realwidth = (float) $size[0];
        $realheight = (float) $size[1];

        $pdf->Ln(3);

        $starty = $pdf->GetY();
        $startx = $x + 4;
        $imgw = $w - 8;
        $imgh = ($realheight / $realwidth) * $imgw;

        $pdf->Image(
            $tempfile,
            $startx,
            $starty,
            $imgw,
            $imgh,
            '',
            '',
            '',
            false,
            300,
            '',
            false,
            false,
            1,
            false,
            false,
            false
        );

        foreach (($overlay['drops'] ?? []) as $drop) {
            $coords = (string) ($drop['coords'] ?? '');
            $label = trim((string) ($drop['label'] ?? ''));

            if ($coords === '' || $label === '') {
                continue;
            }

            $positionpart = explode(';', $coords)[0] ?? '';
            $xy = array_map('trim', explode(',', $positionpart));

            if (count($xy) < 2) {
                continue;
            }

            $dx = (float) $xy[0];
            $dy = (float) $xy[1];

            $labelx = $startx + ($dx / $realwidth) * $imgw;
            $labely = $starty + ($dy / $realheight) * $imgh;

            $labelx = max($startx + 1, min($labelx, $startx + $imgw - 30));
            $labely = max($starty + 1, min($labely, $starty + $imgh - 5));

            $pdf->SetXY($labelx, $labely);
            $pdf->SetFont($this->get_pdf_font_family(), '', 8);
            $pdf->SetTextColor(0, 0, 0);

            $textwidth = min(32, max(12, $pdf->GetStringWidth($label) + 2));
            $pdf->MultiCell(
                $textwidth,
                0,
                $label,
                0,
                $this->get_pdf_alignment(),
                false,
                1,
                $labelx,
                $labely,
                true,
                0,
                false,
                true,
                0,
                'T'
            );
        }

        $pdf->SetY($starty + $imgh + 4);
    }

    /**
     * Draw ddimageortext answer image.
     *
     * @param \TCPDF $pdf
     * @param array $overlay
     * @param float $x
     * @param float $w
     * @return void
     */
    private function draw_ddimageortext_answer_image(\TCPDF $pdf, array $overlay, float $x, float $w): void {
        if (empty($overlay['backgroundimage'])) {
            return;
        }

        $bgtempfile = $this->pluginfile_url_to_tempfile((string) $overlay['backgroundimage']);
        if ($bgtempfile === '' || !file_exists($bgtempfile)) {
            return;
        }

        $size = @getimagesize($bgtempfile);
        if (!$size || empty($size[0]) || empty($size[1])) {
            return;
        }

        $realwidth = (float) $size[0];
        $realheight = (float) $size[1];

        $pdf->Ln(3);

        $starty = $pdf->GetY();
        $startx = $x + 4;
        $imgw = $w - 8;
        $imgh = ($realheight / $realwidth) * $imgw;

        $pdf->Image(
            $bgtempfile,
            $startx,
            $starty,
            $imgw,
            $imgh,
            '',
            '',
            '',
            false,
            300,
            '',
            false,
            false,
            1,
            false,
            false,
            false
        );

        foreach (($overlay['drops'] ?? []) as $drop) {
            $dx = (float) ($drop['xleft'] ?? 0);
            $dy = (float) ($drop['ytop'] ?? 0);

            $targetx = $startx + ($dx / $realwidth) * $imgw;
            $targety = $starty + ($dy / $realheight) * $imgh;

            $targetx = max($startx + 1, min($targetx, $startx + $imgw - 32));
            $targety = max($starty + 1, min($targety, $starty + $imgh - 12));

            $dragimageurl = (string) ($drop['dragimageurl'] ?? '');
            $draglabel = trim((string) ($drop['draglabel'] ?? ''));

            if ($dragimageurl !== '') {
                $dragtempfile = $this->pluginfile_url_to_tempfile($dragimageurl);
                if ($dragtempfile !== '' && file_exists($dragtempfile)) {
                    $dragsize = @getimagesize($dragtempfile);

                    if ($dragsize && !empty($dragsize[0]) && !empty($dragsize[1])) {
                        $draww = 16.0;
                        $drawh = ((float) $dragsize[1] / (float) $dragsize[0]) * $draww;

                        $pdf->Image(
                            $dragtempfile,
                            $targetx,
                            $targety,
                            $draww,
                            $drawh,
                            '',
                            '',
                            '',
                            false,
                            300,
                            '',
                            false,
                            false,
                            1,
                            false,
                            false,
                            false
                        );

                        continue;
                    }
                }
            }

            if ($draglabel !== '') {
                $pdf->SetFont($this->get_pdf_font_family(), '', 8);
                $textwidth = min(34, max(14, $pdf->GetStringWidth($draglabel) + 4));

                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetDrawColor(120, 120, 120);
                $pdf->SetTextColor(0, 0, 0);

                $pdf->MultiCell(
                    $textwidth,
                    0,
                    $draglabel,
                    1,
                    'C',
                    true,
                    1,
                    $targetx,
                    $targety,
                    true,
                    0,
                    false,
                    true,
                    0,
                    'T'
                );
            }
        }

        $pdf->SetY($starty + $imgh + 4);
    }

    /**
     * Draw one question box.
     *
     * @param \TCPDF $pdf
     * @param array $question
     * @return void
     */
    private function draw_question_box(\TCPDF $pdf, array $question): void {
        $x = self::PAGE_MARGIN_LEFT;
        $y = $pdf->GetY();
        $w = self::BOX_WIDTH;

        $estimatedheight = $this->estimate_question_height($question);
        if ($y + $estimatedheight > self::PAGE_CONTENT_BOTTOM_LIMIT) {
            $pdf->AddPage();
            $pdf->SetY(self::PAGE_START_Y);
            $y = $pdf->GetY();
        }

        $innerx = $x + self::BOX_PADDING;
        $innery = $y + self::BOX_PADDING;
        $innerw = $w - (self::BOX_PADDING * 2);

        $pdf->SetXY($innerx, $innery);

        $title = get_string('question', 'quiz_downloadquiz') . ' ' . ($question['slot'] ?? '') .
            ' (' . $this->txt($question['qtype'] ?? '') .
            ' | ' . get_string('maxmark', 'quiz_downloadquiz') .
            $this->txt((string) ($question['maxmark'] ?? '')) . ')';

        $this->line($pdf, $this->bidi($title), 'B', 11);

        $qtext = $this->txt($question['questionhtml'] ?? '');
        if ($qtext !== '') {
            $pdf->SetX($innerx);
            $pdf->SetFont($this->get_pdf_font_family(), '', 10);
            $pdf->MultiCell($innerw, 0, $this->bidi($qtext), 0, $this->get_pdf_alignment(), false, 1);
        }

        if (!empty($question['questionimages']) && is_array($question['questionimages'])) {
            $this->draw_question_images($pdf, $question['questionimages'], $x, $w);
        }

        $pdf->Ln(3);

        if (($question['qtype'] ?? '') === 'ddmarker' && !empty($question['ddmarkeroverlay'])) {
            $this->draw_ddmarker_answer_image($pdf, $question['ddmarkeroverlay'], $x, $w);
        }

        if (($question['qtype'] ?? '') === 'ddimageortext' && !empty($question['ddimageortextoverlay'])) {
            $this->draw_ddimageortext_answer_image($pdf, $question['ddimageortextoverlay'], $x, $w);
        }

        if (($question['qtype'] ?? '') === 'gapselect') {
            $this->draw_gapselect_block($pdf, $question, $innerx, $innerw, $x, $w);
        } else {
            $this->draw_standard_answers_and_notes($pdf, $question, $innerx, $innerw, $x, $w);
        }

        $contentbottom = $pdf->GetY();
        $actualheight = max(18, ($contentbottom - $y) + self::BOX_PADDING);

        $pdf->RoundedRect($x, $y, $w, $actualheight, 2, '1111', 'D');
        $pdf->SetY($y + $actualheight + 6);
    }

    /**
     * Draw answers and notes for a standard question.
     *
     * @param \TCPDF $pdf
     * @param array $question
     * @param float $innerx
     * @param float $innerw
     * @param float $x
     * @param float $w
     * @return void
     */
    private function draw_standard_answers_and_notes(
        \TCPDF $pdf,
        array $question,
        float $innerx,
        float $innerw,
        float $x,
        float $w
    ): void {
        if (!empty($question['answers']) && is_array($question['answers'])) {
            $answerlabel = $this->txt($question['answerlabel'] ?? 'Correct answer');

            $pdf->SetX($innerx);
            $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
            $pdf->MultiCell($innerw, 0, $answerlabel . ':', 0, $this->get_pdf_alignment(), false, 1);

            $pdf->Ln(1);

            $ismultiple = !empty($question['ismultiple']);

            foreach ($question['answers'] as $answer) {
                $text = $this->txt($answer['label'] ?? '');
                if ($text === '') {
                    continue;
                }

                if (isset($answer['fraction']) && is_numeric($answer['fraction'])) {
                    $percent = round(((float) $answer['fraction']) * 100);
                    $text .= ' (' . $percent . '%)';
                }

                if ($ismultiple) {
                    $prefix = !empty($answer['iscorrect']) ? '[x] ' : '[   ]';
                } else {
                    $prefix = !empty($answer['iscorrect']) ? '• ' : 'o ';
                }

                $style = !empty($answer['iscorrect']) ? 'B' : '';

                $pdf->SetX($x + 8);
                $pdf->SetFont($this->get_pdf_font_family(), $style, 10);
                $pdf->MultiCell($w - 12, 0, $this->bidi($prefix . $text), 0, $this->get_pdf_alignment(), false, 1);
            }
        }

        if (!empty($question['notes']) && is_array($question['notes'])) {
            $pdf->Ln(1);

            foreach ($question['notes'] as $note) {
                $notetext = $this->txt($note);
                if ($notetext === '') {
                    continue;
                }

                $pdf->SetX($innerx);
                $pdf->SetFont($this->get_pdf_font_family(), 'I', 9);
                $pdf->MultiCell($innerw, 0, $notetext, 0, $this->get_pdf_alignment(), false, 1);
            }
        }
    }

    /**
     * Draw answers and notes for a gapselect question.
     *
     * @param \TCPDF $pdf
     * @param array $question
     * @param float $innerx
     * @param float $innerw
     * @param float $x
     * @param float $w
     * @return void
     */
    private function draw_gapselect_block(
        \TCPDF $pdf,
        array $question,
        float $innerx,
        float $innerw,
        float $x,
        float $w
    ): void {
        if (!empty($question['notes']) && is_array($question['notes'])) {
            $pdf->Ln(1);

            foreach ($question['notes'] as $note) {
                $notetext = $this->txt($note);
                if ($notetext === '') {
                    continue;
                }

                $pdf->SetX($innerx);
                $pdf->SetFont($this->get_pdf_font_family(), 'I', 9);
                $pdf->MultiCell($innerw, 0, $notetext, 0, $this->get_pdf_alignment(), false, 1);
            }
        }

        if (!empty($question['answers']) && is_array($question['answers'])) {
            $pdf->Ln(1);

            $answerlabel = $this->txt($question['answerlabel'] ?? 'Correct answer');

            $pdf->SetX($innerx);
            $pdf->SetFont($this->get_pdf_font_family(), 'B', 10);
            $pdf->MultiCell($innerw, 0, $answerlabel . ':', 0, $this->get_pdf_alignment(), false, 1);

            $pdf->Ln(1);

            foreach ($question['answers'] as $answer) {
                $text = $this->txt($answer['label'] ?? '');
                if ($text === '') {
                    continue;
                }

                $pdf->SetX($x + 8);
                $pdf->SetFont($this->get_pdf_font_family(), !empty($answer['iscorrect']) ? 'B' : '', 10);
                $pdf->MultiCell($w - 12, 0, '• ' . $text, 0, $this->get_pdf_alignment(), false, 1);
            }
        }
    }

    /**
     * Render question images in the PDF.
     *
     * @param \TCPDF $pdf
     * @param array $images
     * @param float $x
     * @param float $w
     * @return void
     */
    private function draw_question_images(\TCPDF $pdf, array $images, float $x, float $w): void {
        foreach ($images as $imageurl) {
            $tempfile = $this->pluginfile_url_to_tempfile((string) $imageurl);
            if ($tempfile === '') {
                continue;
            }

            $pdf->Ln(2);
            $currenty = $pdf->GetY();

            $maxwidth = $w - 8;
            $maxheight = 80;

            try {
                $pdf->Image(
                    $tempfile,
                    $x + 4,
                    $currenty,
                    $maxwidth,
                    $maxheight,
                    '',
                    '',
                    '',
                    false,
                    300,
                    '',
                    false,
                    false,
                    1,
                    false,
                    false,
                    false
                );

                $pdf->SetY($currenty + $maxheight + 2);
            } catch (\Exception $e) {
                if (debugging('', DEBUG_DEVELOPER)) {
                    debugging('Image rendering failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
                }
            }
        }
    }

    /**
     * Convert a Moodle pluginfile URL into a temporary local file.
     *
     * @param string $url
     * @return string
     */
    private function pluginfile_url_to_tempfile(string $url): string {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return '';
        }

        $script = '/pluginfile.php/';
        $pos = strpos($path, $script);
        if ($pos === false) {
            return '';
        }

        $relative = substr($path, $pos + strlen($script));
        $parts = explode('/', trim($relative, '/'));

        if (count($parts) < 5) {
            return '';
        }

        $contextid = (int) array_shift($parts);
        $component = (string) array_shift($parts);
        $filearea = (string) array_shift($parts);
        $itemid = (int) array_shift($parts);
        $filename = (string) array_pop($parts);

        $filepath = '/';
        if (!empty($parts)) {
            $filepath .= implode('/', $parts) . '/';
        }

        $fs = get_file_storage();
        $storedfile = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);

        if (!$storedfile) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if ($extension === '') {
            $extension = 'img';
        }

        $tempfile = make_request_directory() . '/' . sha1($url . microtime(true)) . '.' . $extension;
        $content = $storedfile->get_content();

        if ($content === false || $content === '') {
            return '';
        }

        if (file_put_contents($tempfile, $content) === false) {
            return '';
        }

        if (!file_exists($tempfile) || filesize($tempfile) === 0) {
            return '';
        }

        return $tempfile;
    }
}
