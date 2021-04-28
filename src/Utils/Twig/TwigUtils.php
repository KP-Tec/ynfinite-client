<?php

namespace App\Utils\Twig;

class TwigUtils
{
    private $data;

    public function __construct(
        $twig,
        $data,
        $templates,
        $templateOverrides,
        $uriData
    ) {
        $this->data = $data;
        $this->twig = $twig;
        $this->templates = $templates;
        $this->templateOverrides = $templateOverrides;
        $this->uriData = $uriData;

        $this->standardTemplates = [
            'article:article' => 'yn/components/article.twig',
            'article:headline' => 'yn/components/article/headline.twig',
            'article:text' => 'yn/components/article/text.twig',
            'article:images' => 'yn/components/article/images.twig',
            'article:video' => 'yn/components/article/video.twig',
            'images:image' => 'yn/components/image.twig',
            'images:figure' => 'yn/components/figure.twig',
            'form:form' => 'yn/components/form.twig',
            'form:fields.select' => 'yn/components/form/select.twig',
            'form:fields.radio' => 'yn/components/form/radio.twig',
            'form:fields.checkbox' => 'yn/components/form/checkbox.twig',
            'form:fields.datetime-local' =>
                'yn/components/form/datetime-local.twig',
            'form:fields.month' => 'yn/components/form/month.twig',
            'form:fields.date' => 'yn/components/form/date.twig',
            'form:fields.time' => 'yn/components/form/time.twig',
            'form:fields.week' => 'yn/components/form/week.twig',
            'form:fields.color' => 'yn/components/form/color.twig',
            'form:fields.number' => 'yn/components/form/number.twig',
            'form:fields.range' => 'yn/components/form/range.twig',
            'form:fields.text' => 'yn/components/form/text.twig',
            'form:fields.textarea' => 'yn/components/form/textarea.twig',
            'form:fields.tel' => 'yn/components/form/tel.twig',
            'form:fields.url' => 'yn/components/form/url.twig',
            'form:fields.email' => 'yn/components/form/email.twig',
            'form:fields.password' => 'yn/components/form/password.twig',
            'form:fields.file' => 'yn/components/form/file.twig',
            'form:fields.complexFormField' =>
                'yn/components/form/complexFormField.twig',
            'form:fields.hidden' => 'yn/components/form/hidden.twig',
            'form:fields.spacer' => 'yn/components/form/spacer.twig',
            'form:fields.description' => 'yn/components/form/description.twig',
            'form:fields.basic' => 'yn/components/form/basic.twig',
            'listing:pagination' => 'yn/components/pagination.twig',
            'listing:perPageDropdown' => 'yn/components/perPageDropdown.twig',
        ];
    }

    private function getTemplate($name)
    {
        $template = $this->standardTemplates[$name];
        if ($this->templateOverrides[$name]) {
            $template = $this->templateOverrides[$name];
        }

        return $template;
    }

    private function getSizes($image, $confAlias)
    {
        $srcset = [];
        $src = '';

        $sizeConfig = [];

        if (!is_array($confAlias)) {
            $sizeConfig = $this->data['images'][$confAlias];
            $sizes = $sizeConfig['sizes'];
        } else {
            $sizes = $confAlias;
        }

        if (count($sizes) === 0) {
            $defaultSizesIndex = array_search(
                'true',
                array_column($this->data['images'], 'isDefault')
            );

            if ($defaultSizesIndex !== false) {
                $keys = array_keys($this->data['images']);
                $sizeConfig = $this->data['images'][$keys[$defaultSizesIndex]];
                $sizes = $sizeConfig['sizes'];
            }
        }

        foreach ($sizes as $size) {
            $path = $image['path'];

            $attrArray = [];
            if ($size['w']) {
                $attrArray[] = 'w=' . $size['w'];
            }
            if ($size['h']) {
                $attrArray[] = 'h=' . $size['h'];
            }
            if ($sizeConfig['disableWebp'] === true) {
                $attrArray[] = 'disableWebp=1';
            }
            $path .= '?' . implode('&', $attrArray);

            if ($size['screenSize']) {
                $srcset[] = $path . ' ' . $size['screenSize'] . 'w';
            } else {
                $src = $path;
            }
        }

        if (!$src) {
            $src = $image['path'];
        }

        return [
            'src' => $src, 
            'srcset' => implode(',', $srcset),
            'height' => $sizes[0]["h"], 
            'width' => $sizes[0]["w"]
        ];
    }

    public function printImage($image, $sizes = [], $classes = '')
    {
        $sources = $this->getSizes($image, $sizes);

        return $this->twig->render($this->getTemplate('images:image'), [
            'image' => $image,
            'src' => $sources['src'],
            'srcset' => $sources['srcset'],
            'height' => $sources['height'],
            'width' => $sources['width'],
            'classes' => $classes,
        ]);
    }

    public function printFigure($image, $sizes = [], $classes = '')
    {
        $sources = $this->getSizes($image, $sizes);
        return $this->twig->render($this->getTemplate('images:figure'), [
            'image' => $image,
            'src' => $sources['src'],
            'srcset' => $sources['srcset'],
            'height' => $sources['height'],
            'width' => $sources['width'],
            'classes' => $classes,
        ]);
    }

    public function form($form, $section = [])
    {
        $this->currentForm = $form;
        return $this->twig->render($this->getTemplate('form:form'), [
            'form' => $form,
            'section' => $section,
            'templates' => $this->templates,
        ]);
    }

    public function renderArticle($article, $imageConfigAlias = [])
    {
        return $this->twig->render($this->getTemplate('article:article'), [
            'article' => $article,
            'imageConfigAlias' => $imageConfigAlias,
        ]);
    }

    public function renderArticleComponent($component, $imageConfigAlias = [])
    {
        return $this->twig->render(
            $this->getTemplate('article:' . $component['type']),
            ['component' => $component, 'imageConfigAlias' => $imageConfigAlias]
        );
    }

    public function renderFields(
        $form,
        $section = [],
        $addValues = [],
        $parent = ''
    ) {
        $this->currentForm = $form;

        $groups = [];

        $hiddenFields = [];

        foreach ($form['groups'] as $key => $group) {
            $groups[$key] = ['label' => $group['label']];

            $groupFields = array_filter($form['fields'], function ($field) use (
                $group
            ) {
                return in_array($field['_id'], $group['fields']);
            });

            $fieldGrid = [];
            $currentRow = [];
            $currentY = -1;

            foreach ($groupFields as $field) {
                $grid = $field['grid'];

                if ($field['type'] === 'hidden') {
                    $hiddenFields[] = $field;
                } else {
                    if (!is_array($fieldGrid[$grid['y']])) {
                        $fieldGrid[$grid['y']] = [];
                    }
                    $fieldGrid[$grid['y']][$grid['x']] = $field;
                    ksort($fieldGrid[$grid['y']]);
                }
            }

            ksort($fieldGrid);

            $groups[$key]['fields'] = $fieldGrid;
        }

        return $this->twig->render('yn/components/renderFields.twig', [
            'form' => $form,
            'groups' => $groups,
            'parent' => $parent,
            'hiddenFields' => $hiddenFields,
            'section' => $section,
            'templates' => $this->templates,
            'addValues' => $addValues,
        ]);
    }

    public function printCookieSettingsButton()
    {
        return $this->twig->render(
            'yn/module/consentManager/settingsButton.twig'
        );
    }

    public function formField(
        $formField,
        $renderWidget = true,
        $valueOverride = '',
        $parent = ''
    ) {
        if ($parent) {
            $parent = '[' . $parent . '][::count::]';
        }

        $template = $this->getTemplate('form:fields.' . $formField['type']);
        if (!$template) {
            var_dump('form:fields.' . $formField['type']);
            return '<p>Error</p>';
        } else {
            return $this->twig->render($template, [
                'field' => $formField,
                'parent' => $parent,
                'renderWidget' => $renderWidget,
                'form' => $this->currentForm,
                'addValue' => $valueOverride,
            ]);
        }
    }

    public function consents($form)
    {
        return $this->twig->render('yn/components/form/consents.twig', [
            'form' => $form,
        ]);
    }

    public function pagination()
    {
        return $this->twig->render($this->getTemplate('listing:pagination'), [
            'uriData' => $this->uriData,
        ]);
    }

    public function perPageDropdown()
    {
        return $this->twig->render(
            $this->getTemplate('listing:perPageDropdown'),
            ['uriData' => $this->uriData]
        );
    }

    public function linkPage($pageSlug, $slug = '')
    {
        $route = $this->data['routes'][$pageSlug];
        return str_replace('{{alias}}', $slug, $route);
    }

    public function sectionByAlias($alias)
    {
        $sections = $this->data['sections'];
        foreach ($sections as $key => $section) {
            if ($section['alias'] === $alias) {
                $section['template'] = $this->data['templates'][$section['template']];
                return $section;
            }
        }
        return false;
    }
}
