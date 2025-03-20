// Import WordPress components
const { registerBlockType } = wp.blocks;
const { RichText } = wp.blockEditor;
const { Fragment } = wp.element;

// Register the block
registerBlockType('correction-block/main', {
    title: 'Correction Link',
    icon: 'edit',
    category: 'common',
    attributes: {
        content: {
            type: 'string',
            default: ''
        }
    },
    
    edit: function(props) {
        const { attributes, setAttributes, className } = props;
        
        return (
            <Fragment>
                <div className={className}>
                    <RichText
                        tagName="p"
                        value={attributes.content}
                        onChange={(content) => setAttributes({ content })}
                        placeholder="Enter link text here"
                    />
                    <div className="preview-correction">
                        <span>Preview: </span>
                        <button className="correction-button">
                            {attributes.content || 'Correction'}
                        </button>
                    </div>
                </div>
            </Fragment>
        );
    },
    
    save: function() {
        // Return null to use the render_callback
        return null;
    }
});
