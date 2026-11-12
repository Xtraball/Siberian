<?php
/**
 * The committed lib/vendor tree is the biggest upgrade risk: it is frozen at
 * old majors (symfony 3, stripe 7, phpseclib 2, ...). These tests prove each
 * library still loads and does its core job on the PHP under test.
 */
return [
    'HTMLPurifier strips scripts' => function () {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null); // never write into the vendor tree
        $purifier = new HTMLPurifier($config);
        $out = $purifier->purify('<p onclick="x()">hi</p><script>alert(1)</script>');
        t_equal('<p>hi</p>', $out, 'purified');
    },

    'Transliterator' => function () {
        t_equal('Elephant cafe', \Behat\Transliterator\Transliterator::unaccent('Éléphant café'), 'unaccent');
        t_equal('hello-world', \Behat\Transliterator\Transliterator::transliterate('Hello, World!'), 'slug');
    },

    'Stripe client classes load' => function () {
        t_true(class_exists('Stripe\\Stripe'), 'Stripe');
        t_true(class_exists('Stripe\\Customer'), 'Customer');
    },

    'gettext Translations' => function () {
        $t = new Gettext\Translations();
        $t->insert('', 'Hello')->setTranslation('Bonjour');
        t_equal('Bonjour', $t->find('', 'Hello')->getTranslation(), 'translation');
        t_true(strlen($t->toPoString()) > 0, 'po export');
    },

    'Minify CSS and JS' => function () {
        t_equal('a{color:red}', (new MatthiasMullie\Minify\CSS('a { color : red ; }'))->minify(), 'css');
        t_true(strlen((new MatthiasMullie\Minify\JS('var  a = 1 ;  // c'))->minify()) < 18, 'js');
    },

    'Symfony DomCrawler (goutte dependency)' => function () {
        $c = new Symfony\Component\DomCrawler\Crawler('<html><body><p class="x">hi</p></body></html>');
        t_equal('hi', $c->filter('p.x')->text(), 'text');
    },

    'Endroid QrCode renders PNG' => function () {
        $qr = new Endroid\QrCode\QrCode('https://example.com');
        t_equal("\x89PNG", substr($qr->writeString(), 0, 4), 'png header');
    },

    'Debril feed-io classes load' => function () {
        t_true(class_exists('FeedIo\\FeedIo'), 'FeedIo');
    },
];
