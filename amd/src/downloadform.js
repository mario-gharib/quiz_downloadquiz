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
 * Download form behaviour for quiz_downloadquiz.
 *
 * @module    quiz_downloadquiz/downloadform
 * @copyright 2026 Center for Digital Innovation and Artificial Intelligence <moodle.cinia@usj.edu.lb>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    return {
        init: function(config) {
            var form = document.querySelector('.quiz-downloadquiz-form');
            var input = document.getElementById('id_pdfkey');
            var generateBtn = document.getElementById('downloadquiz-generate-btn');
            var toggleBtn = document.getElementById('downloadquiz-toggle-btn');
            var errorBox = document.getElementById('id_pdfkey_error');

            if (!form || !input || !generateBtn || !toggleBtn || !errorBox) {
                return;
            }

            /**
             * Return one random character from a string.
             *
             * @param {String} set Character set.
             * @returns {String}
             */
            function getRandomChar(set) {
                return set.charAt(Math.floor(Math.random() * set.length));
            }

            /**
             * Generate a valid PDF access key.
             *
             * @param {Number} length Key length.
             * @returns {String}
             */
            function generateKey(length) {
                var upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                var lower = 'abcdefghijkmnopqrstuvwxyz';
                var numbers = '23456789';
                var all = upper + lower + numbers;
                var result = getRandomChar(upper) + getRandomChar(numbers);

                for (var i = 2; i < length; i++) {
                    result += getRandomChar(all);
                }

                return result.split('').sort(function() {
                    return 0.5 - Math.random();
                }).join('');
            }

            /**
             * Show a validation error.
             *
             * @param {String} message Error message.
             */
            function showError(message) {
                input.classList.add('is-invalid');
                input.setAttribute('aria-invalid', 'true');
                errorBox.textContent = message;
                errorBox.style.display = 'block';
            }

            /**
             * Clear the validation error.
             */
            function clearError() {
                input.classList.remove('is-invalid');
                input.removeAttribute('aria-invalid');
                errorBox.textContent = '';
                errorBox.style.display = 'none';
            }

            if (input.value.trim() === '') {
                input.value = generateKey(6);
            }

            generateBtn.addEventListener('click', function() {
                input.value = generateKey(6);
                clearError();
                input.focus();
                input.select();
            });

            toggleBtn.addEventListener('click', function() {
                var isHidden = input.type === 'password';
                var label = isHidden ? config.hideLabel : config.showLabel;
                var icon = isHidden ? config.hideIcon : config.showIcon;

                input.type = isHidden ? 'text' : 'password';
                toggleBtn.setAttribute('title', label);
                toggleBtn.setAttribute('aria-label', label);
                toggleBtn.innerHTML = '<img src="' + icon + '" class="icon" alt="">';
            });

            input.addEventListener('input', function() {
                if (input.value.trim() !== '') {
                    clearError();
                }
            });

            form.addEventListener('submit', function(e) {
                var value = input.value.trim();

                if (value === '') {
                    e.preventDefault();
                    showError(config.requiredError);
                    input.focus();
                    return false;
                }

                if (value.length < 6 || !/[A-Z]/.test(value) || !/[0-9]/.test(value)) {
                    e.preventDefault();
                    showError(config.lengthError);
                    input.focus();
                    return false;
                }

                clearError();
                return true;
            });
        }
    };
});
