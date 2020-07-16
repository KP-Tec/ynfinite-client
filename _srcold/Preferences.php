<?php

namespace Ypsolution\YnfinitePhpClient;
/**
 * This class contains the preferences for the application.
 *
 * @package App
 */
class Preferences
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * @var string
     */
    private $config;


    /**
     * Preferences constructor.
     *
     * @param string $rootPath
     */
    public function __construct(string $rootPath, $config)
    {
        $this->rootPath = $rootPath;
        $this->config = $config;
    }
    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getConfig() {
        return $this->config;
    }
}