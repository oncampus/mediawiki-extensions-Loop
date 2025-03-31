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
                                                pre: '<loop_figure title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "[[File:Filename.png|alt=]]",
                                                post: '</loop_figure>'
                                            }
                                        }
                                    },
                                    'loop-formula': {
                                        labelMsg: 'loop_formula-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_formula title="" description="" copyright="" show_copyright="false" index="true"><math>',
                                                peri: "x^2",
                                                post: '</math></loop_formula>'
                                            }
                                        }
                                    },
                                    'loop-listing': {
                                        labelMsg: 'loop_listing-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_listing title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_listing>'
                                            }
                                        }
                                    },
                                    'loop-table': {
                                        labelMsg: 'loop_table-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_table title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_table>'
                                            }
                                        }
                                    },
                                    'loop-task': {
                                        labelMsg: 'loop_task-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_task title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_task>'
                                            }
                                        }
                                    },
                                    'loop-media': {
                                        labelMsg: 'loop_media-name',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-rollover': {
                                        labelMsg: 'loopwikieditor-loop-object-media-rollover',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="rollover" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-mvideo': {
                                        labelMsg: 'loopwikieditor-loop-object-media-video',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="video" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-interaction': {
                                        labelMsg: 'loopwikieditor-loop-object-media-interaction',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="interaction" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-click': {
                                        labelMsg: 'loopwikieditor-loop-object-media-click',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="click" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-maudio': {
                                        labelMsg: 'loopwikieditor-loop-object-media-audio',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="audio" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-animation': {
                                        labelMsg: 'loopwikieditor-loop-object-media-animation',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="animation" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-simulation': {
                                        labelMsg: 'loopwikieditor-loop-object-media-simulation',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="simulation" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
                                                post: '</loop_media>'
                                            }
                                        }
                                    },
                                    'loop-dragdrop': {
                                        labelMsg: 'loopwikieditor-loop-object-media-dragdrop',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_media type="dragdrop" title="" description="" copyright="" show_copyright="false" index="true">',
                                                peri: "Content",
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: '<loop_toc/>',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
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
                                                peri: 'Text',
                                                post: '</loop_area>'
                                            }
                                        }
                                    },

                                }
                            }, // end of areas


                            'loop-contents': {
                                labelMsg: 'loopwikieditor-loop-contents',
                                type: 'select',
                                list: {
                                    'loopmedia-video': {
                                        labelMsg: 'loopwikieditor-loop-content-video',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_video source="',
                                                peri: 'video.mp4',
                                                post: '"/>'
                                            }
                                        }
                                    },
                                    'loopmedia-audio': {
                                        labelMsg: 'loopwikieditor-loop-content-audio',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_audio source="',
                                                peri: 'audio.mp3',
                                                post: '"/>'
                                            }
                                        }
                                    },
                                    'loopmedia-youtube': {
                                        labelMsg: 'loopwikieditor-loop-content-youtube',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '{{#ev:youtube|',
                                                peri: 'ID',
                                                post: '}}'
                                            }
                                        }
                                    },
                                    'loopmedia-h5p': {
                                        labelMsg: 'loopwikieditor-loop-content-h5p',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<h5p id="',
                                                peri: 'ID',
                                                post: '" width="800px" height="500px"/>'
                                            }
                                        }
                                    },
                                    'loopmedia-panopto': {
                                        labelMsg: 'loopwikieditor-loop-content-panopto',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<panopto id="',
                                                peri: 'ID',
                                                post: '" width="´720px" height="405px"/>'
                                            }
                                        }
                                    },
                                    'loopmedia-learningapps': {
                                        labelMsg: 'loopwikieditor-loop-content-learningapps',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<learningapp app="',
                                                peri: 'ID',
                                                post: '" width="800px" height="500px"/>'
                                            }
                                        }
                                    },
									'loopmedia-padlet': {
										labelMsg: 'loopwikieditor-loop-content-padlet',
										action: {
											type: 'encapsulate',
											options: {
												pre: '<padlet id="',
												peri: 'ID',
												post: '" width="800px" height="500px"/>'
											}
										}
									},
									'loopmedia-taskcard': {
										labelMsg: 'loopwikieditor-loop-content-taskcard',
										action: {
											type: 'encapsulate',
											options: {
												pre: '<taskcard id="ID" token="',
												peri: 'TOKEN',
												post: '" width="800px" height="500px"/>'
											}
										}
									},
                                    'loopmedia-prezi': {
                                        labelMsg: 'loopwikieditor-loop-content-prezi',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<prezi id="',
                                                peri: 'ID',
                                                post: '" width="800px" height="500px"/>'
                                            }
                                        }
                                    },
                                    'loopmedia-slideshare': {
                                        labelMsg: 'loopwikieditor-loop-content-slideshare',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<slideshare id="',
                                                peri: 'ID',
                                                post: '" width="800px" height="500px"/>'
                                            }
                                        }
                                    },
                                    'loopmedia-quizlet': {
                                        labelMsg: 'loopwikieditor-loop-content-quizlet',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<quizlet id="',
                                                peri: 'ID',
                                                post: '" width="800px" height="500px"/>'
                                            }
                                        }
                                    },
                                }
                            },

                            'loop-snippets': {
                                labelMsg: 'loopwikieditor-loop-snippets',
                                type: 'select',
                                list: {
                                    'loopsnippets-zip': {
                                        labelMsg: 'loopwikieditor-loop-snippets-zip',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_zip file=".zip" start=".html" width="800px" height="500px" scale="false"/>'
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
                                                pre: '<loop_toc/>'
                                            }
                                        }
                                    },
                                    'loopsnippets-literature': {
                                        labelMsg: 'loopwikieditor-loop-snippets-literature',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_literature>\n<literature>',
                                                peri: 'Key',
                                                post: '</literature>\n<literature>Key+2</literature>\n</loop_literature>\n'
                                            }
                                        }
                                    },
                                    'loopsnippets-syntaxhighlight': {
                                        labelMsg: 'loopwikieditor-loop-snippets-syntaxhighlight',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<syntaxhighlight lang="xml" line>',
                                                peri: '<code>',
                                                post: '</syntaxhighlight>'
                                            }
                                        }
                                    },
                                    'loopsnippets-accordion': {
                                        labelMsg: 'loopwikieditor-loop-snippets-accordion',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_accordion>\n<loop_row>\n<loop_title>Title 1</loop_title>\n',
                                                peri: 'Text',
                                                post: '\n</loop_row>\n<loop_row>\n<loop_title>Title 2</loop_title>\nText 2\n</loop_row>\n</loop_accordion>\n'
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
                                    'loopsnippets-sidenote': {
                                        labelMsg: 'loopwikieditor-loop-snippets-sidenote',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_sidenote>',
                                                peri: 'Sidenote',
                                                post: '</loop_sidenote>'
                                            }
                                        }
                                    },
                                    'loopsnippets-print': {
                                        labelMsg: 'loopwikieditor-loop-snippets-print',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_print button="true">',
                                                peri: 'Print-only',
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
                                                peri: 'No-print',
                                                post: '</loop_noprint>'
                                            }
                                        }
                                    },
									'loopsnippets-nospeech': {
										labelMsg: 'loopwikieditor-loop-snippets-nospeech',
										action: {
											type: 'encapsulate',
											options: {
												pre: '<loop_nospeech>',
												peri: 'No-speech',
												post: '</loop_nospeech>'
											}
										}
									},
									'loopsnippets-speech': {
										labelMsg: 'loopwikieditor-loop-snippets-speech',
										action: {
											type: 'encapsulate',
											options: {
												pre: '<loop_speech>',
												peri: 'Speech',
												post: '</loop_speech>'
											}
										}
									},
                                    'loopsnippets-screenshot': {
                                        labelMsg: 'loopscreenshot',
                                        action: {
                                            type: 'encapsulate',
                                            options: {
                                                pre: '<loop_screenshot width="700" height="500">',
                                                peri: 'Text',
                                                post: '</loop_screenshot>'
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
