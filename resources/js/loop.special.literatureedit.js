$( document ).ready( function () {
    checkKeyValue()

    // fill out fields from url parameters if the entry was not saved
    if ( $('#literature-error').length > 0 ) {
        $('.literature-field input').each( function () {
            $id = $(this).attr("id");
            var param = getUrlParam( $id ).replace('+', '%20');
            $(this).val( decodeURIComponent( param ) );
        })
        $('#itemType').val( decodeURIComponent( getUrlParam( 'itemType' )) )
        updateFields( getUrlParam( 'itemType' ) )
        checkKeyValue()
    }
    if ( getUrlParam( 'edit' ) != '' ) {
        $('.literature-field input').each( function () {
            $id = $(this).attr("id");
            $val = editValues[$id];
            if ( $val !== undefined ) {
                $(this).val( $val.replace(/&quot;/g, '"') );
            }
        })
        checkKeyValue()
    }

    $('#itemType').on("change", function() {
        $type = $(this).val();
        updateFields( $type ) 
        checkKeyValue()
    })

    function updateFields( $type ) {

        $literatureTypesJSON = {
            "article": {
                "required": [ "itemKey", "author", "itemTitle", "journal", "year" ],
                "optional": [ "volume", "number", "pages", "month", "note", "url", "doi" ]
            },
            "book": {
                "required": [ "itemKey", "author", "editor", "itemTitle", "publisher", "year" ],
                "optional": [ "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url", "doi" ]
            },
            "booklet": {
                "required": [ "itemKey", "itemTitle" ],
                "optional": [ "author", "howpublished", "address", "month", "year", "note", "url", "doi", "doi"  ]
            },
            "conference": {
                "required": [ "itemKey", "author", "itemTitle", "booktitle", "year" ],
                "optional": [ "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" ]
            },
            "inbook": {
                "required": [ "itemKey", "author", "editor", "itemTitle", "chapter", "pages", "publisher", "year" ],
                "optional": [ "volume", "number", "series", "type", "address", "edition", "month", "note", "url", "doi" ]
            },
            "incollection": {
                "required": [ "itemKey", "author", "itemTitle", "booktitle", "publisher", "year" ],
                "optional": [ "editor", "volume", "number", "series", "type", "chapter", "pages", "address", "edition", "month", "note", "url", "doi" ]
            },
            "inproceedings": {
                "required": [ "itemKey", "author", "itemTitle", "booktitle", "year" ],
                "optional": [ "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url", "doi" ]
            },
            "manual": {
                "required": [ "itemKey", "address", "itemTitle", "year" ],
                "optional": [ "author", "organization", "edition", "month", "note", "url", "doi" ]
            },
            "mastersthesis": {
                "required": [ "itemKey", "author", "itemTitle", "school", "year" ],
                "optional": [ "type", "address", "month", "note", "url" ]
            },
            "misc": {
                "required": [ "itemKey" ],
                "optional": [ "author", "itemTitle", "howpublished", "month", "year", "note", "url", "doi" ]
            },
            "phdthesis": {
                "required": [ "itemKey", "author", "itemTitle", "school", "year" ],
                "optional": [ "type", "address", "month", "note", "url" ]
            },
            "proceedings": {
                "required": [ "itemKey", "itemTitle", "year" ],
                "optional": [ "editor", "volume", "number", "series", "address", "month", "organization", "publisher", "note", "url", "doi" ]
            },
            "techreport": {
                "required": [ "itemKey", "author", "itemTitle", "institution", "year" ],
                "optional": [ "type", "note", "number", "address", "month", "url", "doi" ]
            },
            "unpublished": {
                "required": [ "itemKey", "author", "itemTitle", "note" ],
                "optional": [ "month", "year", "url" ]
            }
        };
        
        $typeFields = $literatureTypesJSON[ $type ];
        $('#required-row .literature-field').each( function () {
            $id = $(this).find("input").attr("id");
            if ( jQuery.inArray( $id, $typeFields["required"] ) < 0 ) { // not in required row
                if ( jQuery.inArray( $id, $typeFields["optional"] ) < 0 ) { // not in optional row, disable
                    $(this).detach().appendTo("#disabled-row");
                    doDisabled($(this));
                } else { // in optional row
                    $(this).detach().appendTo("#optional-row");
                    doOptional($(this));
                }
            } // else stay in required
        })
        $('#optional-row .literature-field').each( function () {
            $id = $(this).find("input").attr("id");
            if ( jQuery.inArray( $id, $typeFields["optional"] ) < 0 ) { // not in optional row
                if ( jQuery.inArray( $id, $typeFields["required"] ) < 0 ) { // not in required row, disable
                    $(this).detach().appendTo("#disabled-row");
                    doDisabled($(this));
                } else { // in required row
                    $(this).detach().appendTo("#required-row");
                    doRequired($(this));
                }
            } // else stay in optional
        })
        $('#disabled-row .literature-field').each( function () {
            $id = $(this).find("input").attr("id");
            if ( jQuery.inArray( $id, $typeFields["optional"] ) >= 0 ) { // in optional row
                
                $(this).detach().appendTo("#optional-row");
                doOptional($(this));
            } else if ( jQuery.inArray( $id, $typeFields["required"] ) >= 0 ) { // in required row
                $(this).detach().appendTo("#required-row");
                doRequired($(this));
             } // else stay in disabled
        })
        
        if ( $typeFields["required"].length == 0 ) {
            $('#required-row').addClass("d-none");
        } else {
            $('#required-row').removeClass("d-none");
        }
    }

    
    $('#itemKey').on("change keyup click", function() {
        checkKeyValue()
    })
    $('#overwrite').on("change", function() {
        checkKeyValue()
    })
    
    function checkKeyValue() {
        $val = $('#itemKey').val();

        if ( jQuery.inArray( $val, $existingKeys ) >= 0  ) {
            $("#overwrite").prop("disabled", false)
            $("#overwrite").parent().removeClass("d-none")
            if ( $("#overwrite").prop("checked") == true ) {
                $("#loopliterature-submit").prop("disabled", false)
            } else { 
                $("#keymsg").show()
                $("#loopliterature-submit").prop("disabled", true)
            }
        } else {
            $("#keymsg").hide()
            $("#loopliterature-submit").prop("disabled", false)
            $("#overwrite").prop("disabled", true)
            $("#overwrite").parent().addClass("d-none")
        }
    }
    
    function doOptional( $this ) {
        $this.removeClass("d-none");
        $this.find("input").prop("required", false);
        $this.find("input").prop("disabled", false);
    }
    function doDisabled( $this ) {
        $this.addClass("d-none");
        $this.find("input").prop("required", false);
        $this.find("input").prop("disabled", true);
    }
    function doRequired( $this ) {
        $this.removeClass("d-none");
        $this.find("input").prop("required", true);
        $this.find("input").prop("disabled", false);
    }
    // returns parameter value
    function getUrlParam ( $param ){
        $results = new RegExp('[\?&]' + $param + '=([^&#]*)').exec( window.location.href );
        if ( $results != null ) {
            return $results[1];
        } else {
            return '';
        }
    }
    
})