<?php

namespace rock\sanitize;


use rock\base\ObjectInterface;
use rock\base\ObjectTrait;
use rock\helpers\Instance;
use rock\sanitize\rules\Abs;
use rock\sanitize\rules\BasicTags;
use rock\sanitize\rules\BooleanRule;
use rock\sanitize\rules\Call;
use rock\sanitize\rules\Decode;
use rock\sanitize\rules\DefaultRule;
use rock\sanitize\rules\Email;
use rock\sanitize\rules\Encode;
use rock\sanitize\rules\FloatRule;
use rock\sanitize\rules\IntRule;
use rock\sanitize\rules\Lowercase;
use rock\sanitize\rules\LowerFirst;
use rock\sanitize\rules\LtrimWords;
use rock\sanitize\rules\Negative;
use rock\sanitize\rules\NoiseWords;
use rock\sanitize\rules\Numbers;
use rock\sanitize\rules\Positive;
use rock\sanitize\rules\Round;
use rock\sanitize\rules\SpecialChars;
use rock\sanitize\rules\ReplaceRandChars;
use rock\sanitize\rules\RtrimWords;
use rock\sanitize\rules\Rule;
use rock\sanitize\rules\RemoveScript;
use rock\sanitize\rules\RemoveTags;
use rock\sanitize\rules\StringRule;
use rock\sanitize\rules\ToType;
use rock\sanitize\rules\Slug;
use rock\sanitize\rules\Trim;
use rock\sanitize\rules\Truncate;
use rock\sanitize\rules\TruncateWords;
use rock\sanitize\rules\Unserialize;
use rock\sanitize\rules\Uppercase;
use rock\sanitize\rules\UpperFirst;

/**
 * Sanitize
 *
 * @method static Sanitize attributes($attributes)
 * @method static Sanitize recursive(bool $recursive = true)
 * @method static Sanitize rules(array $rules)
 *
 * @method static Sanitize abs()
 * @method static Sanitize basicTags(StringRule $allowedTags = '')
 * @method static Sanitize bool()
 * @method static Sanitize call(callable $call, array $args = null)
 * @method static Sanitize decode()
 * @method static Sanitize defaultValue(mixed $default = null)
 * @method static Sanitize email()
 * @method static Sanitize encode(bool $doubleEncode = true)
 * @method static Sanitize float()
 * @method static Sanitize int()
 * @method static Sanitize lowercase()
 * @method static Sanitize lowerFirst()
 * @method static Sanitize ltrimWords(array $words)
 * @method static Sanitize negative()
 * @method static Sanitize noiseWords(StringRule $enNoiseWords = '')
 * @method static Sanitize numbers()
 * @method static Sanitize positive()
 * @method static Sanitize specialChars()
 * @method static Sanitize removeScript()
 * @method static Sanitize removeTags()
 * @method static Sanitize replaceRandChars(StringRule $replaceTo = '*')
 * @method static Sanitize round(IntRule $precision = 0)
 * @method static Sanitize rtrimWords(array $words)
 * @method static Sanitize string()
 * @method static Sanitize toType()
 * @method static Sanitize slug(StringRule $replacement = '-', bool $lowercase = true)
 * @method static Sanitize trim()
 * @method static Sanitize truncate(IntRule $length = 4, StringRule $suffix = '...')
 * @method static Sanitize truncateWords(IntRule $length = 100, StringRule $suffix = '...')
 * @method static Sanitize unserialize()
 * @method static Sanitize uppercase()
 * @method static Sanitize upperFirst()
 *
 * @package rock\sanitize
 */
class Sanitize implements ObjectInterface
{
    use ObjectTrait {
        ObjectTrait::__call as parentCall;
    }

    /**
     * List rules.
     * @var array
     */
    protected $rules = [];
    /**
     * Enable recursive mode.
     * @var bool
     */
    protected $recursive = true;
    /**
     * Label remainder.
     * @var string
     */
    protected $remainder = '*';
    /**
     * List raw rules.
     * @var Rule[]
     */
    protected $rawRules = [];

    public function init()
    {
        $this->rules = array_merge($this->defaultRules(), $this->rules);
    }

    /**
     * Sets a list rules.
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules)
    {
        $this->rules = array_merge($this->rules, $rules);
        return $this;
    }

    /**
     * Enable recursive mode.
     * @param bool $enable
     * @return $this
     */
    public function setRecursive($enable)
    {
        $this->recursive = $enable;
        return $this;
    }

    /**
     * Sets a label remainder.
     * @param string $label
     * @return $this
     */
    public function setRemainder($label)
    {
        $this->remainder = $label;
        return $this;
    }

    /**
     * Sanitize value.
     *
     * @param mixed $input
     * @return mixed
     * @throws SanitizeException
     */
    public function sanitize($input)
    {
        foreach ($this->rawRules as $rule) {
            if ($rule instanceof Attributes) {
                $config = [
                    'remainder' => $this->remainder,
                    'recursive' => $this->recursive
                ];
                Instance::configure($rule, $config);
                return $rule->sanitize($input);
            }
            $input = $rule->sanitize($input);

            if ($rule->recursive && (is_array($input) || is_object($input))) {
                $config['attributes'] = $this;
                return (new Attributes($config))->sanitize($input);
            }
        }

        return $input;
    }

    /**
     * Exists rule.
     * @param string $name name of rule.
     * @return bool
     */
    public function existsRule($name)
    {
        return isset($this->rules[$name]);
    }

    /**
     * @return rules\Rule[]
     */
    public function getRawRules()
    {
        return $this->rawRules;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this, "{$name}Internal")) {
            return call_user_func_array([$this, "{$name}Internal"], $arguments);
        }

        if (!isset($this->rules[$name])) {
            throw new SanitizeException("Unknown rule: {$name}");
        }
        if (!class_exists($this->rules[$name])) {
            throw new SanitizeException(SanitizeException::UNKNOWN_CLASS, ['class' => $this->rules[$name]]);
        }
        $rule = $this->getInstanceRule($name, $arguments);
        $this->rawRules[] = $rule;
        return $this;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::getInstance(), $name], $arguments);
    }

    protected function attributesInternal($attributes)
    {
        $this->rawRules = [];
        $this->rawRules[] = new Attributes(['attributes' => $attributes, 'remainder' => $this->remainder]);

        return $this;
    }

    protected function recursiveInternal($enable = true)
    {
        $this->recursive = $enable;
        return $this;
    }

    protected function rulesInternal(array $rules)
    {
        foreach ($rules as $rule => $args) {
            if (is_int($rule)) {
                $rule = $args;
                $args = [];
            }
            if (is_array($rule)) {
                $args = (array)current($rule);
                $rule = key($rule);
            }
            call_user_func_array([$this, $rule], $args);
        }
        return $this;
    }

    /**
     * Returns instance rule.
     * @param string $name name of rule
     * @param array $arguments
     * @return Rule
     */
    protected function getInstanceRule($name, array $arguments)
    {
        $reflect = new \ReflectionClass($this->rules[$name]);
        return $reflect->newInstanceArgs($arguments);
    }

    /**
     * Returns instance.
     *
     * If exists {@see \rock\di\Container} that uses it.
     * @return static
     */
    protected static function getInstance()
    {
        if (class_exists('\rock\di\Container')) {
            return \rock\di\Container::load(static::className());
        }
        return new static();
    }

    protected function defaultRules()
    {
        return [
            'abs' => Abs::className(),
            'basicTags' => BasicTags::className(),
            'bool' => BooleanRule::className(),
            'call' => Call::className(),
            'decode' => Decode::className(),
            'defaultValue' => DefaultRule::className(),
            'email' => Email::className(),
            'encode' => Encode::className(),
            'float' => FloatRule::className(),
            'int' => IntRule::className(),
            'lowercase' => Lowercase::className(),
            'lowerFirst' => LowerFirst::className(),
            'ltrimWords' => LtrimWords::className(),
            'negative' => Negative::className(),
            'noiseWords' => NoiseWords::className(),
            'numbers' => Numbers::className(),
            'positive' => Positive::className(),
            'specialChars' => SpecialChars::className(),
            'removeScript' => RemoveScript::className(),
            'removeTags' => RemoveTags::className(),
            'replaceRandChars' => ReplaceRandChars::className(),
            'round' => Round::className(),
            'rtrimWords' => RtrimWords::className(),
            'string' => StringRule::className(),
            'toType' => ToType::className(),
            'slug' => Slug::className(),
            'trim' => Trim::className(),
            'truncate' => Truncate::className(),
            'truncateWords' => TruncateWords::className(),
            'unserialize' => Unserialize::className(),
            'uppercase' => Uppercase::className(),
            'upperFirst' => UpperFirst::className(),
        ];
    }
}