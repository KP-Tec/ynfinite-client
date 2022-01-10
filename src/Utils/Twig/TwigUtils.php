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
            'form:fields.list' => 'yn/components/form/list.twig',
            'form:fields.hidden' => 'yn/components/form/hidden.twig',
            'form:fields.spacer' => 'yn/components/form/spacer.twig',
            'form:fields.description' => 'yn/components/form/description.twig',
            'form:fields.basic' => 'yn/components/form/basic.twig',
            'form:fields.categories' => 'yn/components/form/categories.twig',
            'form:fields.distance' => 'yn/components/form/distance.twig',
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

    public function printImage($image, $sizes = [], $classes = '', $nolazy = '')
    {
        $sources = $this->getSizes($image, $sizes);

        return $this->twig->render($this->getTemplate('images:image'), [
            'image' => $image,
            'src' => $sources['src'],
            'srcset' => $sources['srcset'],
            'height' => $sources['height'],
            'width' => $sources['width'],
            'classes' => $classes,
            'nolazy' => $nolazy,
        ]);
    }

    public function printFigure($image, $sizes = [], $classes = '', $nolazy = '')
    {
        $sources = $this->getSizes($image, $sizes);
        return $this->twig->render($this->getTemplate('images:figure'), [
            'image' => $image,
            'src' => $sources['src'],
            'srcset' => $sources['srcset'],
            'height' => $sources['height'],
            'width' => $sources['width'],
            'classes' => $classes,
            'nolazy' => $nolazy,
        ]);
    }

    public function form($form, $section = [])
    {
        $this->currentForm = $form;

        $data = array();
        foreach($form["events"] as $event) {
            $isAsync = "";
            if($event["async"]) {
                $isAsync = "async";
            }
            $data[] = "data-".strtolower($event["type"])."=".$isAsync;
        }

        return $this->twig->render($this->getTemplate('form:form'), [
            'form' => $form,
            'section' => $section,
            'templates' => $this->templates,
            "data" => implode(" ",$data)
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


            $fieldGrid = [];
            $currentRow = [];
            $currentY = -1;

            foreach ($group["elements"] as $field) {
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

    public function renderGroupFieldsByIndex(
		$form,
		$section = [],
		$groupIndex,
		$addValues = [],
		$parent = ''
	) {
		$this->currentForm = $form;

		$hiddenFields = [];

		$targetGroup = $form['groups'][$groupIndex];

		return $this->renderGroup($targetGroup);
	}

	public function renderGroupFieldsByAlias(
		$form,
		$section = [],
		$groupAlias,
		$addValues = [],
		$parent = ''
	) {
		$this->currentForm = $form;

		$hiddenFields = [];

		$targetGroup = array_filter($form['groups'], function($v) use($groupAlias) {
		 	return $v['alias'] === $groupAlias;
		 });


		if(sizeof($targetGroup) <= 0) {
			return "The chosen alias does not exist";
		}

		$targetGroup = array_values($targetGroup)[0];

		return $this->renderGroup($targetGroup);
	}

	private function renderGroup($targetGroup) {
		$groups = ['label' => $targetGroup['label']];

		$fieldGrid = [];
		$currentRow = [];
		$currentY = -1;

		foreach ($targetGroup["elements"] as $field) {
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

		$groups[0]['fields'] = $fieldGrid;


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

    public function printString ($data){
        $intro_title = array();
        foreach($data as $entry){
            if (is_array($entry)){
                if ($entry['value']){
                    $intro_title[]= $entry['prefix'].$entry['value'].$entry['postfix'];
                }
            } else{
                if ($entry){
                    $intro_title[]= $entry . ' ';
                }
            }
        }
        return implode('', $intro_title);
    }

    public function withVersion($path) {
        $parsed = parse_url($path);
        $separator = "?";
        if ($parsed["query"]){
            $separator = "&";
        }
        $time = filemtime(getcwd().$parsed["path"]) + 60 * 60 * 2;
        $pathWithVersion = $path.$separator."v=".$time;
        return($pathWithVersion);
    }
}
