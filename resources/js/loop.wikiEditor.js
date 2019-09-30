// customize WikiEditor for LOOP
// @author Dennis Krohn @krohnden

mw.loader.using( 'user.options' ).then( function () {
    if ( mw.user.options.get( 'usebetatoolbar' ) == 1 ) {
        $.when(
            mw.loader.using( 'ext.wikiEditor' ), $.ready
        ).then( customizeWikiEditor );
    }
} );

var wikiEditor = $( '#wpTextbox1' );

var customizeWikiEditor = function () {
     // https://www.mediawiki.org/wiki/Extension:WikiEditor/Toolbar_customization

    
    $( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
        'section': 'characters'
    } );
    
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        'sections': {
            'loop': {
                'type': 'toolbar', 
                'labelMsg': 'loopwikieditor-section-loop'
            },
            'references': {
                'type': 'booklet', 
                'labelMsg': 'loopwikieditor-section-references',
                
            }
        }
    } );

    var wikiEditorElements = new Array();
    
    // add all pages to references that are given in variable
    $.each( loop_elements, function( $index, $value ) {
        var tmp_characters = [];

        var tag = ["<loop_reference id='", "'/>"];
        if ( $index == "loop_literature" ) {
            tag = ["<cite>", "</cite>"];
        }
        // add character button definitions
            $.each( $value, function( $key, $val ) {
                
        if ( $index == "loop_literature" ) {
            $tmp = {
                label: $key + " - " + $val, 
                action: { 
                    type: 'encapsulate', 
                    options: { 
                        pre: "<cite>" + $key + "</cite>" } 
                    } 
                }
        }
        else {
            $refid = $key.substring($key.indexOf("::") + 2);
            $tmp = { 
                label: $val, 
                action: { 
                    type: 'encapsulate', 
                    options: { 
                        pre: "<loop_reference id='" + $refid + "'/>" } 
                    } 
                }
        }
            tmp_characters.push( $tmp );
        } );
        // prepare page
        wikiEditorElements[ $index ] = { [$index]: {
            'layout': 'characters',
            'labelMsg': 'loopwikieditor-references-'+$index,
            'characters': tmp_characters
            }
        }
        // add page to editor
        addGroupToReferences( wikiEditorElements[ $index ] );

    } );
        
}

// adds given group to references in toolbar
function addGroupToReferences( group ) {
    wikiEditor.wikiEditor( 'addToToolbar', {
        'section': 'references',
        'pages': group 
    } );
}
