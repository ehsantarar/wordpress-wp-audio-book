jQuery(function($){
    /*
     * Select/Upload image(s) event
     */
    $('body').on('click', '.wab_upload_audio_button', function(e){
        e.preventDefault();

            var button = $(this),
                custom_uploader = wp.media({
            title: 'Insert Audio',
            library : {
                // uncomment the next line if you want to attach image to the current post
                // uploadedTo : wp.media.view.settings.post.id, 
                type : 'audio'
            },
            button: {
                text: 'Use this Audio' // button label text
            },
            multiple: false // for multiple image selection set to true
        }).on('select', function() { // it also has "open" and "close" events 
            var attachment = custom_uploader.state().get('selection').first().toJSON();
			console.log(attachment);
            $(button).removeClass('button').html('<label>Audio</label><input type="text" name="audio[]" class="true_pre_image" value="' + attachment.url + '" style="max-width:100%;display:block;" />').next().val(attachment.id).next().show();
            /* if you sen multiple to true, here is some code for getting the image IDs
            var attachments = frame.state().get('selection'),
                attachment_ids = new Array(),
                i = 0;
            attachments.each(function(attachment) {
                attachment_ids[i] = attachment['id'];
                console.log( attachment );
                i++;
            });
            */
        })
        .open();
    });

    /*
     * Remove image event
     */
    $('body').on('click', '.wab_remove_audio_button', function(){
        $(this).hide().prev().val('').prev().addClass('button').html('Upload Audio');
        return false;
    });

});