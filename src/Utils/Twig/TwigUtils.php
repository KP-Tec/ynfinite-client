<?php

namespace App\Utils\Twig;


class TwigUtils
{

  private $data;

  public function __construct($twig, $data, $templates, $templateOverrides, $uriData)
  {
    $this->data = $data;
    $this->twig = $twig;
    $this->templates = $templates;
    $this->templateOverrides = $templateOverrides;
    $this->uriData = $uriData;


    $this->standardTemplates = array(
      "images:image" => "yn/components/image.twig",
      "images:figure" => "yn/components/figure.twig",
      "form:form" => "yn/components/form.twig",
      "form:fields.select" => "yn/components/form/select.twig",
      "form:fields.radio" => "yn/components/form/radio.twig",
      "form:fields.checkbox" => "yn/components/form/checkbox.twig",
      "form:fields.datetime-local" => "yn/components/form/datetime-local.twig",
      "form:fields.month" => "yn/components/form/month.twig",
      "form:fields.date" => "yn/components/form/date.twig",
      "form:fields.time" => "yn/components/form/time.twig",
      "form:fields.week" => "yn/components/form/week.twig",
      "form:fields.color" => "yn/components/form/color.twig",
      "form:fields.number" => "yn/components/form/number.twig",
      "form:fields.range" => "yn/components/form/range.twig",
      "form:fields.text" => "yn/components/form/text.twig",
      "form:fields.textarea" => "yn/components/form/textarea.twig",
      "form:fields.tel" => "yn/components/form/tel.twig",
      "form:fields.url" => "yn/components/form/url.twig",
      "form:fields.email" => "yn/components/form/email.twig",
      "form:fields.password" => "yn/components/form/password.twig",
      "form:fields.file" => "yn/components/form/file.twig",
      "form:fields.hidden" => "yn/components/form/hidden.twig",
      "form:fields.spacer" => "yn/components/form/spacer.twig",
      "form:fields.basic" => "yn/components/form/basic.twig"
    );
  }

  private function getTemplate($name) {
    $template = $this->standardTemplates[$name];
    if($this->templateOverrides[$name]) {
      $template = $this->templateOverrides[$name];
    }

    return $template;
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

    return $this->twig->render($this->getTemplate("images:image"), array("image" => $image, "src" => $sources["src"], "srcset" => $sources["srcset"], "classes" => $classes));
  }

  public function printFigure($image, $sizes = array(), $classes = "") {
    $sources = $this->getSizes($image, $sizes);
    return $this->twig->render($this->getTemplate("images:figure"), array("image" => $image, "src" => $sources["src"], "srcset" => $sources["srcset"], "classes" => $classes));
  }

  public function form($form, $section = array()) {
    $this->currentForm = $form;
    return $this->twig->render($this->getTemplate("form:form"), array("form" => $form, "section" => $section, "templates" => $this->templates));
  }

  public function renderFields($form, $section = array(), $addValues = array()) {
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
    
    $template = $this->getTemplate("form:fields.".$formField["type"]);
    if(!$template) {
      var_dump("form:fields.".$formField["type"]);
      return "<p>Error</p>";
    }
    else {
      return $this->twig->render($template, array("field"=> $formField, "renderWidget" => $renderWidget, "form" => $this->currentForm, "addValue" => $valueOverride));
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