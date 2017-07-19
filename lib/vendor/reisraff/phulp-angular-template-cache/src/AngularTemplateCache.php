<?php

namespace Phulp\AngularTemplateCache;

use Phulp\PipeInterface;
use Phulp\Source;
use Phulp\DistFile;

class AngularTemplateCache implements PipeInterface
{
    private $filename;
    private $options = [
        'module' => null,
    ];

    public function __construct($filename, array $options)
    {
        $this->filename = $filename;
        $this->options = array_merge($this->options, $options);
    }

    public function execute(Source $src)
    {
        $templateHeader = 'angular.module("<%= module %>"<%= standalone %>).run(["$templateCache", function($templateCache) {';
        $templateBody = '$templateCache.put("<%= url %>",<%= contents %>);';
        $templateFooter = '}]);';

        $puts = [];
        foreach ($src->getDistFiles() as $key => $file) {
            $root = rtrim($this->options['root'], '/') . DIRECTORY_SEPARATOR;
            $url = $root . $file->getRelativepath() . '/' . $file->getName();
            $content = json_encode($file->getContent(), JSON_UNESCAPED_SLASHES);

            $puts[] = preg_replace(
                ['/<%= url %>/', '/<%= contents %>/'],
                [$url, $content],
                $templateBody
            );

            $src->removeDistFile($key);
        }

        $content = sprintf(
            '%s%s%s',
            preg_replace(['/<%= module %>/', '/<%= standalone %>/'], [$this->options['module'], null], $templateHeader),
            implode(PHP_EOL, $puts),
            $templateFooter
        );

        $src->addDistFile(new DistFile($content, $this->filename));
    }
}
