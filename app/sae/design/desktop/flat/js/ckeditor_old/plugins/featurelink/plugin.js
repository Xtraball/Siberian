CKEDITOR.plugins.add('featurelink', {
    lang: 'en',
    icons: 'internal-link',
    init: function(editor) {
        editor.addCommand(
            'openFeaturelink',
            new CKEDITOR.dialogCommand('links')
        );

        editor.ui.addButton('featurelink', {
            label: 'Internal Link',
            command: 'openFeaturelink',
            icon: 'internal-link',
            toolbar : 'insert,101'
        });

        CKEDITOR.dialog.add('links', this.path+'dialogs/links.js');
    }
});