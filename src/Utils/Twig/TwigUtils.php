<?php

namespace App\Utils\Twig;


class TwigUtils
{

  private $data;

  public function __construct($twig, $data, $templates, $uriData)
  {
    $this->data = $data;
    $this->twig = $twig;
    $this->templates = $templates;
    $this->uriData = $uriData;
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
      case "checkbox": {
        return $this->twig->render("yn/components/form/checkbox.twig", array("field"=> $formField));
      }
      case "radio": {
        return $this->twig->render("yn/components/form/radio.twig", array("field"=> $formField));
      }
      case "date": {
        return $this->twig->render("yn/components/form/date.twig", array("field"=> $formField));
      }
      case "number": {
        return $this->twig->render("yn/components/form/number.twig", array("field"=> $formField));
      }
      case "textarea": {
        return $this->twig->render("yn/components/form/textarea.twig", array("field"=> $formField));
      }
      default: {
        return $this->twig->render("yn/components/form/basic.twig", array("field"=> $formField));
      }
    }
  }

  public function consents($form) {
    return $this->twig->render("yn/components/form/consents.twig", array("form" => $form));
  }

  public function pagination() {
   return $this->twig->render("yn/components/pagination.twig", array("uriData" => $this->uriData));
  }

  public function perPageDropdown() {
    return $this->twig->render("yn/components/perPageDropdown.twig", array("uriData" => $this->uriData));
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