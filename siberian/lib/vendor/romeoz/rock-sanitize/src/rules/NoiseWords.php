<?php

namespace rock\sanitize\rules;


class NoiseWords extends Rule
{
    protected $enNoiseWords = 'about,after,all,also,an,and,another,any,are,as,at,be,because,been,before,
				  				  	 being,between,both,but,by,came,can,come,could,did,do,each,for,from,get,
				  				  	 got,has,had,he,have,her,here,him,himself,his,how,if,in,into,is,it,its,it\'s,like,
			      				  	 make,many,me,might,more,most,much,must,my,never,now,of,on,only,or,other,
				  				  	 our,out,over,said,same,see,should,since,some,still,such,take,than,that,
				  				  	 the,their,them,then,there,these,they,this,those,through,to,too,under,up,
				  				  	 very,was,way,we,well,were,what,where,which,while,who,with,would,you,your,a,
				  				  	 b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,$,1,2,3,4,5,6,7,8,9,0,_';

    public function __construct($enNoiseWords = null, $config = [])
    {
        $this->parentConstruct($config);
        if (!empty($enNoiseWords)) {
            $this->enNoiseWords = $enNoiseWords;
        }
    }

    /**
     * @inheritdoc
     */
    public function sanitize($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        $value = preg_replace('/\s\s+/u', chr(32), $input);
        $value = " $value ";
        $words = explode(',', $this->enNoiseWords);
        foreach ($words as $word) {
            $word = trim($word);
            $word = " $word "; // Normalize
            if (stripos($value, $word) !== FALSE) {
                $value = str_ireplace($word, chr(32), $value);
            }
        }
        return trim($value);
    }
}