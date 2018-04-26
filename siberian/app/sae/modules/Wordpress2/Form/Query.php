<?php

/**
 * Class Wordpress2_Form_Query
 */
class Wordpress2_Form_Query extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/wordpress2/application/editquery'))
            ->setAttrib('id', 'form-wordpress2-query');

        self::addClass('create', $this);

        $title = $this->addSimpleText('title', __('Title'));
        $subtitle = $this->addSimpleText('subtitle', __('Subtitle'));

        $showTitle = $this->addSimpleCheckbox('show_title', __('Show title & subtitle'));

        $coverHelp = '
<div class="alert alert-info">
    ' . __('Cover & Thumbnails are only visible when queries are not grouped.') . '
</div>
        ';

        $this->addSimpleHtml('cover_help', $coverHelp, [
            'class' => 'col-sm-12'
        ]);

        $showCover = $this->addSimpleCheckbox('show_cover', __('Show cover'));
        $picture = $this->addSimpleImage(
            'picture',
            __('Cover'),
            __('Import a cover image'),
            [
                'width' => 960,
                'height' => 600,
                'required' => true
            ]);
        $picture
            ->addClass('default_button')
            ->addClass('form_button');

        $thumbnail = $this->addSimpleImage(
            'thumbnail',
            __('Thumbnail'),
            __('Import a thumbnail image'),
            [
                'width' => 512,
                'height' => 512,
                'required' => true
            ]);
        $thumbnail
            ->addClass('default_button')
            ->addClass('form_button');

        $this->addSimpleHidden('value_id');
        $this->addSimpleHidden('query_id');

        $queryHelp = '
<div class="alert alert-info">
    ' . __("You can mix posts & pages in the queries if needed, however it's not recommended and can lead to random sorting.") . '
</div>
        ';

        $this->addSimpleHtml('query_help', $queryHelp, [
            'class' => 'col-sm-12'
        ]);
    }

    /**
     * @throws Zend_Form_Exception
     */
    public function addSortFields ()
    {
        $sortType = $this->addSimpleSelect('sort_type', __('Sort by'), [
            'date' => __('Date'),
            'author' => __('Author'),
            'id' => __('ID'),
            'relevance' => __('Relevance'),
            'title' => __('Title'),
        ]);

        $sortOrder = $this->addSimpleSelect('sort_order', __('Sort order'), [
            'desc' => __('Descending'),
            'asc' => __('Ascending'),
        ]);
    }

    /**
     * @param $categories
     * @param array $selectedCategories
     * @return $this
     */
    public function loadCategories ($categories, $selectedCategories = [])
    {
        $categoryParentId = [];
        foreach ($categories as $category) {
            $parent = $category['parent'];

            if (!array_key_exists($parent, $categoryParentId)) {
                $categoryParentId[$parent] = [];
            }
            $categoryParentId[$parent][] = $category;
        }

        $inputHtml = '
<label style="width: 100%;">
    <input type="checkbox" 
           name="categories[]" 
           value="#VALUE#" 
           #CHECKED#
           color="color-blue" 
           class="sb-form-checkbox color-blue" />
    <span class="sb-checkbox-label">#LABEL#</span>
</label>';

        // Sub function to recursively compute child categories!
        function displayRecursive ($parent, $categoryParentId, $inputHtml, $selectedCategories) {
            if (array_key_exists($parent, $categoryParentId)) {
                $currentCategories = $categoryParentId[$parent];

                $html = '';
                foreach ($currentCategories as $currentCategory) {
                    $currentParent = $currentCategory['id'];

                    $inputMarkup = str_replace(
                        [
                            '#VALUE#',
                            '#LABEL#',
                            '#CHECKED#'
                        ],
                        [
                            $currentParent,
                            sprintf(
                                "%s (%s, %s %s)",
                                $currentCategory['name'],
                                $currentCategory['slug'],
                                $currentCategory['count'],
                                __('posts')),
                            in_array($currentParent, $selectedCategories) ? 'checked="checked"' : ''
                        ],
                        $inputHtml);

                    $html .= '<li>' . $inputMarkup;

                    $subs = displayRecursive($currentParent, $categoryParentId, $inputHtml, $selectedCategories);
                    if (!empty($subs)) {
                        $subs = '<ul>' . $subs . '</ul>';
                    }
                    $html .= $subs . '</li>';
                }

                return $html;
            }
            return '';
        }

        $markupCategories = '<ul>' . displayRecursive(0, $categoryParentId, $inputHtml, $selectedCategories) . '</ul>';

        $markupCategories = '
<label for="categories" 
       class="sb-form-line-title col-sm-3 optional">' . __('Categories') . '</label>
<div class="col-sm-7"
     style="max-height: 400px;overflow-y: scroll;">
    ' . $markupCategories . '
</div>
<div class="sb-cb"></div>';

        $this->addSimpleHtml('markup_categories', $markupCategories);

        return $this;
    }

    /**
     * @param $pages
     * @param array $selectedPages
     * @return $this
     */
    public function loadPages ($pages, $selectedPages = [])
    {
        $pageParentId = [];
        foreach ($pages as $page) {
            $parent = $page['parent'];

            if (!array_key_exists($parent, $pageParentId)) {
                $pageParentId[$parent] = [];
            }
            $pageParentId[$parent][] = $page;
        }

        $inputHtml = '
<label style="width: 100%;">
    <input type="checkbox" 
           name="pages[]" 
           value="#VALUE#" 
           #CHECKED#
           color="color-blue" 
           class="sb-form-checkbox color-blue" />
    <span class="sb-checkbox-label">#LABEL#</span>
</label>';

        // Sub function to recursively compute child categories!
        function displayRecursivePage ($parent, $pageParentId, $inputHtml, $selectedPages) {
            if (array_key_exists($parent, $pageParentId)) {
                $currentPages = $pageParentId[$parent];

                $html = '';
                foreach ($currentPages as $currentPage) {
                    $currentParent = $currentPage['id'];

                    $inputMarkup = str_replace(
                        [
                            '#VALUE#',
                            '#LABEL#',
                            '#CHECKED#'
                        ],
                        [
                            $currentParent,
                            sprintf(
                                "%s (%s)",
                                $currentPage['title']['rendered'],
                                $currentPage['slug']
                            ),
                            in_array($currentParent, $selectedPages) ? 'checked="checked"' : ''
                        ],
                        $inputHtml);

                    $html .= '<li>' . $inputMarkup;

                    $subs = displayRecursivePage($currentParent, $pageParentId, $inputHtml, $selectedPages);
                    if (!empty($subs)) {
                        $subs = '<ul>' . $subs . '</ul>';
                    }
                    $html .= $subs . '</li>';
                }

                return $html;
            }
            return '';
        }

        $markupPages = '<ul>' . displayRecursivePage(0, $pageParentId, $inputHtml, $selectedPages) . '</ul>';

        $markupPages = '
<label for="categories" 
       class="sb-form-line-title col-sm-3 optional">' . __('Pages') . '</label>
<div class="col-sm-7"
     style="max-height: 400px;overflow-y: scroll;">
    ' . $markupPages . '
</div>
<div class="sb-cb"></div>';

        $this->addSimpleHtml('markup_pages', $markupPages);

        return $this;
    }

    /**
     * @return $this
     * @throws Zend_Form_Exception
     */
    public function createSubmit ()
    {
        $this->addSubmit(__('Save'))
            ->addClass('default_button')
            ->addClass('pull-right')
            ->addClass('submit_button');

        return $this;
    }
}