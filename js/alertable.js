//
// jquery.alertable.js - Minimal alert, confirmation, and prompt alternatives.
//
// Developed by Cory LaViska for A Beautiful Site, LLC
//
// Licensed under the MIT license: http://opensource.org/licenses/MIT
//
if(jQuery) (function($) {
  'use strict';

  var modal;
  var overlay;
  var errorOKbutton;
  var infoOKbutton;
  var cancelButton;
  var activeElement;

  function show(type, title, message, options) {

    var defer = $.Deferred();

    // Remove focus from the background
    activeElement = document.activeElement;
    activeElement.blur();

    // Remove other instances
    $(modal).add(overlay).remove();

    // Merge options
    options = $.extend({}, $.alertable.defaults, options);

    // Create elements
    modal = $(options.modal).hide();
    overlay = $(options.overlay).hide();
    errorOKbutton = $(options.errorOKbutton);
    infoOKbutton = $(options.infoOKbutton);
    cancelButton = $(options.cancelButton);

    // Add message
    if(options.html)
	{
	
		//	Different layout for different types of alert... This is all basic AH!
		if (type === 'info')
		{
			modal.find('.alertable-message').html('<p class="alert_info_text_class has-text-dark"><br>' + message + '<br><br></p>');
		}
		else if (type === 'error')
		{
			modal.find('.alertable-message').html('<p class="alert_error_text_class">Err: ' + title + '<br><br>' + message + '<br><br></p>');
		}



    }
	else
	{
      modal.find('.alertable-message').text(message);
    }





    // Add prompt
    if(type === 'prompt') {
      modal.find('.alertable-prompt').html(options.prompt);
    } else {
      modal.find('.alertable-prompt').remove();
    }



    //	Add buttons
	//
	//	Need the following styles:
	//	-	Error	:	when stuff goes sideways (very red please)
	//	-	Info	:	general user information popups like "Warehouse already exists" etc (blue themed?)
	//
    if(type === 'error')
	{
		$(modal).find('.alertable-buttons').append(errorOKbutton);
    }
	else if(type === 'info')
	{
		$(modal).find('.alertable-buttons').append(infoOKbutton);
	}



	
	//$(modal).find('.alertable-buttons').append(type === 'alert' ? '' : cancelButton).append(okButton);




    // Add to container
    $(options.container).append(overlay).append(modal);

    // Show it
    options.show.call({
      modal: modal,
      overlay: overlay
    });

    // Set focus
    if(type === 'prompt') {
      // First input in the prompt
      $(modal).find('.alertable-prompt :input:first').focus();
    } else {
      // OK button
      // Do not need the focus to be on the button at all
	  //$(modal).find(':input[type="submit"]').focus();
    }

    // Watch for submit
    $(modal).on('submit.alertable', function(event) {
      var i;
      var formData;
      var values = [];

      event.preventDefault();

      if(type === 'prompt') {
        formData = $(modal).serializeArray();
        for(i = 0; i < formData.length; i++) {
          values[formData[i].name] = formData[i].value;
        }
      } else {
        values = null;
      }

      hide(options);
      defer.resolve(values);
    });

    // Watch for cancel
    cancelButton.on('click.alertable', function() {
      hide(options);
      defer.reject();
    });

    // Cancel on escape
    $(document).on('keydown.alertable', function(event) {
      if(event.keyCode === 27) {
        event.preventDefault();
        hide(options);
        defer.reject();
      }
    });

    // Prevent focus from leaving the modal
    $(document).on('focus.alertable', '*', function(event) {
      if(!$(event.target).parents().is('.alertable')) {
        event.stopPropagation();
        event.target.blur();
        $(modal).find(':input:first').focus();
      }
    });

    return defer.promise();
  }



  function hide(options) {
    // Hide it
    options.hide.call({
      modal: modal,
      overlay: overlay
    });

    // Remove bindings
    $(document).off('.alertable');
    modal.off('.alertable');
    cancelButton.off('.alertable');

    // Restore focus
    activeElement.focus();
  }



  // Defaults
  $.alertable = {
    // Show an info dialog box
    error: function(title ,message, options) {
      return show('error', title, message, options);
    },


    info: function(title ,message, options) {
      return show('info', title, message, options);
    },


    // Show a confirmation
    confirm: function(title ,message, options) {
      return show('confirm', title, message, options);
    },


    // Show a prompt
    prompt: function(title ,message, options) {
      return show('prompt', title, message, options);
    },



    defaults: {
      // Preferences
      container: 'body',
      html: true,	//	HTML is always enabled!

      // Templates
      cancelButton: '<button class="alertable-cancel" type="button">Cancel</button>',
      errorOKbutton: '<button class="button alert_error_btn_class is-fullwidth" type="submit">OK</button>',
      infoOKbutton: '<button class="button alert_info_btn_class is-fullwidth" type="submit">OK</button>',
      overlay: '<div class="alertable-overlay"></div>',
      prompt: '<input class="alertable-input" type="text" name="value">',
      modal:
        '<form class="alertable">' +
        '<div class="alertable-message"></div>' +
        '<div class="alertable-prompt"></div>' +
        '<div class="alertable-buttons"></div>' +
        '</form>',

      // Hooks
      hide: function() {
        $(this.modal).add(this.overlay).fadeOut(50);
      },
      show: function() {
        $(this.modal).add(this.overlay).fadeIn(50);
      }
    }


  };
 

})(jQuery);