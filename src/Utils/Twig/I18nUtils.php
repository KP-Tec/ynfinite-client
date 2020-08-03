<?php

namespace App\Utils\Twig;

use App\Utils\I18n\I18n;

class I18nUtils {
    public function __construct($twig, $data)
    {
      $this->data = $data;
      $this->twig = $twig;

      $this->i18n = new I18n(__DIR__.'/../../locale/lang_{LANGUAGE}.ini', __DIR__.'/../../../tmp/locales/', "en");
      $this->i18n->setForcedLang($this->data["languages"]["current"]);

      $this->i18n->init();

    }

    public function translate($string, $args = array()) {

        return L($string, $args);
    }
}