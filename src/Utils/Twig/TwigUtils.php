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

  public function printImage($image, $sizes = array(), $classes = "") {
    $sources = $this->getSizes($image, $sizes);

    return $this->twig->render("yn/components/image.twig", array("image" => $image, "src" => $sources["src"], "srcset" => $sources["srcset"], "classes" => $classes));
  }

  public function printFigure($image, $sizes = array(), $classes = "") {
    $sources = $this->getSizes($image, $sizes);
    return $this->twig->render("yn/components/figure.twig", array("image" => $image, "src" => $sources["src"], "srcset" => $sources["srcset"], "classes" => $classes));
  }

  public function form($form, $section = array()) {
    $this->currentForm = $form;
    return $this->twig->render("yn/components/form.twig", array("form" => $form, "section" => $section, "templates" => $this->templates));
  }

  public function renderFields($form, $section = array(), $addValues) {
    $this->currentForm = $form;
    $fields = array();

    $currentY = -1;
    $fieldGrid = array();
    
    $hiddenFields = array();

    $currentRow = array();

    foreach($form["fields"] as $field){
      $grid = $field["grid"];
      
      if($field["type"] === "hidden") {
        $hiddenFields[] = $field;
      } 
      else {
        if(!is_array($fieldGrid[$grid["y"]])) {
          $fieldGrid[$grid["y"]] = array();
        }
        $fieldGrid[$grid["y"]][$grid["x"]] = $field;
        ksort($fieldGrid[$grid["y"]]);
      }
    }

    ksort($fieldGrid);

    return $this->twig->render("yn/components/renderFields.twig", array("form" => $form, "fieldGrid" => $fieldGrid, "hiddenFields" => $hiddenFields, "section" => $section, "templates" => $this->templates, "addValues" => $addValues));
  }

  public function printCookieSettingsButton() {
    return $this->twig->render("yn/module/consentManager/settingsButton.twig");
  }

  public function formField($formField, $renderWidget = true, $valueOverride = "") {
    switch($formField["type"]) {
      case "select": {
        return $this->twig->render("yn/components/form/select.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
      }
      case "checkbox": {
        return $this->twig->render("yn/components/form/checkbox.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
      }
      case "radio": {
        return $this->twig->render("yn/components/form/radio.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
      }
      case "date": {
        return $this->twig->render("yn/components/form/date.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
      }
      case "number": {
        return $this->twig->render("yn/components/form/number.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
      }
      case "textarea": {
        return $this->twig->render("yn/components/form/textarea.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
      }
      case "spacer": {
        return $this->twig->render("yn/components/form/spacer.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
      }
      default: {
        return $this->twig->render("yn/components/form/basic.twig", array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
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