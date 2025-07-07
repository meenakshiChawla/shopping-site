
( function( blocks, blockEditor, element ) {
    var el = element.createElement;
    var InnerBlocks = blockEditor.InnerBlocks;

    blocks.registerBlockType( 'custom/callout-box', {
        title: 'Callout Box',
        icon: 'megaphone',
        category: 'widgets',
        supports: {
            html: false,
            align: ['wide', 'full']
        },
        edit: function() {
            return el( 'div', { className: 'custom-callout-box' }, el( InnerBlocks ) );
        },
        save: function() {
            return el( 'div', { className: 'custom-callout-box' }, el( InnerBlocks.Content ) );
        }
    } );
} )(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.element
);
