<?php

namespace Ypsolution\YnfinitePhpClient\utils;


class TwigUtils
{

  private $data;

  public function __construct($twig, $data, $templates)
  {
    $this->data = $data;
    $this->twig = $twig;
    $this->templates = $templates;
  }

  private function getSizes($image, $sizes) {
    $srcset = array();
    $src = '';

    forEach($sizes as $size) {

      $path = $image["path"];

      $attrArray = array();
      if ($size["w"]) {
        $attrArray[] = ("w=".$size['w']);
      }
      if ($size["h"]) {
        $attrArray[] = ("h=".$size['h']);
      }
      $path .= "?".implode("&", $attrArray);

      if ($size["screenSize"]) {
        $srcset[] = $path." ".$size["screenSize"]."w";
      } else {
        $src = $path;
      }
    }

    if (!$src) {
      $src = $image["path"];;
    }

    return array("src" => $src, "srcset" => implode(",", $srcset));
  }

  public function printImage($image, $sizes = array()) {
    $sources = $this->getSizes($image, $sizes);
    return $this->twig->render("yn/components/image.twig", array("image" => $image, "src" => $sources["src"], "srcset" => $sources["srcset"]));
  }

  public function printFigure($image, $sizes = array()) {
    $sources = $this->getSizes($image, $sizes);
    return $this->twig->render("yn/components/figure.twig", array("image" => $image, "src" => $sources["src"], "srcset" => $sources["srcset"]));
  }

  public function form($form, $section = array()) {
    return $this->twig->render("yn/components/form.twig", array("form" => $form, "section" => $section, "templates" => $this->templates));
  }

  public function formField($formField) {
    switch($formField["type"]) {
      case "select": {
        return $this->twig->render("yn/components/form/select.twig", array("field"=> $formField));
      }
      default: {
        return $this->twig->render("yn/components/form/text.twig", array("field"=> $formField));
      }
    }
  }

  public function consents($form) {
    return $this->twig->render("yn/components/form/consents.twig", array("form" => $form));
  }


  public function linkPage($pageSlug, $slug = '')
  {
    $route = $this->data["routes"][$pageSlug];
    return str_replace('{{alias}}', $slug, $route);
  }

  public function sectionByAlias($alias) {
    $sections = $this->data["sections"];
    foreach($sections as $key => $section)
    {
       if ( $section["alias"] === $alias )
          return $section;
    }
    return false;
  }
}