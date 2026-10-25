<?php
/**
 * Helper to generate a "password" element with an eye button (see Siberian_Form_Element_RevealPassword).
 * The toggle itself is the delegated handler `[data-reveal-toggle]` of the flat design (features.js).
 */
class Siberian_View_Helper_FormRevealPassword extends Zend_View_Helper_FormPassword
{
    /**
     * @param string|array $name
     * @param mixed $value
     * @param array|null $attribs
     * @return string
     */
    public function formRevealPassword($name, $value = null, $attribs = null)
    {
        if (is_array($name)) {
            // Array form of the Zend helpers is not supported here, plain password input.
            return $this->formPassword($name, $value, $attribs);
        }

        $attribs = is_array($attribs) ? $attribs : [];
        // Zend prints the value of a password input only when asked: without it a save would store an empty password.
        $attribs['renderPassword'] = true;
        $attribs['data-reveal-input'] = '1';
        if (!isset($attribs['autocomplete'])) {
            // The browser must not fill it with the login of the editor.
            $attribs['autocomplete'] = 'new-password';
        }
        $style = isset($attribs['style']) ? rtrim($attribs['style'], '; ') . ';' : '';
        $attribs['style'] = $style . 'padding-right:36px;';

        $label = __('Show / hide the password');
        $title = $this->view->escape($label);

        $eye = '<svg data-eye width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
            . '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        $eyeOff = '<svg data-eye-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
            . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none">'
            . '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>'
            . '<path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>'
            . '<path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

        $button = '<button type="button" data-reveal-toggle aria-pressed="false" tabindex="-1"'
            . ' title="' . $title . '" aria-label="' . $title . '"'
            . ' style="position:absolute;top:50%;right:8px;transform:translateY(-50%);border:0;background:transparent;'
            . 'cursor:pointer;padding:2px 4px;line-height:1;color:inherit;opacity:.6;">'
            . $eye . $eyeOff . '</button>';

        return '<span class="sb-reveal-password" style="position:relative;display:block;">'
            . $this->formPassword($name, $value, $attribs)
            . $button
            . '</span>';
    }
}
