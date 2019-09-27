/* Check if view is in edit mode and that the required modules are available. Then, customize the toolbar … */
//if ( [ 'edit', 'submit' ].indexOf( mw.config.get( 'wgAction' ) ) !== -1 ) {
	mw.loader.using( 'user.options' ).then( function () {
		// This can be the string "0" if the user disabled the preference ([[phab:T54542#555387]])
		if ( mw.user.options.get( 'usebetatoolbar' ) == 1 ) {
			$.when(
				mw.loader.using( 'ext.wikiEditor' ), $.ready
			).then( customizeWikiEditor );
		}
	} );
//}
var wikiEditor = $( '#wpTextbox1' );

var customizeWikiEditor = function () {
     // https://www.mediawiki.org/wiki/Extension:WikiEditor/Toolbar_customization

    
    $( '#wpTextbox1' ).wikiEditor( 'removeFromToolbar', {
        'section': 'characters'
    })
    
        var literature2 = {};
        var figures = {};
        var formulas = {};
        //var media = {};
        var listings = {};
        var tasks = {};
        var tables = {};

        if ( loop_literature !== "undefined" ) {
            literature2 = { 'literature': {
                'layout': 'characters',
                'labelMsg': 'loopwikieditor-references-literature',
                'characters': []
            }}
        }
        console.log(typeof(media), literature2);
        figures = {'figures': {
            'layout': 'characters',
            'labelMsg': 'loopwikieditor-references-figures',
            'characters': []
        }}
        formulas = {'formulas': {
            'layout': 'characters',
            'labelMsg': 'loopwikieditor-references-formulas',
            'characters': []
        }}
        var media = {'media': {
            'layout': 'characters',
            'labelMsg': 'loopwikieditor-references-media',
            'characters': []
        }}
        listings = {'listings': {
            'layout': 'characters',
            'labelMsg': 'loopwikieditor-references-listings',
            'characters': []
        }}
        tasks = {'tasks': {
            'layout': 'characters',
            'labelMsg': 'loopwikieditor-references-tasks',
            'characters': []
        }}
        tables = {'tables': {
            'layout': 'characters',
            'labelMsg': 'loopwikieditor-references-tables',
            'characters': []
        }}



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
    } )
    $( '#wpTextbox1' ).wikiEditor( 'addToToolbar', {
        'section': 'references',
        'pages': media // nur eins geht gleichzeitig
    } )
    wikiEditor.wikiEditor( 'addToToolbar', {
        
      
    } )

}

wikiEditor.on( 'wikiEditor-toolbar-buildSection-referencess', function (event, section) {
    
    jQuery.each( loop_literature, function( $key, $val ) {
        section.pages.literature.characters.push( 
            { label: $key + " - " + $val + "", action: { type: 'encapsulate', options: { pre: '<cite>'+$key+'</cite>' } } }
        );
    });

});