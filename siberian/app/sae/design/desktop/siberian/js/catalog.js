var Categories = Class.extend({

    init: function(url) {

        this.categories = new Array();
        this.currentParentId = 0;
        this.url = url;

        this.processEvents();
    },

    processEvents: function() {

        $(document).ready(function() {
            $('#categoryForm').validate({
                submitHandler: function(form) {
                    reload(form, form.action, true, function(response) {
                        $('#list_of_categories').append(response.row_html);
                        $(form).find('input').val('');
                    });
                    return false;
                }
            });

            $('#subcategoryForm').validate({
                submitHandler: function(form) {
                    reload(form, form.action, true, function(response) {
                        $('#list_of_subcategories').append(response.row_html);
                        $('#subcategory_name').val('');
                        $('#row_'+response.category_id).slideDown();
                    });
                    return false;
                }
            });
        });
    },

    add: function(id, parent_id) {

        var category = {
            id: id,
            parent_id: parent_id,
            edit: function() {

                $('#details_'+this.id).slideDown();
                $('#list_of_outlets_'+this.id).slideDown();
                $('#edit_category_'+this.id).hide();
                $('#category_name_'+this.id).fadeIn();
                $('#validate_'+this.id).slideDown();

                $('#list_of_outlets_'+this.id).show();

            },
            hide: function() {

                if($('#category_name_'+this.id).val().isEmpty()) return;

                var callback = function(response) {

                    var category_id = response.category_id;

                    $('#edit_category_'+category_id).html($('#category_name_'+category_id).val());
                    $('#details_'+category_id).slideUp();
                    $('#list_of_outlets_'+category_id).slideUp();
                    $('#category_name_'+category_id).hide();
                    $('#edit_category_'+category_id).fadeIn();
                    $('#validate_'+category_id).slideUp();

                    $('#list_of_outlets_'+category_id).hide();
//                    alert(category_id);
                    var li = $('#row_'+category_id);
                    var default_color = li.css('color');
                    li.css('background-color', '#208FAF').css('color', 'white');
                    li.animate({backgroundColor: 'white', color: default_color}, 1000, function() { li.removeAttr('style'); });
                    li.removeClass('active');

                    if(this.parent_id == 0) {
                        this.hideChildren();
                    }
                }.bind(this);
                this.submit(callback);

            },
            remove: function() {

                $('#'+this.id+'_is_deleted').val(1);

                var callback = function(response) {
                    var category_id = response.category_id;

                    $('#edit_category_'+category_id).css('color', 'white');
                    $('#row_'+category_id).css('background-color', '#C41313').css('color', 'white')
                    .animate({opacity: 0.3, height: 0}, 500, function() {
                        $('#row_'+category_id).remove();
                    });

                    if(this.parent_id == 0)  {
                        this.hideChildren();
                    }

                }.bind(this);

                this.submit(callback);
            },
            showChildren: function() {

                if(this.parent_id > 0) return;

                categories.hideAllChildren(this.id);

                $('#row_'+this.id).addClass('active');

                $('#parent_id').val(this.id);

                $('#arrow_'+this.id).show();

                $('#list_of_subcategories').find('li').each(function(e, element) {
                    if($(element).attr('rel')) {
                        if($(element).attr('rel') == id) $(element).slideDown();
                    }
                }.bind(this));

                $('#subcategoryForm').show();

            },
            hideChildren: function() {

                $('#list_of_subcategories').find('li').slideUp();

                $('#arrow_'+this.id).hide();
                $('#subcategoryForm').hide();

                $('#parent_id').val('');

                $('#row_'+this.id).removeClass('active');
            },

            activate: function() {
                $('#category_name_'+this.id).css('color', 'green');
                $('#edit_category_'+this.id).css('color', 'green');
                $('#'+this.id+'_is_active').val(1);
            },

            deactivate: function() {
                $('#category_name_'+this.id).css('color', 'red');
                $('#edit_category_'+this.id).css('color', 'red');
                $('#'+this.id+'_is_active').val(0);
            },

            submit: function(callback) {
                if(!callback) callback = function() {}
                reload($('#form_'+this.id), categories.url, true, callback);
            }
        };

        this.updateEvents(id);

        this.categories[id] = category;

        return category;

    },

    submit: function(form, parent_id) {

        if(!form.valid()) return false;

        reload(form, form.attr('action'), true, function(response) {
            $('#list_of_subcategories_'+parent_id).append(response.row_html);
            $('#subcategory_name_'+parent_id).val('');
            $('#row_'+response.category_id).css({'border-color': 'white', 'background-color': 'white'}).slideDown();
            iframe.f.reload();
        });

    },

    updateEvents: function(id) {

        $('#edit_category_'+id).unbind('click');
        $('#edit_category_'+id).click(function() {
            this.categories[id].showChildren();
            return false;
        }.bind(this));

        $('#category_name_'+id).unbind('keyup');
        $('#category_name_'+id).keyup(function(e) {
            if(e.keyCode == 13) this.hide(id);
        }.bind(this));

        $('#row_'+id).unbind('mouseenter mouseleave');
        $('#row_'+id).hover(function() {
            if(!$(this).hasClass('active')) {
                $('#handle_'+id).css('visibility', 'visible');
                $(this).stop().animate({ borderColor: '#888888', backgroundColor: '#E8E8E8' }, 300);
            }
        }, function() {
            if(!$(this).hasClass('active')) {
                $('#handle_'+id).css('visibility', 'hidden');
                $(this).animate({ borderColor: 'transparent', backgroundColor: 'transparent' }, 10);
            }
        });

        $('#row_'+id+' input.edit').unbind('click');
        $('#row_'+id+' input.edit').click(function() {
            this.edit(id);
        }.bind(this));

        $('#row_'+id+' input.remove').unbind('click');
        $('#row_'+id+' input.remove').click(function() {
            this.remove(id);
        }.bind(this));

        $('#validate_'+id).unbind('click');
        $('#validate_'+id).click(function() {
            this.hide(id);
        }.bind(this));

        $('#activate_'+id).unbind('click');
        $('#activate_'+id).click(function() {
            this.categories[id].activate();
        }.bind(this));

        $('#deactivate_'+id).unbind('click');
        $('#deactivate_'+id).click(function() {
            this.categories[id].deactivate();
        }.bind(this));
    },

    edit: function(id) {
        var category = this.categories[id];
        category.edit();
    },

    hide: function(id) {
        var category = this.categories[id];
        this.currentParentId = 0;
        category.hide();
    },

    remove: function(id) {
        if(confirm('Etes-vous sûr de vouloir supprimer cette catégorie ?')) {
            var category = this.categories[id];
            category.remove();
        }
    },

    hideAllChildren: function(id) {

        $('#list_of_categories').children('li').removeClass('active').removeAttr('style');
        $('.handle').each(function(e, element) {
            var handle = $(element);
            if(element.id != 'handle_'+id /*&& handle.is(':visible')*/) handle.css('visibility', 'hidden');
        });

        $('#list_of_subcategories').find('li').each(function(e, element) {
            if($(element).attr('rel')) {
                if($(element).attr('rel') != id) $(element).slideUp();
            }
        }.bind(this));

//            $('.arrows').hide();

    }

});


var Products = Class.extend({

    init: function(url) {
        this.list = new Array();
        this.url = url;

        this.processEvents();
    },

    processEvents: function() {

        $('.subcategory').change(function() {
            var id = $(this).val();
            $('#products_list_'+id).show();
        });

        $('.automatic').each(function() {
            new AutomaticInput($(this), $(this).attr('title'));
        });

        productForm.init();

        $('#add_product').click(function() {
            productForm.show();
//            $('#productForm').find('input').not(':radio').each(function() {
//                if(this.id != 'category_id' && $(this).attr('name') != 'value_id') {
//                    if($(this).hasClass('automatic')) {
//                        $(this).val($(this).attr('title'));
//                        if(!$(this).hasClass('automatic_input')) $(this).addClass('automatic_input');
//                    }
//                    else $(this).val('');
//                }
//            });
//            if(!$('#createProduct').is(':visible')) {
//                $('#createProduct').slideDown();
//            }
//
//            $('#product_configuration').show();
//
//            showSimpleInfos();

            return false;
        });

        $('#hide_form').click(function() {
            productForm.hide();
        }.bind(this));

        $('.is_multiple').click(function() {
            if($(this).val() == 1) {
                showFormatInfos();
            }
            else {
                showSimpleInfos();
            }
        });

        $('#add_format').click(function() {
    //        var nbr = $('.new_format:visible').length + 1;
            var isShown = false;
            $('#formats .new_format').each(function() {
                if(!$(this).is(':visible') && !isShown) {
                    var id = $(this).attr('rel');
                    $(this).fadeIn();
                    $(this).find('input').removeAttr('disabled');
                    $('#product_format_is_deleted_'+id).val('0');
                    isShown = true;
                }
            });
        });

        $('#formats .delete_format').click(function() {
            var id = $(this).attr('rel');
            $('#row_format_'+id).find('input').attr('disabled', 'disabled');
            $('#row_format_'+id).fadeOut();
            $('#product_format_option_id_'+id).removeAttr('disabled');
            $('#product_format_is_deleted_'+id).removeAttr('disabled').val('1');
            return false;
        });

    },

    add: function(id, datas, formats) {

        var product = {
            id: null,
            datas: [],
            formats: [],
            isEditing: false,
            is_active: 1,
            form: productForm,
            init: function(id, datas, formats) {
                this.id = id;
                this.datas = datas;
                this.formats = formats;
            },
            edit: function() {

                this.isEditing = true;
                var id = this.id;

                this.form.show(this);

                $('.product_rows').each(function() {
                    if(this.id != 'row_'+id) $(this).addClass('deactive').slideUp();
                    else $(this).slideDown(300, function() {$(this).removeClass('deactive');});
                });

                $('#row_'+this.id).addClass('active');

            },

            remove: function() {

                if(confirm('Êtes-vous sur de vouloir supprimer ce produit ?')) {
                    $('#'+this.id+'_is_deleted').val(1);

                    var callback = function(response) {
                        var product_id = response.product_id;

                        $('#label_'+product_id).css('color', 'white');
                        $('#row_'+product_id).css('background-color', '#C41313').css('color', 'white')
                        .animate({opacity: 0.3, height: 0}, 500, function() {
                            $('#row_'+product_id).remove();
                        });

                    }.bind(this);

                    this.submit(callback);
                }
            },

            activate: function() {
                $('#label_'+this.id).css('color', 'green');
                this.is_active = 1;
            },

            deactivate: function() {
                $('#label_'+this.id).css('color', 'red');
                this.is_active = 0;
            },

            submit: function(callback) {
                if(!callback) callback = function() {}
                reload($('#productForm'), $('#productForm').attr('action'), true, callback);
            },

            getData: function(key) {
                return typeof this.datas[key] != 'undefined' ? this.datas[key] : null;
            }
        };

        product.init(id, datas, formats);

        this.updateEvents(id);

        this.list[id] = product;

        return product;

    },

    updateEvents: function(id) {

        $('#row_'+id).unbind('mouseenter mouseleave');
        $('#row_'+id).hover(function(){
            if(!$(this).hasClass('active') && !$(this).hasClass('deactive')) {
                $('#handle_'+id).css('visibility', 'visible');
                $(this).stop().animate({ borderColor: '#888888', backgroundColor: '#E8E8E8' }, 300);
            };
        }, function() {
            if(!$(this).hasClass('active') && !$(this).hasClass('deactive')) {
                $('#handle_'+id).css('visibility', 'hidden');
                $(this).animate({ borderColor: 'transparent', backgroundColor: 'transparent' }, 10);
            }
        });

        $('#edit_'+id).unbind('click');
        $('#edit_'+id).click(function() {
            this.list[id].edit();
            return false;
        }.bind(this));

        $('#label_'+id).unbind('click');
        $('#label_'+id).click(function() {
            this.list[id].edit();
            return false;
        }.bind(this));

        $('#delete_'+id).unbind('click');
        $('#delete_'+id).click(function() {
            this.list[id].remove(id);
            return false;
        }.bind(this));

//        $('.toggle_pos_'+id).unbind('click');
//        $('.toggle_pos_'+id).change(function() {
//            var id = $(this).val();
//            var isChecked = $(this).is(':checked');
//            $(this).parent('td').children('input').each(function() {
//                if($(this).attr('rel')) {
//                    if($(this).attr('rel') == id && !$(this).val().isEmpty()) {
//                        if(isChecked) $(this).removeAttr('disabled');
//                        else $(this).attr('disabled', 'disabled');
//                    }
//                }
//            })
//
//        });

    }
});

var productForm = {
    element: $('#productForm'),

    init: function() {

        $('#productForm').find('input.price').blur(function() {
            $(this).formatCurrency($(this), {region: 'fr'});
        });

        $('#productForm').submit(this.submit.bind(this));
    },

    submit: function() {

        var inputs = $('#product_infos').find('input');
        var error = false;
        inputs.each(function() {
            var input = $(this);

            if(!input.is(':visible') && input.val() == input.attr('title')) input.attr('disabled', 'disabled')

            if(input.attr('disabled') == 'disabled') return;

            if(input.hasClass('required') && input.val().trim() == '' || input.hasClass('automatic') && input.val() == input.attr('title')) {
                input.addClass('error');
                error = true;
            }
            else if(input.hasClass('number') && isNaN(input.val().replace('€', '').replace(',', '.').trim())) {
                input.addClass('error');
                error = true;
            }
            else {
                input.removeClass('error');
            }

        });

        if($('#select_categories').attr('id')) {
            if($('#select_categories').val().isEmpty() || $('#select_categories').val() == 0) {
                error = true;
                $('#select_categories').addClass('error');
            }
            else {
                $('#select_categories').removeClass('error');
            }
            if($('.subcategory:visible').length > 0) {
                var subcategory = $('.subcategory:visible');
                if(subcategory.val().isEmpty() || subcategory.val() == 0) {
                    error = true;
                    subcategory.addClass('error');
                }
                else {
                    subcategory.removeClass('error');
                }
            }
        }


        if(!error) {
            var callback = function(response) {

                if(response.row_html) {
                    $('#products_list').append(response.row_html);
                    $('#row_'+response.product_id).slideDown();
                    $('#createProduct').slideUp();
                    if(typeof iframe == 'object') iframe.f.reload();
                }

                if(response.product_id) {

                    var product_id = response.product_id;
                    if(response.datas) {
                        var product = products.list[product_id];
                        product.init(response.product_id, response.datas, response.formats);
                    }

                    var li = $('#row_'+product_id);
                    var default_color = li.css('color');
                    li.css('background-color', '#208FAF').css('color', 'white');
                    li.animate({backgroundColor: 'white', color: default_color}, 1000, function() {
                        li.removeAttr('style');
                        $('#row_'+product_id).show();
                    });

                    $('#add_product').show();
                    $('.product_rows').slideDown(300, function() {$(this).removeClass('deactive');});

                    products.list[product_id].isEditing = false;

                }

                this.hide();

            }.bind(this)
            reload($('#productForm'), $('#productForm').attr('action'), true, callback);
        }

        return false;

    },

    show: function(product) {

        if(product) {
            $('#product_name').val(product.getData('name'));
            $('#product_description').val(product.getData('description'));
            $('#product_id').val(product.id);

            if(product.formats.length > 0) {
                $('#is_multiple_1').click();
                for(var i in product.formats) {
                    var format = product.formats[i];
                    var id = format.id;
                    $('#product_format_title_'+i).val(format.title);
                    $('#product_format_price_'+i).val(format.price);
                    $('#product_format_option_id_'+i).val(format.id);
                    $('#product_format_is_deleted_'+i).val('0');
                    $('#row_format_'+i).show();
                }
                for(var remaining=3;remaining>i;remaining--) {
                    $('#row_format_'+remaining).find('input').attr('disabled', 'disabled');
                    $('#product_format_option_id_'+remaining).val('');
                }
            }
            else {
                $('#is_multiple_0').click();
                $('#product_price').val(product.getData('price'));
            }

            if(!product.getData('category_id').isEmpty() && product.getData('category_id') > 0) {
                var category_id = product.getData('category_id');
                var hasValue = false;

                $('#select_categories option').each(function() {
                    if($(this).val() == category_id) {
                        $('#select_categories').val(category_id);
                        hasValue = true;
                    }
                });

                if(!hasValue) {

                    $('.subcategory').hide().attr('disabled', 'disabled').val('');
                    $('.subcategory option').each(function() {
                        if($(this).val() == category_id) {
                            $(this).parent().val(category_id).removeAttr('disabled').show();
                            $('#select_categories').val($(this).attr('rel'));
                            $('#subcategories').slideDown();
                        }
                    });
                }
                else {
                    $('#subcategories').hide();
                }
            }
            else {
                $('#subcategories').hide();
                $('.subcategory').hide().attr('disabled', 'disabled').val('');
            }

            $('#add_product').hide();
            $('#product_configuration').hide();

            $('#product_name').unbind('keyup');
            $('#product_name').keyup(function() {
                $('#label_'+product.id).html($(this).val());
            });
        }
        else {
            $('#product_configuration').show();
            showSimpleInfos();
        }

        if(!$('#createProduct').is(':visible')) {
            $('#createProduct').slideDown();
        }

        return false;

    },

    hide: function() {

        $('#createProduct').slideUp(300, function() {
//            $('#productForm').get(0).reset();
            $('.autoreset').each(function() {
                if($(this).hasClass('automatic_input')) $(this).val($(this).attr('title'));
                else $(this).val('');
            });
            $('.subcategory').hide();
        });
        $('#add_product').show();
        $('.product_rows').removeClass('active').slideDown(300, function() {$(this).removeClass('deactive');});
        $('#product_name').unbind('keyup');

        $('#category_id').val('');
        $('#product_id').val('');

        $('#row_format_2').hide();
        $('#row_format_3').hide();

        for(var i in products.list) {
            if(products.list[i].isEditing) {
                $('#label_'+products.list[i].id).html(products.list[i].getData('name'));
                products.list[i].isEditing = false;
            }
        }

        iframe.f.reload();

    }
}

function showSimpleInfos() {

    $('#is_multiple_0').attr('checked', 'checked');
    $('#is_multiple_1').removeAttr('checked');

    $('#simple_infos').find('input').removeAttr('disabled');
    $('#format_infos').find('input').attr('disabled', 'disabled');

    $('#simple_infos').fadeIn();
    $('#format_infos').slideUp();
}

function showFormatInfos() {
    $('#is_multiple_0').removeAttr('checked');
    $('#is_multiple_1').attr('checked', 'checked');

    $('#simple_infos').find('input').attr('disabled', 'disabled');
    $('#format_infos').find('input').removeAttr('disabled');

    $('#simple_infos').attr('disabled', 'disabled').fadeOut();
    $('#format_infos').removeAttr('disabled').slideDown();
}