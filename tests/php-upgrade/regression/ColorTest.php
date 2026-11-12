<?php
/**
 * Siberian\Color (ariColor) reads the colors an admin types or picks: the Front controllers build the progress bar,
 * the background and the status bar from them, always asking for "hex" whatever is stored. A value written as
 * rgb(), rgba() or hsl() went through hexdec() and came out as a wrong color with a deprecation; rgb() was not
 * limited to 0-255 (a hex of 22 characters); a hue of 360 gave grey instead of red (getNew('hue') clamps to 360);
 * the word colors were case sensitive; and an HSL or fallback color had no brightness.
 */
require_once dirname(__DIR__, 3) . '/siberian/lib/Siberian/Color.php';

use Siberian\Color;

/** @return array [red, green, blue] of a color */
function t_rgb(Color $c)
{
    return [(int) $c->red, (int) $c->green, (int) $c->blue];
}

return [
    'a six digit hex gives its channels, hsl, luminance and brightness' => function () {
        $c = Color::newColor('#336699');
        t_equal([51, 102, 153], t_rgb($c));
        t_equal([210, 50, 40], [(int) $c->hue, (int) $c->saturation, (int) $c->lightness]);
        t_equal(1, $c->alpha);
        t_equal(95, (int) $c->luminance);
        t_equal(92, $c->brightness['total']);
        t_equal('hex', $c->mode);
    },
    'a three digit hex is expanded, with or without the #' => function () {
        t_equal([51, 102, 153], t_rgb(Color::newColor('#369')));
        t_equal([51, 102, 153], t_rgb(Color::newColor('#369', 'hex')));
        t_equal([255, 136, 0], t_rgb(Color::newColor('#FF8800')), 'capitals');
    },
    'a hex longer than seven characters keeps its first six digits' => function () {
        t_equal([51, 102, 153], t_rgb(Color::newColor('#336699ff')));
    },
    'word colors are found whatever their case' => function () {
        t_equal([255, 165, 0], t_rgb(Color::newColor('orange')));
        t_equal([255, 0, 0], t_rgb(Color::newColor('RED')));
        t_equal([255, 0, 0], t_rgb(Color::newColor('Red', 'hex')));
        t_equal([255, 0, 0], t_rgb(Color::newColor(' red ')));
    },
    'something that is not a color gives the fallback, white' => function () {
        foreach (['', 'zzz', '#12', '#gggggg', 'transparent', 'not a color'] as $bad) {
            t_equal([255, 255, 255], t_rgb(Color::newColor($bad)), var_export($bad, true));
            t_equal([255, 255, 255], t_rgb(Color::newColor($bad, 'hex')), 'hex mode: ' . var_export($bad, true));
        }
        t_equal([255, 255, 255], t_rgb(Color::newColor(null, 'hex')));
    },
    'the fallback can be given, and carries a brightness' => function () {
        $c = Color::newColor(['fallback' => '#123456']);
        t_equal([18, 52, 86], t_rgb($c));
        t_true(isset($c->brightness['total']), 'brightness');
        t_true(isset(Color::newColor('zzz')->brightness['total']), 'brightness of the default fallback');
    },
    'rgb and rgba are read and limited to 0-255' => function () {
        t_equal([10, 20, 30], t_rgb(Color::newColor('rgb(10, 20, 30)')));
        t_equal([255, 0, 0], t_rgb(Color::newColor('rgb(300,-5,0)')), 'rgb is limited too');
        t_equal('#ff0000', Color::newColor('rgb(300,-5,0)')->toCSS('hex'));
        t_equal([10, 20, 30], t_rgb(Color::newColor('rgba(10,20,30,0.5)')));
        t_equal([255, 0, 0], t_rgb(Color::newColor('rgba(999,-1,0,1)')));
        t_equal([1, 2, 255], t_rgb(Color::newColor('rgb(1,2)')), 'a missing channel is 255');
    },
    'the alpha is limited to 0-1' => function () {
        t_equal(0.5, (float) Color::newColor('rgba(10,20,30,0.5)')->alpha);
        t_equal(1, (int) Color::newColor('rgba(10,20,30,5)')->alpha);
        t_equal(0, (int) Color::newColor('rgba(10,20,30,-1)')->alpha);
    },
    'hsl and hsla are converted' => function () {
        t_equal([64, 191, 64], t_rgb(Color::newColor('hsl(120, 50%, 50%)')));
        t_equal([0, 0, 128], t_rgb(Color::newColor('hsla(240,100%,25%,0.3)')));
        t_equal([255, 255, 255], t_rgb(Color::newColor('hsl(0,0%,100%)')));
        t_equal(0.3, (float) Color::newColor('hsla(240,100%,25%,0.3)')->alpha);
    },
    'a hue of 360 is red like 0, not grey' => function () {
        t_equal([255, 0, 0], t_rgb(Color::newColor('hsl(360,100%,50%)')));
        t_equal([255, 0, 0], t_rgb(Color::newColor('hsl(0,100%,50%)')));
        t_equal([255, 0, 0], t_rgb(Color::newColor('hsl(720,100%,50%)')), 'a turn more');
    },
    'an incomplete hsl does not raise an undefined key' => function () {
        t_equal([0, 0, 0], t_rgb(Color::newColor('hsl(120)')));
    },
    'the front asks for hex whatever is stored: another notation is read as such' => function () {
        $c = Color::newColor('rgba(10,20,30,0.5)', 'hex');
        t_equal([10, 20, 30], t_rgb($c));
        t_equal('rgba', $c->mode);
        t_equal([10, 20, 30], t_rgb(Color::newColor('rgb(10,20,30)', 'hex')));
        t_equal([64, 191, 64], t_rgb(Color::newColor('hsl(120,50%,50%)', 'hex')));
    },
    'an HSL color has a brightness, so it can be brightened' => function () {
        $c = Color::newColor('hsl(120,50%,50%)');
        t_true(isset($c->brightness['total']), 'brightness');
        t_true($c->getNew('brightness', 250) instanceof Color);
    },
    'array notations' => function () {
        t_equal([10, 20, 30], t_rgb(Color::newColor([10, 20, 30])));
        t_equal([10, 20, 30], t_rgb(Color::newColor([10, 20, 30, 1])));
        t_equal([1, 2, 3], t_rgb(Color::newColor(['r' => 1, 'g' => 2, 'b' => 3])));
        t_equal([171, 205, 239], t_rgb(Color::newColor(['color' => '#abcdef'])));
        t_equal([1, 2, 3], t_rgb(Color::newColor(['rgba' => 'rgba(1,2,3,0.5)'])));
        t_equal([255, 255, 255], t_rgb(Color::newColor(['x' => 1])), 'nothing usable: the fallback');
    },
    'toCSS writes every notation, in lower case for hex' => function () {
        $c = Color::newColor('#FF8800');
        t_equal('#ff8800', $c->toCSS('hex'));
        t_equal('#ff8800', $c->toCSS());
        t_equal('rgb(255,136,0)', $c->toCSS('rgb'));
        t_equal('rgba(255,136,0,1)', $c->toCSS('rgba'));
        t_equal('hsl(32,100%,50%)', $c->toCSS('hsl'));
        t_equal('hsla(32,100%,50%,1)', $c->toCSS('hsla'));
        t_equal('', $c->toCSS('nope'));
        t_equal($c->toCSS('rgb'), $c->to_css('rgb'), 'the alias');
    },
    'getNew changes one channel and leaves the original alone' => function () {
        $c = Color::newColor('#336699');
        t_equal([255, 102, 153], t_rgb($c->getNew('red', 255)));
        t_equal([51, 0, 153], t_rgb($c->getNew('green', -10)), 'limited at 0');
        t_equal([51, 102, 255], t_rgb($c->getNew('blue', 300)), 'limited at 255');
        t_equal(0.5, (float) $c->getNew('alpha', 0.5)->alpha);
        t_equal([51, 102, 153], t_rgb($c), 'the original is unchanged');
    },
    'getNew changes hue, saturation and lightness' => function () {
        $c = Color::newColor('#336699');
        t_equal('#993333', $c->getNew('hue', 0)->toCSS('hex'));
        t_equal('#993333', $c->getNew('hue', 400)->toCSS('hex'), 'limited at 360, which is red again');
        t_equal('#666666', $c->getNew('saturation', 0)->toCSS('hex'));
        t_equal('#d9e6f2', $c->getNew('lightness', 90)->toCSS('hex'));
    },
    'getNew brightness moves every channel by the same amount' => function () {
        $c = Color::newColor('#336699');
        t_equal('#9fd2ff', $c->getNew('brightness', 200)->toCSS('hex'));
        t_equal('#3b6ea1', $c->getNew('brightness', 100)->toCSS('hex'));
        t_true($c->getNew('brightness', $c->brightness['total']) === $c, 'same brightness: the same color');
    },
    'getNew of an unknown property is null' => function () {
        t_equal(null, Color::newColor('#336699')->getNew('nonsense', 1));
        t_equal(null, Color::newColor('#336699')->get_new('nonsense', 1));
    },
    'the same color is one instance' => function () {
        t_true(Color::newColor('#336699') === Color::newColor('#336699'));
        t_true(Color::newColor('#336699') === Color::new_color('#336699'));
        t_true(Color::newColor('#336699') !== Color::newColor('#336698'));
    },
    'the progress bar maths of the front works on every stored notation' => function () {
        // MobileController: trail = lightness of the trail color moved by the text color's lightness +/- 20
        foreach (['#336699', 'rgba(10,20,30,0.5)', 'hsl(210,50%,40%)', 'Blue', 'garbage', null] as $stored) {
            $text = Color::newColor($stored, 'hex');
            $trail = Color::newColor('#ffffff', 'hex');
            $new = $trail->lightness > 80
                ? $trail->getNew('lightness', $text->lightness - 20)
                : $trail->getNew('lightness', $text->lightness + 20);
            t_true($new instanceof Color, var_export($stored, true));
            t_true(preg_match('/^#[0-9a-f]{6}$/', $new->toCSS('hex')) === 1, var_export($stored, true) . ' gives ' . $new->toCSS('hex'));
        }
    },
];
