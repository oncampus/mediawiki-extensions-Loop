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

    
    wikiEditor.wikiEditor( 'removeFromToolbar', {
        section: 'characters'
    } );
    
    wikiEditor.wikiEditor( 'addToToolbar', {
        sections: {
            'loop': {
                type: 'toolbar', 
                labelMsg: 'loopwikieditor-section-loop',
                
                groups: {
                    list: {
                        tools: {
                            'loop-objects': {
                                labelMsg: 'loopwikieditor-loop-object',
                                type: 'select',
                                list: {
                                    'loop-figure': {
                                        labelMsg: 'loop_figure-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_figure>',
                                                post: '</loop_figure>'
                                            }
                                        }
                                    },
                                    'loop-formula': {
                                        labelMsg: 'loop_formula-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_formula title="" description="">',
                                                post: '</loop_formula>'
                                            }
                                        }
                                    },
                                    'loop-listing': {
                                        labelMsg: 'loop_listing-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_listing title="" description="">',
                                                post: '</loop_listing>'
                                            }
                                        }
                                    },
                                    'loop-table': {
                                        labelMsg: 'loop_table-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_table title="" description="">',
                                                post: '</loop_table>'
                                            }
                                        }
                                    },
                                    'loop-task': {
                                        labelMsg: 'loop_task-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_task title="" description="">',
                                                post: '</loop_task>'
                                            }
                                        }
                                    },
                                    'loop-media': {
                                        labelMsg: 'loop_media-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-rollover': {
                                        labelMsg: 'loopwikieditor-loop-object-media-rollover',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="rollover" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-mvideo': {
                                        labelMsg: 'loopwikieditor-loop-object-media-video',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="video" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-interaction': {
                                        labelMsg: 'loopwikieditor-loop-object-media-interaction',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="interaction" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-click': {
                                        labelMsg: 'loopwikieditor-loop-object-media-click',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="click" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-maudio': {
                                        labelMsg: 'loopwikieditor-loop-object-media-audio',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="audio" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-animation': {
                                        labelMsg: 'loopwikieditor-loop-object-media-animation',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="animation" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-simulation': {
                                        labelMsg: 'loopwikieditor-loop-object-media-simulation',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="simulation" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-dragdrop': {
                                        labelMsg: 'loopwikieditor-loop-object-media-dragdrop',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="dragdrop" title="" description="">',
                                                post: '</loop_media>'
                                            }
                                        }
                                    }


                                }
                            }, // end of objects

                            
                            'loop-areas': {
                                labelMsg: 'loopwikieditor-loop-areas',
                                type: 'select',
                                list: {
                                    'looparea-area': {
                                        labelMsg: 'loopwikieditor-loop-area-area',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="area">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-custom': {
                                        labelMsg: 'loopwikieditor-loop-area-custom',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area icon="icon.png" icontext="Text">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-annotation': {
                                        labelMsg: 'looparea-name-annotation',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="annotation">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-arrangement': {
                                        labelMsg: 'looparea-name-arrangement',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="arrangement">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-citation': {
                                        labelMsg: 'looparea-name-citation',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="citation">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-definition': {
                                        labelMsg: 'looparea-name-definition',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="definition">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-example': {
                                        labelMsg: 'looparea-name-example',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="example">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-exercise': {
                                        labelMsg: 'looparea-name-exercise',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="exercise">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-experiment': {
                                        labelMsg: 'looparea-name-experiment',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="experiment">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-formula': {
                                        labelMsg: 'looparea-name-formula',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="formula">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-important': {
                                        labelMsg: 'looparea-name-important',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="important">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-indentation': {
                                        labelMsg: 'looparea-name-indentation',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="indentation">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-law': {
                                        labelMsg: 'looparea-name-law',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="law">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-learningobjectives': {
                                        labelMsg: 'looparea-name-learningobjectives',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="learningobjectives">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-markedsentence': {
                                        labelMsg: 'looparea-name-markedsentence',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="markedsentence">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-norm': {
                                        labelMsg: 'looparea-name-norm',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="norm">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-notice': {
                                        labelMsg: 'looparea-name-notice',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="notice">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-practice': {
                                        labelMsg: 'looparea-name-practice',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="practice">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-question': {
                                        labelMsg: 'looparea-name-question',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="question">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-reflection': {
                                        labelMsg: 'looparea-name-reflection',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="reflection">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-sourcecode': {
                                        labelMsg: 'looparea-name-sourcecode',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="sourcecode">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-summary': {
                                        labelMsg: 'looparea-name-summary',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="summary">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-task': {
                                        labelMsg: 'looparea-name-task',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="task">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-timerequirement': {
                                        labelMsg: 'looparea-name-timerequirement',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="timerequirement">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },
                                    'looparea-websource': {
                                        labelMsg: 'looparea-name-websource',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_area type="websource">',
                                                post: '</loop_area>'
                                            }
                                        }
                                    }

                                }
                            }, // end of areas
                            
                            
                            'loop-snippets': {
                                labelMsg: 'loopwikieditor-loop-snippets',
                                type: 'select',
                                list: {
                                    'loopsnippets-author': {
                                        labelMsg: 'loopwikieditor-loop-snippets-author',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: "'''Dr. Vorname Nachname'''<br />\n\n[[File:vorname_nachname.jpg|85px]]<br />\n\n'''Hochschule:'''<br/>\n\nBeispielhochschule<br/>\n\n<br/>\n\n'''Tätigkeitsbereich:'''<br />\n\nBeispieltätigkeit\n\n<br/>\n\n<br/>\n\n'''Kontakt:'''<br/>\n\nAdresse<br />\n\n Musterstrasse 123<br />\n\n12345 Musterstadt, Deutschland",
                                            }
                                        }
                                    },
                                    'loopsnippets-zip': {
                                        labelMsg: 'loopwikieditor-loop-snippets-zip',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_zip file=".zip" start=".html" width="px" height="px" scale="false"></loop_zip>'
                                            }
                                        }
                                    },
                                    'loopsnippets-spoiler': {
                                        labelMsg: 'loopwikieditor-loop-snippets-spoiler',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_spoiler text="Button" type="transparent">',
                                                peri: 'Text',
                                                post: '</loop_spoiler>'
                                            }
                                        }
                                    },
                                    'loopsnippets-paragraph': {
                                        labelMsg: 'loopwikieditor-loop-snippets-paragraph',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_paragraph copyright="Author">',
                                                peri: 'Text',
                                                post: '</loop_paragraph>'
                                            }
                                        }
                                    },
                                    'loopsnippets-toc': {
                                        labelMsg: 'loopwikieditor-loop-snippets-toc',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_toc></loop_toc>'
                                            }
                                        }
                                    },
                                    'loopsnippets-literature': {
                                        labelMsg: 'loopwikieditor-loop-snippets-literature',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_literature>\n<literature>Key</literature>\n<literature>Key+2</literature>\n</loop_literature>',
                                                peri: 'Text',
                                                post: '</loop_literature>'
                                            }
                                        }
                                    },
                                    'loopsnippets-math': {
                                        labelMsg: 'loopwikieditor-loop-snippets-math',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<math>',
                                                peri: 'x^2',
                                                post: '</math>'
                                            }
                                        }
                                    },
                                    'loopsnippets-print': {
                                        labelMsg: 'loopwikieditor-loop-snippets-print',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_print button="true">',
                                                peri: 'Druckbereich',
                                                post: '</loop_print>'
                                            }
                                        }
                                    },
                                    'loopsnippets-noprint': {
                                        labelMsg: 'loopwikieditor-loop-snippets-noprint',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_noprint button="true">',
                                                peri: 'Druckbereich',
                                                post: '</loop_noprint>'
                                            }
                                        }
                                    },
                                }
                            } // end of snippets

                        } // end of tools
                    }
                }
            },
            'references': {
                type: 'booklet', 
                labelMsg: 'loopwikieditor-section-references',
                
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
