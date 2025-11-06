/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:
    config.language = 'pt';
    // config.uiColor = '#AADC6E';
    config.height = eval(this.element.$.rows * 40) + 'px';

    config.toolbar = [
        {name: 'clipboard', groups: ['clipboard'], items: ['Cut', 'Copy', 'Paste']},
        {
            name: 'basicstyles',
            groups: ['basicstyles', 'cleanup'],
            items: ['Bold', 'Italic', 'Underline', 'Strike', 'FontSize', 'Format']
        },
        {
            name: 'paragraph',
            groups: ['list', 'indent', 'blocks', 'align', 'bidi'],
            items: ['NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-']
        },
        {name: 'links', items: ['Link', 'Unlink']},
    ];
};
