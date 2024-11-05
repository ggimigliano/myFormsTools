// JavaScript Document
$.tablesorter.addWidget({
		      // give the widget a id
		      id: "sortPersist",
		      // format is called when the on init and when a sorting has finished
		      format: function(table) {

		          var COOKIE_NAME = 'MY_PERSISTENT_TABLE';
		          var cookie = $.cookie(COOKIE_NAME);
		          var options = {path: '/'};

		          var data = [];
		          var sortList = table.config.sortList;
		          var id = $(table).attr('id');
		                   // If the existing sortList isn't empty, set it into the cookie and get out
		          if (sortList.length > 0) {
		              if (typeof(cookie) == "undefined" || cookie == null) {
		                  data = {id: sortList};
		              }
		              else {
		                  data = $.evalJSON(cookie);
		                  data[id] = sortList;
		              }
		              $.cookie(COOKIE_NAME, $.toJSON(data), options);
		          }
		          // Otherwise...
		          else {
		              if (typeof(cookie) != "undefined" && cookie != null) {
		                  // Get the cookie data
		                  var data = $.evalJSON($.cookie(COOKIE_NAME));
		                  // If it exists
		                  if (typeof(data[id]) != "undefined" && data[id] != null) {
		                      // Get the list
		                      sortList = data[id];
		                      // And finally, if the list is NOT empty, trigger the sort with the new list
		                      if (sortList.length > 0) {
		                          //table.config.sortList = sortList;
		                            $(table).trigger("sorton", [sortList]);
		                      }
		                   }
		              }
		          }

		      }
		  });