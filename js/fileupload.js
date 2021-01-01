
vc.fileupload = {
    open: function(event) {
        event.preventDefault();
        var parent = $(event.target).parent();
        // Clicking on the image within ajaxUpload (Go one further)
        if (parent.hasClass('ajaxUpload')) {
            parent = parent.parent();
        }
        $('.ajaxUploadSelect', parent).focus().trigger('click');
    },
    
    trigger: function(event) {
        event.preventDefault();
        $('.ajaxUploadSelect', $(event.target).closest('form')).focus().trigger('click');
    },
    
    upload: function(event) {
        var target = $(event.target),
            files = target.context.files,
            ajaxUploadParent = target.parent(),
            ajaxUploadElement = $('.ajaxUpload', ajaxUploadParent),
            ajaxPreviewElement = $('.ajaxUploadPreview', ajaxUploadParent),
            ajaxFileElement = $('.ajaxUploadFile', ajaxUploadParent),
            maxSize = target.data('maxSize'),
            showFilename = ajaxUploadElement.data('filename'),
            showDelete = ajaxUploadElement.data('delete');

        if (window.XMLHttpRequest && window.FormData) {
            // Create a new FormData object.
            var formData = new FormData();
            // Set up the request.
            var xhr = new XMLHttpRequest();

            // Loop through each of the selected files.
            if (files.length > 0) {
                var file = files[0];
                
                // Check the file type.
                if (!file.type.match('image.*')) {
                    alert("%GETTEXT('upload.noimage')%");
                    return;
                }
                
                if (file.size > maxSize) {
                    alert("%GETTEXT('upload.toobig')%");
                    return;
                }
                
                // Add the file to the request.
                formData.append('file', file, file.name);

                var loading = $('<div class="jLoading"></div>');
                ajaxPreviewElement.append(loading);

                // Open the connection.
                xhr.open('POST', ajaxUploadElement.data('path'), true);

                // Set up a handler for when the request finishes.
                xhr.onload = function () {
                    loading.remove();
                    
                    if (xhr.status === 200) {
                        var response = $.parseJSON(xhr.response);
                        if (response.success) {
                            $('img', ajaxPreviewElement).remove();
                            $('span.filename', ajaxPreviewElement).remove();
                            $('a.delete', ajaxPreviewElement).remove();
                            ajaxFileElement.val(response.filename);
                            ajaxPreviewElement.append('<img src="/picture/temp/crop/74/74/' + response.filename + '" />');
                            if (showFilename) {
                                ajaxPreviewElement.append('<span class="filename">' + response.sourcename + '</span>');
                            }
                            if (showDelete) {
                                ajaxPreviewElement.append('<a class="delete" href="#"></a>');
                            }                            
                        } else if(response.message != null) {
                            alert(response.message);
                        } else {
                            alert("%GETTEXT('upload.error')%");
                        }
                    } else {
                        alert("%GETTEXT('upload.error')% [" + xhr.status + "]");
                    }
                };

                // Send the Data.
                xhr.send(formData);
            }
        } else {
            alert("%GETTEXT('upload.incompatible')%");
        }
    },
    
    remove: function(event) {
        event.preventDefault();
        var target = $(event.target),
            ajaxUploadParent = target.closest('.ajaxUploadPreview').parent(),
            ajaxPreviewElement = $('.ajaxUploadPreview', ajaxUploadParent),
            ajaxFileElement = $('.ajaxUploadFile', ajaxUploadParent);
        ajaxFileElement.val('');
        ajaxPreviewElement.empty();
    }
};