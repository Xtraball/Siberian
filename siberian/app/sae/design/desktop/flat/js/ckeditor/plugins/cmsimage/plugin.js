CKEDITOR.plugins.add('cmsimage', {
    lang: 'en',
    init: function(editor) {
        editor.ui.addButton('cmsimage', {
            label: 'Image',
            command: '',
            icon: 'image',
            toolbar : 'insert,100'
        });
    }
});