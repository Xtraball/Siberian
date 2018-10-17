<?php

namespace rock\sanitize\rules;


class RemoveScript extends Rule
{
    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        return is_string($input) ? preg_replace(
            [
                '/\<[\\s\/]*
                (?:applet|b(?:ase|gsound|link)|
                embed|frame(?:set)?|
                i(?:frame|layer)|
                l(?:ayer|ink)|
                meta|s(?:cript|tyle)|title|xml)
                [^\>]*+\>/iusx',
                /* XSS injection IE */
                '/(\<[^\>]+?.*?)
                (?:expression|behaviour|javascript|s\\s*c\\s*r\\s*i\\s*p\\s*t\\s*)\\s*\(*(?:[^\(\)]++|\((?!\()|\)(?!\))|(?R))*\)*	# cut
                (.*?\>)/iux'
            ],
            ["", '$1$2'],
            $input
        ) : $input;
    }
} 