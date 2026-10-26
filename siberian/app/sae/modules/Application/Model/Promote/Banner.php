<?php

/**
 * Class Application_Model_Promote_Banner
 *
 * Builds the smart banner snippet the application owner pastes in a website.
 */
class Application_Model_Promote_Banner
{
    /**
     * A value as a JavaScript string literal. Quotes, backslashes and new lines are escaped, "<" and ">" too so a
     * title can never close the pasted <script>.
     *
     * @param mixed $value
     * @return string
     */
    public static function literal($value): string
    {
        $encoded = json_encode(
            (string) $value,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $encoded === false ? '""' : $encoded;
    }

    /**
     * @param string $scriptUrl url of smart-app-banner.js
     * @param array $banner title, author, button and the optional store / price labels per platform (ios, android)
     * @return string
     */
    public static function script(string $scriptUrl, array $banner): string
    {
        $lines = [
            'daysHidden: 15',
            'daysReminder: 90',
            'title: ' . self::literal($banner['title'] ?? ''),
            'author: ' . self::literal($banner['author'] ?? ''),
            'button: ' . self::literal($banner['button'] ?? ''),
        ];

        foreach (['store', 'price'] as $key) {
            $entries = [];
            foreach (['ios', 'android'] as $platform) {
                $value = $banner[$key][$platform] ?? '';
                if ($value) {
                    $entries[] = $platform . ': ' . self::literal($value);
                }
            }
            if (!empty($entries)) {
                $lines[] = $key . ": {\n            " . implode(",\n            ", $entries) . "\n        }";
            }
        }

        return '<script src="' . $scriptUrl . '"></script>' . "\n" .
            '<script type="text/javascript">' . "\n" .
            '    new SmartBanner({' . "\n" .
            '        ' . implode(",\n        ", $lines) . "\n" .
            '    });' . "\n" .
            '</script>';
    }
}
