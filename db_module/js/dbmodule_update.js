/**
 * @file
 * Contains the definition of the behavior js for dbmodule_update.js.
 */

/**
 * Function LoginFirstModal launches a modal if the user is not logged in and
 * the Updated/Add Entry or Delete Entry is clicked. Handling of modal
 * and additional modal login button which redirects user to login page
 */
function LoginFirstModal()
{
	
	event.preventDefault();
	event.stopImmediatePropagation();
	var titleCheck  = jQuery('#btn-group-custom > input:focus').text();

  if (titleCheck == 'Update Entry')
  {
    jQuery('[name ="button_1"]').attr('data-toggle', 'modal');
		jQuery('[name ="button_1"]').attr('data-content','');
		jQuery('[name ="button_1"]').attr('data-container','');
		jQuery('[name ="button_1"]').attr('data-placement','');
		jQuery('#loginModalCenter').modal('show');
	}
  else
  {
		jQuery('#link-btn').attr('data-toggle','modal');
		jQuery('#link-btn').attr('data-content','');
		jQuery('#link-btn').attr('data-container','');
		jQuery('#link-btn').attr('data-placement','');
		jQuery('#loginModalCenter').modal('show');
	}

  jQuery('#login-page').click(function (event)
    {
	    jQuery('#loginModalCenter').modal('hide');
	    var url = Drupal.url('user/login');
	    jQuery('#db-module-updateform').attr('action', url);
	    jQuery('#db-module-updateform').submit();
	    window.location.href = url;
		});
}
/**
 * Function goBack redirect user to list page if go back button is clicked
 * in the modal
 */
function goBack()
{
	event.preventDefault();
	event.stopImmediatePropagation();
	jQuery('#loginModalCenter').modal('hide');
	var url = Drupal.url('modules/db_module/list');
	jQuery('#db-module-updateform').attr('action', url);
	jQuery('#db-module-updateform').submit();
}

// Begin Drupal JavaScript/jQuery deceleration
(function ($, Drupal, drupalSettings, console, Proxy, Reflect) {
  /**
   * Attaches the JS test behavior for db_module.
   */
  Drupal.behaviors.db_module_update =
  {
	
    attach: function (context, settings, drupalSettings, console, Proxy, Reflect)
    {
      // Remove the button class 'button' from Back to List button element
      $('#edit-back').removeClass('button');
      // Remove the button class 'button' from Add Entry or Update Entry (they have same id/class attributes) button element
      $('[name ="button_1"]').removeClass('button');
      // Remove the button class 'button' from Delete Entry button element
      $('#link-btn').removeClass('button');
      // Remove the list class 'form-select' from gender and state option lists
      $('#edit-gender,#edit-state').removeClass('form-select');

      // Enable bootstrap popover behavior and make it appear for Update/Delete/Add Entry buttons
      // with Mouseover, mouseleave, and hover
      $('[data-toggle="popover"]').popover();

      // Enable popover for button Add Entry or Update Entry (they have same id/class attributes) button element
      $('[name ="button_1"]').popover({
			  container: 'body'
      });
      // mouseover and hover behavior, trigger popover
		  $('[name ="button_1"]').on('mouseover hover',function(event){
			   $(this).popover('show');
		  });
      // Hide it on mouse leave
		  $('[name ="button_1"]').on('mouseleave',function(event){
			  $(this).popover('hide');
		  });

      // Enable popover for button Delete Entry button element
      $('#link-btn').popover({
        container: 'body'
      });

      // mouseover and hover behavior, trigger popover
		  $('#link-btn').on('mouseover hover',function(event){
			 $(this).popover('show');
		  });
      // Hide it on mouse leave
		  $('#link-btn').on('mouseleave',function(event){
			 $(this).popover('hide');
		  });
		
		
    }	
  };	
})(jQuery, Drupal, drupalSettings, console, Proxy, Reflect);

