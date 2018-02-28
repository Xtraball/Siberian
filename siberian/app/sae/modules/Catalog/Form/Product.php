<?php

/**
 * Class Catalog_Form_Product
 */
class Catalog_Form_Product extends Siberian_Form_Abstract {

    // Product formats!
    public static $formatTemplate = '
<div class="row product-format-line">
    <div class="col-md-7">
        <input type="text" 
               tabindex="0"
               class="input-flat"
               placeholder="%TITLE_PL%"
               value="%TITLE_VALUE%"
               name="format[%UUID%][title]" />
    </div>
    <div class="col-md-3">
        <input type="number"
               tabindex="0"
               step="0.0001"
               min="0"
               max="999999999999999"
               class="input-flat"
               placeholder="%PRICE_PL%"
               value="%PRICE_VALUE%"
               name="format[%UUID%][price]" />
    </div>
    <div class="col-md-2">
        <button tabindex="-1" 
                class="delete-format btn default_button color-blue pull-right">
            <i class="fa fa-remove pull-right"></i>
        </button>
    </div>
</div>';

    public function init() {
        parent::init();

        $this
            ->setAction(__path('/catalog/application/editproduct'))
            ->setAttrib('id', 'form-catalog-product');

        // Bind as a create form!
        self::addClass('create', $this);
        self::addClass('callback', $this);

        $this->addNav('goback', __('Save'));

        $this->addSimpleHtml('line_break', '<br />');

        $name = $this->addSimpleText('name', __('Product name'));
        $name->setRequired(true);

        $description = $this->addSimpleTextarea('description', __('Description'));
        $description
            ->setRichtext();

        $price = $this->addSimpleText('price', __('Price'));

        $picture = $this->addSimpleImage(
            'picture',
            __('Add a picture'),
            __('Add a picture'),
            [
                'width' => 512,
                'height' => 512
            ]);
        $picture
            ->addClass('default_button')
            ->addClass('form_button');

        $htmlFormat = '
<div class="product-format-container"
     style="display: none;">
     <button class="product-add-format btn default_button color-blue">' . __('Add a format') . '</button>
</div>
';
        $enableFormat = $this->addSimpleCheckbox('enable_format', __('Enable product formats?'));

        $productFormat = $this->addSimpleHtml('product_format', $htmlFormat);

        $categoryId = $this->addSimpleHidden('category_id');
        $productId = $this->addSimpleHidden('product_id');
        $position = $this->addSimpleHidden('position');
        $valueId = $this->addSimpleHidden('value_id');

        $this->addSubmit(__('Save'))
            ->addClass('default_button')
            ->addClass('pull-right')
            ->addClass('submit_button');

        $jsHandler = '
<script type="text/javascript">
    $(document).ready(function () {
        var guid = function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }
            return s4() + s4() + s4() + s4() + s4();
        };
        
        var formatTemplate = `' . self::$formatTemplate . '`;
        var container = $(".product-format-container");
        
        $("#enable_format").off("click");
        $("#enable_format").on("click", function () {
            var el = $(this);
            container.toggle(el.prop("checked"));
        });
        
        $(document).off("click", ".delete-format");
        $(document).on("click", ".delete-format", function (event) {
            event.preventDefault();
            var el = $(this);
            el.closest(".product-format-line").remove();
        });
        
        $(".product-add-format").off("click");
        $(".product-add-format").on("click", function (event) {
            event.preventDefault();
            container.append(
                formatTemplate
                    .replace(/%UUID%/ig, guid())
                    .replace(/%TITLE_PL%/ig, "' . __('New format') . '")
                    .replace(/%TITLE_VALUE%/ig, "")
                    .replace(/%PRICE_PL%/ig, "' . __('Price') . '")
                    .replace(/%PRICE_VALUE%/ig, "")
            );
        });
    });
</script>';

        $this->addMarkup($jsHandler);
    }
}