<?php
/**
 * Tier 1: the small helpers every screen leans on (Pure.php), the currency table and the date class, on the real
 * core classes. Most are pure functions, so the tests state the contract and a few properties over many inputs.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';
Core_Model_Directory::setBasePath($siberian);
date_default_timezone_set('UTC');

require_once $siberian . '/lib/Siberian/IconvFallback.php';
require_once $siberian . '/lib/Siberian/Json.php';
require_once $siberian . '/lib/Siberian/Currency.php';

use Siberian\Currency;
use Siberian\Date;

return [
    // cut()
    'cut leaves a short text alone and trims it' => function () {
        t_equal('abc', cut('abc', 5));
        t_equal('abc', cut('  abc  ', 5));
        t_equal('abcde', cut('abcde', 5), 'exactly the length is not cut');
    },
    'cut ends a long text with the suffix, within the length' => function () {
        t_equal('Hello w...', cut('Hello wonderful world', 10));
        t_equal('Hello >>', cut('Hello wonderful world', 8, ' >>'));
        t_equal('...', cut('abcdef', 3), 'a length equal to the suffix gives the suffix');
        foreach ([3, 4, 5, 10, 20] as $length) {
            t_true(mb_strlen(cut(str_repeat('x', 50), $length), 'utf8') <= $length, "length $length");
        }
    },
    'cut never exceeds a length shorter than the suffix' => function () {
        t_equal('ab', cut('abcdef', 2));
        t_equal('a', cut('abcdef', 1));
        t_equal('', cut('abcdef', 0));
    },
    'cut counts characters, not bytes' => function () {
        t_equal('éàü...', cut('éàüéàüéàü', 6));
    },
    'cut removes the tags first' => function () {
        t_equal('Hello world', cut('<b>Hello</b> <i>world</i>', 50));
    },

    // ellipsis()
    'ellipsis keeps both ends of a long text' => function () {
        t_equal('Hello...world', ellipsis('Hello wonderful world', 10));
        t_equal('Hi', ellipsis('Hi', 10));
        t_equal('Hi', ellipsis('  Hi  ', 10));
    },
    'ellipsis with an odd length gives the extra character to the end, without a deprecation' => function () {
        t_equal('Hell...world', ellipsis('Hello wonderful world', 9));
    },
    'ellipsis takes a custom mark and counts characters' => function () {
        t_equal('éàü…üàé', ellipsis('éàü12345678üàé', 6, '…'));
        t_equal('abc~~nop', ellipsis('abcdefghijklmnop', 6, '~~'));
    },

    // formatBytes()
    'formatBytes picks the unit and rounds' => function () {
        t_equal('0 B', formatBytes(0));
        t_equal('1023 B', formatBytes(1023));
        t_equal('1 KB', formatBytes(1024));
        t_equal('1.5 KB', formatBytes(1536));
        t_equal('1 MB', formatBytes(1048576));
        t_equal('5 GB', formatBytes(5 * 1024 ** 3));
        t_equal('1 TB', formatBytes(1024 ** 4));
        t_equal('2 KB', formatBytes(1536, 0));
        t_equal('1.46 KB', formatBytes(1500));
    },
    'formatBytes stops at terabytes and treats a negative size as zero' => function () {
        t_equal('5000 TB', formatBytes(5000 * 1024 ** 4));
        t_equal('0 B', formatBytes(-5));
        t_equal('2 KB', formatBytes('2048'), 'a numeric string works');
    },

    // adjustBrightness()
    'adjustBrightness lightens and darkens a colour' => function () {
        t_equal('#477aad', adjustBrightness('#336699', 20));
        t_equal('#1f5285', adjustBrightness('336699', -20), 'the # is optional');
    },
    'adjustBrightness expands a short colour and clamps each channel' => function () {
        t_equal('#aabbcc', adjustBrightness('#abc', 0));
        t_equal('#ffffff', adjustBrightness('#fff', 10));
        t_equal('#000000', adjustBrightness('#000', -50));
        t_equal('#ffffff', adjustBrightness('#000000', 500), 'steps are limited to 255');
        t_equal('#000000', adjustBrightness('#ffffff', -500));
    },

    // array_flat()
    'array_flat joins nested keys with dots' => function () {
        t_equal(['a' => 1, 'b.c' => 2, 'b.d.e' => 3], array_flat(['a' => 1, 'b' => ['c' => 2, 'd' => ['e' => 3]], 'f' => []]));
        t_equal([], array_flat([]));
        t_equal(['p.0' => 'x', 'p.1' => 'y'], array_flat(['x', 'y'], 'p'));
    },

    // JSON helpers
    'escape_json_string turns line breaks and tabs into something a JSON string carries' => function () {
        t_equal('a<br />b<br /><br />c    d', escape_json_string("a\nb\r\nc\td"));
    },
    'data_to_jsonsafe escapes every leaf, a null becomes an empty string' => function () {
        t_equal(
            ['a' => 'x<br />y', 'b' => ['c' => 't    t'], 'n' => '5', 'z' => ''],
            data_to_jsonsafe(['a' => "x\ny", 'b' => ['c' => "t\tt"], 'n' => 5, 'z' => null])
        );
    },
    'data_to_utf8 leaves valid text alone and never leaves invalid bytes' => function () {
        $fixed = data_to_utf8(['ok' => 'café', 'nested' => [1, "x\xff"]]);
        t_equal('café', $fixed['ok']);
        t_equal(1, $fixed['nested'][0]);
        t_true(mb_check_encoding($fixed['nested'][1], 'UTF-8'), 'valid UTF-8');
    },

    // slugify()
    'slugify makes a url friendly slug' => function () {
        t_equal('hello-world', slugify('Hello World'));
        t_equal('cafe-au-lait', slugify('  Café au lait!  '));
        t_equal('unicode-n', slugify('Ünïcödé ñ'));
        t_equal('a-b-c', slugify('a_b c'));
        t_equal('o-neil-sons', slugify("O'Neil & Sons"));
    },
    'slugify gives n-a when nothing usable is left' => function () {
        t_equal('n-a', slugify(''));
        t_equal('n-a', slugify('---'));
        t_equal('n-a', slugify('日本語'));
    },

    // passwords
    'strong_password asks for upper, lower, a digit, a special character and the length' => function () {
        t_true(strong_password('Abcdef1!x'));
        t_true(!strong_password('abcdef1!x'), 'no capital');
        t_true(!strong_password('ABCDEF1!X'), 'no lower case');
        t_true(!strong_password('Abcdefg!x'), 'no digit');
        t_true(!strong_password('Abcdef12x'), 'no special character');
        t_true(!strong_password('Ab1!'), 'too short');
        t_true(strong_password('Ab1!', 4), 'the minimum can be lowered');
    },
    'a generated password has the length asked for' => function () {
        foreach ([1, 4, 9, 10, 12, 20, 40] as $length) {
            t_equal($length, strlen(generate_strong_password($length)), "length $length");
        }
        t_equal('', generate_strong_password(0));
    },
    'every generated password passes strong_password from four characters up' => function () {
        foreach ([4, 5, 9, 10, 12, 30] as $length) {
            for ($i = 0; $i < 400; $i++) {
                $password = generate_strong_password($length);
                t_true(strong_password($password, $length), "length $length: $password");
            }
        }
    },
    'a generated password avoids the look-alike characters' => function () {
        for ($i = 0; $i < 300; $i++) {
            t_equal(0, preg_match('/[ilIoLO01]/', generate_strong_password(30)));
        }
    },
    'a class can be left out and the others stay' => function () {
        for ($i = 0; $i < 200; $i++) {
            t_equal(0, preg_match('/[^A-Za-z0-9]/', generate_strong_password(10, ['special' => false])), 'no special');
            t_equal(0, preg_match('/[0-9]/', generate_strong_password(10, ['numeric' => false])), 'no digit');
            t_equal(0, preg_match('/[A-Z]/', generate_strong_password(10, ['uppercase' => false])), 'no capital');
        }
        t_true(preg_match('/[!@#$%&*?]/', generate_strong_password(10)) === 1, 'the special characters are there by default');
    },
    'two generated passwords differ' => function () {
        $seen = [];
        for ($i = 0; $i < 100; $i++) {
            $seen[generate_strong_password(12)] = true;
        }
        t_true(count($seen) > 95, 'not a pattern');
    },
    'the generator draws from a secure source, not shuffle() or rand()' => function () {
        $source = file_get_contents(dirname(__DIR__, 3) . '/siberian/lib/Siberian/Pure.php');
        preg_match('/function generate_strong_password\(.*?\n}\n/s', $source, $m);
        $code = preg_replace('~/\*.*?\*/|//[^\n]*~s', '', $m[0]);
        t_true(strpos($code, 'random_int') !== false, 'random_int');
        t_equal(0, preg_match('/\b(shuffle|rand|mt_rand|usleep)\(/', $code), 'no weak randomness');
    },
    'encrypt_password is the legacy sha1 that stored passwords were made with' => function () {
        t_equal(sha1('secret1'), encrypt_password('secret1'));
    },

    // Currency
    'the currency table lists the codes in order and knows the usual ones' => function () {
        $all = Currency::getAllCurrencies();
        $codes = array_keys($all);
        $sorted = $codes;
        sort($sorted);
        t_equal($sorted, $codes, 'ksorted');
        foreach (['USD', 'EUR', 'GBP', 'JPY', 'CAD'] as $code) {
            t_true(isset($all[$code]), $code);
        }
    },
    'a currency carries its symbol and decimals' => function () {
        t_equal('$', Currency::getCurrency('USD')['symbol']);
        t_equal(2, Currency::getCurrency('EUR')['decimal_digits']);
        t_equal(0, Currency::getCurrency('JPY')['decimal_digits']);
        foreach (Currency::getAllCurrencies() as $code => $currency) {
            t_equal($code, $currency['code'], "$code matches its own record");
            t_true(is_int($currency['decimal_digits']), "$code has decimals");
        }
    },
    'an unknown currency is refused with its code in the message' => function () {
        try {
            Currency::getCurrency('XXX');
            t_true(false, 'it should throw');
        } catch (Siberian\Exception $e) {
            t_true(strpos($e->getMessage(), 'XXX') !== false, $e->getMessage());
        }
    },
    'vat is a rate of the price and addVat adds it' => function () {
        t_equal(20.0, (float) Currency::getVat(100, 20));
        t_equal(0.0, (float) Currency::getVat(100, 0));
        t_equal(120.0, (float) Currency::addVat(100, 20));
        t_equal(100.0, (float) Currency::addVat(100, 0));
        t_equal(0.0, (float) Currency::getVat(0, 20));
        foreach ([[19.99, 20], [5, 5.5], [1234.56, 19.6]] as [$price, $rate]) {
            t_true(abs(Currency::addVat($price, $rate) - ($price + Currency::getVat($price, $rate))) < 1e-9, "$price at $rate");
        }
    },

    // Date
    'the date constants are seconds' => function () {
        t_equal(3600, Date::HOUR_SECONDS);
        t_equal(86400, Date::DAY_SECONDS);
        t_equal(7 * 86400, Date::WEEK_SECONDS);
    },
    'Date::now is now' => function () {
        t_true(abs(Date::now()->getTimestamp() - time()) <= 2);
        t_true(Date::now() instanceof Date);
    },
    'the beginning and the end of a day' => function () {
        $date = new Date(strtotime('2024-03-15 13:45:10'), Date::TIMESTAMP);
        t_equal('2024-03-15 00:00:00', $date->setBeginningOfTheDay()->toString('y-MM-dd HH:mm:ss'));

        $date = new Date(strtotime('2024-03-15 13:45:10'), Date::TIMESTAMP);
        t_equal('2024-03-15 23:59:59', $date->setEndOfTheDay()->toString('y-MM-dd HH:mm:ss'));
    },
    'Date::format formats a database date' => function () {
        t_equal('15/03/2024', Date::format('2024-03-15 10:11:12', 'dd/MM/y'));
        t_equal('2024-03-15', Date::format('2024-03-15 10:11:12'));
        t_equal('10:11', Date::format('2024-03-15 10:11:12', 'HH:mm'));
    },
    'time_to_date formats a timestamp' => function () {
        t_equal('2024-03-15', time_to_date(strtotime('2024-03-15 10:11:12')));
        t_equal('15.03.2024', time_to_date(strtotime('2024-03-15 10:11:12'), 'dd.MM.y'));
    },
];
