/* hand written edge cases for the minifier, not an app file */
(function (root, factory) {
    // a block comment with a "string" and a /regex/ in it
    var a = 10, b = 2, c = a / b / 5; // division, not a regex
    var re = /ab+c\/d[/]e/gi, re2 = /["']/g;
    var s1 = 'it\'s', s2 = "say \"hi\"", s3 = `tpl ${a + b} and ${`nested ${c}`}`;
    var url = "http://example.com/a//b"; // the // in the string is not a comment
    if (a++ + ++b > 3) { return a - -b; }
    var o = { delete: 1, new: 2, class: 3, 'quoted-key': 4 };
    o.delete = o.new + o["class"];
    var t = a ? /x/.test(s1) : /y/.test(s2);
    var neg = !!a, und = undefined, tr = true, fl = false;
    for (var i = 0; i < 3; i++) { if (i === 1) continue; else break }
    var f = function () { return /re/.test("re") }
    var g = () => ({ x: 1 })
    switch (a) { case 1: b = 2; break; default: b = 3 }
    var x = a
    ++b
    root.lib = factory(re, re2, s3, url, o, t, neg, und, tr, fl, f, g, x);
}(this, function (re) { return re; }));
/*! preserved important comment */
