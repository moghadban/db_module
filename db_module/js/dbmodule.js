/**
 * @file
 * Contains the definition of the behavior js for db_module.
 */
function setUrlParameter(url, key, value) {
    var key = encodeURIComponent(key),
        value = encodeURIComponent(value);

    var baseUrl = url.split('?')[0],
        newParam = key + '=' + value,
        params = '?' + newParam;

    if (url.split('?')[1] === undefined){ // if there are no query strings, make urlQueryString empty
        urlQueryString = '';
    } else {
        urlQueryString = '?' + url.split('?')[1];
    }

    // If the "search" string exists, then build params from it
    if (urlQueryString) {
        var updateRegex = new RegExp('([\?&])' + key + '[^&]*');
        var removeRegex = new RegExp('([\?&])' + key + '=[^&;]+[&;]?');

        if (value === undefined || value === null || value === '') { // Remove param if value is empty
            params = urlQueryString.replace(removeRegex, "$1");
            params = params.replace(/[&;]$/, "");
            
        } else if (urlQueryString.match(updateRegex) !== null) { // If param exists already, update it
            params = urlQueryString.replace(updateRegex, "$1" + newParam);
            
        } else if (urlQueryString == '') { // If there are no query strings
            params = '?' + newParam;
        } else { // Otherwise, add it to end of query string
            params = urlQueryString + '&' + newParam;
        }
    }

    // no parameter was set so we don't need the question mark
    params = params === '?' ? '' : params;

    return baseUrl + params;
}
// Function update form takes uses jQuery Ajax call to route to update page with proper employee id being passed
// This function is passed when the user clicks on specific entry in list to update it
function updateForm()
 {
  // When the user clicks on an entry to edit/delete, define element 'loading' with bootstrap spinner animation 
  var dots = '<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span><span> Loading</span>';
  var $this = jQuery('#edit-tablesort-table > tbody > tr > td > button.alink:focus');
  $this.text('');
  $this.html(dots);

  // The text of button should have the passed uid, store it in var uid
  var uid = jQuery('#edit-tablesort-table > tbody > tr > td > button.alink:focus').text();

  // Set the above defined/obtained value of passed uid in URL to custom defined uid input defined in DbModuleListForm.php
  jQuery('input#uid').val(uid);
  jQuery('input#uid').attr('value',uid);

  // Get the link of the entry button that's clicked
  var urlLink = jQuery('#edit-tablesort-table > tbody > tr > td > button.alink:focus').attr('data-href');

  // use this to show if proper URL is processed in console
  console.log(urlLink);

  // Get the base URL and add the above obtained var URL to it
  var urlc = window.location.protocol + "//" + window.location.host + urlLink;

  // decode URL so it's passed safely with jQuery Ajax
  var decodedUrl = decodeURIComponent(urlc.replace(/&amp;/g, "&"));
	 
  // use this to show if proper URL is processed in console
  console.log(decodedUrl);

  // Begin the Ajax process, use post, if success, redirect to that URL after saving in form action attribute
  jQuery.ajax({
      method: "POST",
      url: decodedUrl,
      dataType: "html",
      data: jQuery('#db-module-list-form').serialize(),
      success: function (response) // Success function begins
      {
        // use this to show success in console
        console.log("Success");

        // save the URL in the action attribute of list form, and submit form
	      jQuery('#db-module-list-form').attr('action', urlc);
	      jQuery('#db-module-list-form').submit();
			
      },
      error: function (xhr, status, error)
      {
        // use this to show error in console
        console.log("Error");

        // In case an error happens, backup plan B is to redirect using JavaScript
        // This way a failed Ajax call can still redirect user to the proper page
        window.location.href = urlc;
      },
      complete: function (e)
      {
          // When Ajax call completed, show Done in console
          console.log("DONE");
      }
  });
} 


// Begin Drupal JavaScript/jQuery declaration

(function ($, Drupal, drupalSettings)
{

  /**
   * Attaches the JS behaviors to db_module.
   */
  Drupal.behaviors.db_module =
  {
	
    attach: function (context, settings, drupalSettings)
    {

      // Turn default Drupal Pagers into more elegant bootstrap pagination block by removing drupal classes and adding bootstrap ones
      // the <a /> element
        $('#db-module-list-form > nav.pager > ul > li').find('a').addClass('page-link');

      // the <li /> element
        $('#db-module-list-form > nav.pager > ul').find('li').attr('style', 'margin:20px 0px;');
        $('#db-module-list-form > nav.pager > ul').find('li.is-active').removeClass('is-active').addClass('disabled');
        $('#db-module-list-form > nav.pager > ul').find('li.pager__item').removeClass('pager__item').addClass('page-item');
        $('#db-module-list-form > nav.pager > ul').find('li.pager__item--first').removeClass('pager__item--first').addClass('page-item');
        $('#db-module-list-form > nav.pager > ul').find('li.pager__item--last').removeClass('pager__item--last').addClass('page-item');
        $('#db-module-list-form > nav.pager > ul').find('li.pager__item--next').removeClass('pager__item--next').addClass('page-item');
        $('#db-module-list-form > nav.pager > ul').find('li.pager__item--previous').removeClass('pager__item--previous').addClass('page-item');

      // the <ul /> element
        $('#db-module-list-form > nav.pager').find('ul.pager__items.js-pager__items').removeClass('pager__items js-pager__items').addClass('pagination justify-content-center');
        $('#db-module-list-form > nav.pager').find('ul').attr('style', 'margin:20px 0px;');

      // Inject CSS style to table list header so it's more appealing 
        $('#edit-tablesort-table > thead > tr > th > a').addClass('btn btn-outline-light font-weight-bold');
        $('#edit-tablesort-table > thead > tr > th.is-active > a > span.tablesort').addClass('btn btn-dark btn-sm');	

    
	    // Due to saving table headers and cells with rendered html element, which causes the URL to use the html rendered headers
		  // For instance, the header $this->t('Employee Id') will cause the URL to have space encoded as '%20'
		  // which leads to problem in URL decoding especially a search query is passed using custom parameters 
		  // Handling the text has been fixed below with each header check, e.g if header is uid, change it to Employee Id
		  // Assignment of the elements exists in DbModuleListForm.php for more information.

      // uid to Employee Id
		  if ($("#edit-tablesort-table > thead > tr > th:nth-child(2) > a").text()=="uid" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('uid')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(2) > a").html(function(i,t){
          return t.replace("uid","Employee Id");
	      });
      }

      // first_name to First Name
      if ($("#edit-tablesort-table > thead > tr > th:nth-child(3) > a").text()=="first_name" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('first_name')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(3) > a").html(function(i,t){
          return t.replace("first_name","First Name");
	      });
      }

      // last_name to Last Name
      if ($("#edit-tablesort-table > thead > tr > th:nth-child(4) > a").text()=="last_name" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('last_name')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(4) > a").html(function(i,t){
          return t.replace("last_name","Last Name");
	      });
      }

      // gender to Gender
      if ($("#edit-tablesort-table > thead > tr > th:nth-child(5) > a").text()=="gender" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('gender')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(5) > a").html(function(i,t){
          return t.replace("gender","Gender");
	      });
      }

      // street_address to Street Address
      if ($("#edit-tablesort-table > thead > tr > th:nth-child(6) > a").text()=="street_address" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('street_address')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(6) > a").html(function(i,t){
          return t.replace("street_address","Street Address");
		      });
      }

      // city to City
      if ($("#edit-tablesort-table > thead > tr > th:nth-child(7) > a").text() == "city" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('city')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(7) > a").html(function(i,t){
          return t.replace("city","City");
	      });
      }

       // state to State
      if ($("#edit-tablesort-table > thead > tr > th:nth-child(8) > a").text() == "state" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('state')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(8) > a").html(function(i,t){
           return t.replace("state","State");
	      });
      }

      // zip to Zip code
      if ($("#edit-tablesort-table > thead > tr > th:nth-child(9) > a").text() == "zip" || $("#edit-tablesort-table > thead > tr > th.is-active > a:contains('zip')").text())
      {
        $("#edit-tablesort-table > thead > tr > th:nth-child(9) > a").html(function(i,t){
          return t.replace("zip","Zip Code");
	      });
	    }
	
	
        /**
        * If the user clicks on one of the paginations buttons, URL handling of
        * passed page value is required especially with regards to the search query
        * that's entered in the text-box.
        * Example: if the URL ('drupal/modules/db_modues/list?page=1')
        * Adding the text value as ('drupal/modules/db_modues/list?textcheck={some search query}')
        * will over ride the '?page=1' portion, that's why it needs to be handled below
        */
        $('#db-module-list-form > nav > ul > li > a').click(function (event)
        {
	        event.preventDefault();
	        event.stopImmediatePropagation();
	        var linkup = $('#db-module-list-form > nav > ul > li > a:focus').attr('href');
	
	        if(window.location.href.indexOf("textcheck=") > -1)
	        {
			        var url = linkup;
			        console.log('URL='+url);
	        }
	        if(window.location.href.indexOf("textcheck=") === -1)
	        {
		        if (document.getElementById('textcheck').value.length==0)
		        {
			        var url = linkup ;
			        console.log('URL='+url);
		        }
		        if (document.getElementById('textcheck').value.length!=0)
		        {
	            var url = linkup + '?textcheck='+document.getElementById('textcheck').value;
		          console.log('URL='+url);
		        }
	        }
			var finalurl = decodeURIComponent(url);
	        console.log('URL='+finalurl);
			
	      $('input#textcheck').attr('value',document.getElementById('textcheck').value);
          $('input#textcheck').val(document.getElementById('textcheck').value);
          $('#db-module-list-form').attr('action', finalurl);
				  $('#db-module-list-form').submit();
        });
        /**
        * If the user clicks on one of the sorting headers of the table, URL handling of
        * passed page value is required especially with regards to the search query
        * that's entered in the textbox.
        * Example: if the URL ('drupal/modules/db_modues/list?sort=uid&order=asc')
        * Adding the text value as ('drupal/modules/db_modues/list?textcheck={some search query}')
        * will over ride the '?sort=uid&order=asc' portion, that's why it needs to be handled below
        */
        $('#edit-tablesort-table > thead > tr > th > a').click(function (event)
        {
	        event.preventDefault();
	        event.stopImmediatePropagation();
	        var linku = $('#edit-tablesort-table > thead > tr > th > a:focus').attr('href');
          if(window.location.href.indexOf("textcheck=") > -1)
	        {
			      var url = linku;
            console.log('URL=' + url);
          }
	        if(window.location.href.indexOf("textcheck=") === -1)
          {
            if (document.getElementById('textcheck').value.length==0)
		        {
			        var url = linku;
			        console.log('URL='+url);
		        }
		        if (document.getElementById('textcheck').value.length!=0)
		        {
	            var url = decodeURIComponent(linku + '?textcheck='+document.getElementById('textcheck').value);
		          console.log('URL='+url);
		        }
	        }
			var finalurl = decodeURIComponent(url);
	        console.log('URL='+finalurl);
	      $('input#textcheck').attr('value',document.getElementById('textcheck').value);
          $('input#textcheck').val(document.getElementById('textcheck').value);
          $('#db-module-list-form').attr('action', finalurl);
		  $('#db-module-list-form').submit();
        });

			   /**
        * if the user enter a search query the following will happen
        * The label will show the typed query as the user types with loading effects
        * If the search button is clicked URL handling of
        * passed page value or sort query is required especially with regards to the search query
        * that's entered in the text-box.
        * Example: if the URL ('drupal/modules/db_modues/list?sort=uid&order=asc&page=3')
        * Adding the text value as ('drupal/modules/db_modues/list?textcheck={some search query}')
        * will over ride the '??sort=uid&order=asc&page=3' portion, that's why it needs to be handled below
        */
        var dots = '<span class="small_txt_loading_dot"  style="overflow: hidden"> <span class="loader__dot"> . </span><span class="loader__dot"> . </span><span class="loader__dot"> . </span></span><br>';					
        $('input#textcheck').keyup(function (event)
        {

	        var textcheck = event.target.value;
	        // If length of text in textbox is not equal to zero
          if ( $('input#textcheck').val().length !== 0 )
          {
           
		    //Check if user is typing any key but delete and backspace
			      if (event.which !== 46 || event.keycode !== 46 || event.which !== 8 || event.keyCode !== 8)
			      {
			      $('label#checktext_label.small_txt_loading_dot').text('');
			      $('label#checktext_label.small_txt_loading_dot').html('<span class="small_txt_loading_dot">Typing search query: '+event.target.value+'</span> '+dots);
			     
			
			      }	
	          if (event.which === 46 || event.keycode === 46 || event.which === 8 || event.keyCode === 8)
		        {
		        $('label#checktext_label.small_txt_loading_dot').text('');
		        $('label#checktext_label.small_txt_loading_dot').html('<span class="small_txt_loading_dot">Deleting search query: '+event.target.value+'</span> '+dots);
		        }
				
				    $('button#search_text').click(function (event)
				    {
					    event.preventDefault();
					    event.stopImmediatePropagation();
					    var pass_url = $(this).attr('data-href') ;
					    var textcheck = $('input#textcheck').val();
					    $('input#textcheck').attr('value',textcheck);
					    var t_check  = new URL(window.location.href);
					    var c = t_check.searchParams.get("textcheck");
					      console.log('t_check='+c);
						    if(window.location.href.indexOf("textcheck=") === -1)
						    {
							
							    if ($('input#textcheck').val().length!=0)
							    {
							    var url =  pass_url + '?textcheck=' + textcheck;
							      console.log('URL first time='+url);
							    }
						    }
						    else
						    {
                   if (c != textcheck)
                   {
								      var pass_string = 'textcheck';
							        var url = setUrlParameter(pass_url, pass_string, textcheck);
							        console.log('URL if not equal ='+url);
							     }
                   else
                   {
                     var url = pass_url;
							       console.log('URL same='+url);
							     }
							      console.log('URL='+url);	
						    }
					    var decodedUrl = decodeURIComponent(url.replace(/&amp;/g, "&"));
					
			        $.ajax({
				            method: "POST",
                    url: decodedUrl,
                    dataType: "html",
                    data: $('#db-module-list-form').serialize(),
					            success: function(response){
					              console.log("success");
                        jQuery('#db-module-list-form').attr('action', url);
                      },
		                  error: function (e)
                      {
                        // If Ajax call returned error
                        console.log("Error");
                      },
                      complete: function (e)
                      {
                        // When Ajax call completed, show Done in console, assign form action attribute & submit
                        console.log("DONE");
					  				    jQuery('#db-module-list-form').attr('action', url);
				                jQuery('#db-module-list-form').submit();
                      }
				      });
				    });
		      }
	        if ( $('input#textcheck').val().length === 0 )
		      {
		      $('label#checktext_label.small_txt_loading_dot').text('');
		      $('label#checktext_label.small_txt_loading_dot').append('Search List');
		      
          }
        });
      	/**
        * The following clears the form and redirects to list page without any
        * parameters being passed, then it submits
        */
        $('button#clear_text').click(function (event)
        {
          var urlup = Drupal.url('modules/db_module/list');
          $('input#textcheck').attr('value', '');
          $('input#textcheck').val('');
          console.log('URL=' + urlup);
          $('#db-module-list-form').attr('action', urlup);
          $('#db-module-list-form').submit();
        });
    }	
  };	
})(jQuery, Drupal, drupalSettings); // End of dbmodule.js

