(function($){
  $(document).ready(function(){

    var lastFieldToAdd;
    $("#modal-muruca-container")
        .on('shown.bs.modal', function(e) {
            e.preventDefault();
            var button = $(e.relatedTarget);
            lastFieldToAdd = jQuery(button).parents("[data-key=" + button.data('key') + "]");
            $.ajax({
                url: options.ajaxurl,
                data: {
                    post_type_parent: button.data('parent'),
                    post_type_child: button.data('child'),
                    field_key: button.data('key'),
                    label: button.data('label'),
                    subtype: button.data('subtype'),
                    postid: button.data('postid'),
                    action: 'prepare_related_object',
                    edit: button.data('edit')
                },
                success: function(html) {
                    var modal = $('#modal-addPostObject');
                    modal.find('.modal-body').html(html);
                    acf.do_action('append', modal);
                    modal.find('.modal-title').html(options.create_new + " " + button.data('label'));                    
                    return false;
                }
            })
        })
        .on('hidden.bs.modal', function(e) {
            e.preventDefault();
            var modal = $("#modal-addPostObject");
            var title = modal.find('.modal-title');
            modal.find('.modal-body').html('<span class="acf-spinner is-active"></span>');
            title.html(title.data('default'));
            var submit = $("#publishing-action");
            submit.find('#publish').removeClass('disabled button-disabled button-primary-disabled');
            submit.find('span.spinner').removeClass('is-active');
            e.stopPropagation();
            return false;
        });
        
    $("body").on('submit', ".acf-form", function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      var form = $(this);
      $.ajax({
        url: options.ajaxurl + '?action=save_related_object',
        data: form.serialize(),
        success: function(data) {
          var modal = $('#modal-addPostObject');
          var result = JSON.parse(data);
          modal.modal('hide');
          var title = modal.find('.modal-title');
          title.html(title.data('default'));
          var $select = lastFieldToAdd;
          var $listLeft = $select.find('div.choices ul.acf-bl.list');
          var $listRight = $select.find('div.values ul.acf-bl.list');
          var buttons = '<a href="#" class="acf-icon -minus small dark" data-name="remove_item"></a><a href="#" class="acf-icon -pencil small dark" data-toggle="modal" data-target="#modal-addPostObject" data-name="edit_item"></a>';
          
          if( $listRight.length <= 0 ){
            $select.find('.select2-selection__rendered').html(result.title);
            $select.find('.select2-hidden-accessible').append('<option value="' + result.post_id  + '">' + result.title + '</option>');
            /*if( $select.find('.select2-hidden-accessible').data("multiple") === 0){
              $select.find(".acf-actions .acf-button").hide();
            }*/
          }
          if (result.success == true && result.edit == "") {
            $listLeft.find('p').remove();
            $listLeft.prepend('<li><span class="acf-rel-item disabled" data-id="' + result.post_id + '">' + result.title + '</span></li>');
            $listRight.prepend('<li><input type="hidden" name="'+result.name+'" value="' + result.post_id + '"><span class="acf-rel-item" data-id="' + result.post_id + '">' + result.title + buttons + "</span></li>");
          } else if( result.edit == "edit" ){
            $listRight.find('[data-id="' + result.post_id +'"]').html(result.title + buttons);
          } else {
            console.log(result);
          }
          e.stopPropagation();

          return false;
        }
      });
    });     //acf form submit   
  
  /* FIX COMPATIBILITY ISSUE WITH BOOTSTRAP*/     
    $("#contextual-help-link").click(function () {
        $("#contextual-help-wrap").css("cssText", "display: block !important; visibility: visible !important;");
    });
    $("#show-settings-link").click(function () {
        $("#screen-options-wrap").css("cssText", "display: block !important; visibility: visible !important;");
    });
    if($('.hide-calendar').length > 0){
      $('.acf-ui-datepicker').css('display', 'none');
    } 
});       //end document ready
       
})(jQuery);

