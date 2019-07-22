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
        //console.log(getUrlParam( 'itemtitle' ), decodeURIComponent( getUrlParam( 'itemtitle' )));
        updateFields( getUrlParam( 'itemType' ) )
        checkKeyValue()
    }

    $('#itemType').on("change", function() {
        $type = $(this).val();
        updateFields( $type ) 
    })

    function updateFields( $type ) {

        $literatureTypesJSON = {
            "article": {
                "required": [ "itemKey", "author", "itemtitle", "journal", "year" ],
                "optional": [ "volume", "number", "pages", "month", "note", "url" ]
            },
            "book": {
                "required": [ "itemKey", "author", "editor", "itemtitle", "publisher", "year" ],
                "optional": [ "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url" ]
            },
            "booklet": {
                "required": [ "itemKey", "itemtitle" ],
                "optional": [ "author", "howpublished", "address", "month", "year", "note", "url" ]
            },
            "conference": {
                "required": [ "itemKey", "author", "itemtitle", "booktitle", "year" ],
                "optional": [ "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" ]
            },
            "inbook": {
                "required": [ "itemKey", "author", "editor", "itemtitle", "chapter", "pages", "publisher", "year" ],
                "optional": [ "volume", "number", "series", "type", "address", "edition", "month", "note", "url" ]
            },
            "incollection": {
                "required": [ "itemKey", "author", "itemtitle", "booktitle", "publisher", "year" ],
                "optional": [ "editor", "volume", "number", "series", "type", "chapter", "pages", "address", "edition", "month", "note", "url" ]
            },
            "inproceedings": {
                "required": [ "itemKey", "author", "itemtitle", "booktitle", "year" ],
                "optional": [ "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" ]
            },
            "manual": {
                "required": [ "itemKey", "address", "itemtitle", "year" ],
                "optional": [ "author", "organization", "edition", "month", "note", "url" ]
            },
            "mastersthesis": {
                "required": [ "itemKey", "author", "itemtitle", "school", "year" ],
                "optional": [ "type", "address", "month", "note", "url" ]
            },
            "misc": {
                "required": [ "itemKey" ],
                "optional": [ "author", "itemtitle", "howpublished", "month", "year", "note", "url" ]
            },
            "phdthesis": {
                "required": [ "itemKey", "author", "itemtitle", "school", "year" ],
                "optional": [ "type", "address", "month", "note", "url" ]
            },
            "proceedings": {
                "required": [ "itemKey", "itemtitle", "year" ],
                "optional": [ "editor", "volume", "number", "series", "address", "month", "organization", "publisher", "note", "url" ]
            },
            "techreport": {
                "required": [ "itemKey", "author", "itemtitle", "institution", "year" ],
                "optional": [ "type", "note", "number", "address", "month", "url" ]
            },
            "unpublished": {
                "required": [ "itemKey", "author", "itemtitle", "note" ],
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
                //$("#keymsg").hide()
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