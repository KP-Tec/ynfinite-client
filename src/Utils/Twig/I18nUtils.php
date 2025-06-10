<?php

namespace App\Utils\Twig;

use App\Utils\I18n\I18n;
use Exception;

/**
 * Extended I18n class that automatically merges custom translations
 */
class CustomMergeI18n extends I18n {
    protected $customTranslationsPath = null;

    public function setCustomTranslationsPath($path) {
        $this->customTranslationsPath = $path;
    }

    protected function load($filename) {
        $config = parent::load($filename);
        
        // If custom translations path is set, merge them
        if ($this->customTranslationsPath) {
            $customFile = str_replace('{LANGUAGE}', $this->appliedLang, $this->customTranslationsPath);
            if (file_exists($customFile)) {
                $customConfig = parent::load($customFile);
                if ($customConfig !== false) {
                    // Merge custom translations, overwriting main translations where keys exist
                    $config = array_replace_recursive($config, $customConfig);
                }
            }
        }
        
        return $config;
    }

    protected function getConfigFilename($langcode) {
        return str_replace('{LANGUAGE}', $langcode, $this->filePath);
    }
    
    /**
     * Override cache checking to include custom translations files
     */
    /**
     * Override init to use our custom cache checking
     */
    public function init() {
        if ($this->isInitialized()) {
            throw new \BadMethodCallException('This object from class ' . __CLASS__ . ' is already initialized. It is not possible to init one object twice!');
        }

        $this->isInitialized = true;

        $this->userLangs = $this->getUserLangs();

        // search for language file
        $this->appliedLang = NULL;
        foreach ($this->userLangs as $priority => $langcode) {
            $this->langFilePath = $this->getConfigFilename($langcode);
            if (file_exists($this->langFilePath)) {
                $this->appliedLang = $langcode;
                break;
            }
        }
        if ($this->appliedLang == NULL) {
            throw new \RuntimeException('No language file was found.');
        }

        // search for cache file
        $this->cacheFilePath = $this->cachePath . '/php_i18n_' . md5_file(__FILE__) . '_' . $this->prefix . '_' . $this->appliedLang . '.cache.php';

        // whether we need to create a new cache file using our custom method
        if ($this->isCacheOutdated()) {
            $config = $this->load($this->langFilePath);
            if ($this->mergeFallback)
                $config = array_replace_recursive($this->load($this->getConfigFilename($this->fallbackLang)), $config);

            $compiled = "<?php class " . $this->prefix . " {\n"
                . $this->compile($config)
                . 'public static function __callStatic($string, $args) {' . "\n"
                . '    return vsprintf(constant("self::" . $string), $args);'
                . "\n}\n}\n"
                . "function ".$this->prefix .'($string, $args=NULL) {'."\n"
                . '    $return = constant("'.$this->prefix.'::".$string);'."\n"
                . '    return $args ? vsprintf($return,$args) : $return;'
                . "\n}";

            if( ! is_dir($this->cachePath))
                mkdir($this->cachePath, 0755, true);

            if (file_put_contents($this->cacheFilePath, $compiled) === FALSE) {
                throw new \Exception("Could not write cache file to path '" . $this->cacheFilePath . "'. Is it writable?");
            }
            chmod($this->cacheFilePath, 0755);
        }

        require_once $this->cacheFilePath;
    }
    
    protected function isCacheOutdated() {
        $mainFile = $this->langFilePath;
        $cacheOutdated = !file_exists($this->cacheFilePath) ||
            filemtime($this->cacheFilePath) < filemtime($mainFile);
            
        // Check custom translations file
        if (!$cacheOutdated && $this->customTranslationsPath) {
            $customFile = str_replace('{LANGUAGE}', $this->appliedLang, $this->customTranslationsPath);
            if (file_exists($customFile)) {
                $cacheOutdated = filemtime($this->cacheFilePath) < filemtime($customFile);
            }
        }
        
        // Check fallback file if merge fallback is enabled
        if (!$cacheOutdated && $this->mergeFallback) {
            $fallbackFile = $this->getConfigFilename($this->fallbackLang);
            if (file_exists($fallbackFile)) {
                $cacheOutdated = filemtime($this->cacheFilePath) < filemtime($fallbackFile);
            }
        }
        
        return $cacheOutdated;
    }
}

class I18nUtils {

    public $data;
    public $twig;

    public $i18n;

    public function __construct($twig, $data)
    {
      $this->data = $data;
      $this->twig = $twig;

      // Initialize I18n with main locale files
      $this->i18n = new CustomMergeI18n(__DIR__ . '/../../locale/lang_{LANGUAGE}.ini', __DIR__.'/../../../tmp/locales/', "de");
      $this->i18n->setCustomTranslationsPath(__DIR__ . '/../../../plugins/custom_translations/lang_{LANGUAGE}.ini');
      $this->i18n->setForcedLang($this->data["languages"]["current"]);
      $this->i18n->setMergeFallback(true);

      $this->i18n->init();

    }

    public function translate($string, $args = array()) {
        try {
            // Check if the translation constant exists in the L class
            if (defined('L::' . $string)) {
                return L($string, $args);
            } else {
                // If translation doesn't exist, return the variable name as string
                return $string;
            }
        } catch (Exception $e) {
            // Fallback to variable name if any error occurs
            return $string;
        }
    }
}