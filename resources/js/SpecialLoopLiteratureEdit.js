$( document ).ready( function () {

    $literatureTypesJSON = {
        "article": {
            "required": [ "key", "author", "title", "journal", "year" ],
            "optional": [ "volume", "number", "pages", "month", "note", "url" ]
        },
        "book": {
            "required": [ "key", "author", "editor", "title", "publisher", "year" ],
            "optional": [ "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url" ]
        },
        "booklet": {
            "required": [ "key", "title" ],
            "optional": [ "author", "howpublished", "address", "month", "year", "note", "url" ]
        },
        "conference": {
            "required": [ "key", "author", "title", "booktitle", "year" ],
            "optional": [ "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" ]
        },
        "inbook": {
            "required": [ "key", "author", "editor", "title", "chapter", "pages", "publisher", "year" ],
            "optional": [ "volume", "number", "series", "type", "address", "edition", "month", "note", "url" ]
        },
        "incollection": {
            "required": [ "key", "author", "title", "booktitle", "publisher", "year" ],
            "optional": [ "editor", "volume", "number", "series", "type", "chapter", "pages", "address", "edition", "month", "note", "url" ]
        },
        "inproceedings": {
            "required": [ "key", "author", "title", "booktitle", "year" ],
            "optional": [ "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" ]
        },
        "manual": {
            "required": [ "key", "address", "title", "year" ],
            "optional": [ "author", "organization", "edition", "month", "note", "url" ]
        },
        "mastersthesis": {
            "required": [ "key", "author", "title", "school", "year" ],
            "optional": [ "type", "address", "month", "note", "url" ]
        },
        "misc": {
            "required": [ "key" ],
            "optional": [ "author", "title", "howpublished", "month", "year", "note", "url" ]
        },
        "phdthesis": {
            "required": [ "key", "author", "title", "school", "year" ],
            "optional": [ "type", "address", "month", "note", "url" ]
        },
        "proceedings": {
            "required": [ "key", "title", "year" ],
            "optional": [ "editor", "volum", "number", "series", "address", "month", "organization", "publisher", "note", "url" ]
        },
        "techreport": {
            "required": [ "key", "author", "title", "institution", "year" ],
            "optional": [ "type", "note", "number", "address", "month", "url" ]
        },
        "unpublished": {
            "required": [ "key", "author", "title", "note" ],
            "optional": [ "month", "year", "url" ]
        }
    };
    
    $('#entryType').on("change", function() {
        $type = $(this).val();
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
    })

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
})