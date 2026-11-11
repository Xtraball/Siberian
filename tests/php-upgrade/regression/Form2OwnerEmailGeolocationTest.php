<?php
/**
 * The e-mail to the owner of a form (Form2 submit_owner.phtml) died with "TypeError: Cannot access offset of type string
 * on string" when a geolocation field was left empty: the empty value was replaced by the "not filled" text, and the
 * text was then read as an array (['address']). PHP 7 only warned, PHP 8 throws, and the controller catches Exception,
 * not Error, so the app user got a 500 after the answers were saved.
 *
 * The app sends null for an empty field and ['address' => ..., 'coords' => ['lat' => ..., 'lng' => ...]] when filled.
 */
if (!function_exists('p__')) {
    function p__($context, $text)
    {
        return $text;
    }
}

/** The real template, with the fields of one geolocation answer */
function form2_owner_email($value): string
{
    $view = new class {
        public $fields;

        public function getFields()
        {
            return $this->fields;
        }

        public function render(string $file): string
        {
            ob_start();
            try {
                include $file;
            } finally {
                $html = ob_get_clean();
            }

            return $html;
        }
    };
    $view->fields = [['type' => 'geolocation', 'label' => 'where', 'value' => $value]];

    $text = $view->render(dirname(__DIR__, 3) . '/siberian/app/sae/modules/Form2/resources/design/email/template/form2/submit_owner.phtml');

    return trim(preg_replace('/\s+/', ' ', strip_tags($text)));
}

return [
    'an empty geolocation field is shown as not filled, not a fatal error' => function () {
        foreach ([null, '', [], ['address' => null, 'coords' => null]] as $value) {
            $text = form2_owner_email($value);
            t_true(strpos($text, 'Where :- not filled -') !== false, 'not filled for ' . var_export($value, true) . ": $text");
        }
    },

    'a filled geolocation field is shown as before' => function () {
        $text = form2_owner_email(['address' => '1 rue X', 'coords' => ['lat' => '48.10000', 'lng' => '2.30000']]);

        t_true(strpos($text, 'Where :1 rue XLat: 48.10000 - Long: 2.30000') !== false, $text);
    },

    'a position without an address shows its coordinates' => function () {
        $text = form2_owner_email(['address' => null, 'coords' => ['lat' => '48.1', 'lng' => '2.3']]);

        t_true(strpos($text, 'Lat: 48.1 - Long: 2.3') !== false, $text);
        t_true(strpos($text, 'not filled') === false, 'it is filled');
    },

    'a text from an older app is shown, escaped' => function () {
        $text = form2_owner_email('48.1, 2.3 <b>x</b>');

        // strip_tags() leaves the entities: the tag of the text was escaped, not sent as markup
        t_true(strpos($text, 'Where :48.1, 2.3 &lt;b&gt;x&lt;/b&gt;') !== false, $text);
    },
];
