jQuery(function($){

	// load the search suggestions
	$(".iss,input[name='s']").suggest(
		iss_options.iss_suggest_url,
		{
			resultsClass: 'iss_results',
			selectClass:'iss_over',
			matchClass: 'iss_match',
			
			createItems:function(txt) {
				if (typeof JSON!=='undefined' && typeof JSON.parse==='function' && txt!=='') {
					return JSON.parse(txt);
				} else {
					return eval(txt);
				}
			},
			
			formatItem:function(item,q) {
				var isshtml = "";
				
				switch(item.type) {
				
					case 'post':
						
						switch (iss_options.iss_style) {
						
							case 'text':
								isshtml += "<li class=\"iss-text\">"+this.addMatchClass(item.title,q)+"</li>";
							break;
							
							case 'textstrap':
								isshtml += "<li class=\"iss-textstrap\">"+this.addMatchClass(item.title,q)+"<span class=\"iss-sub\">"+item.posttype+": "+item.postdate+"</span></li>";
							break;
							
							case 'image':
								if (item.image != 0) {
									if (item.yype == 'post') {
										isshtml += "<li class=\"iss-image\"><img src=\""+item.image+"\" width=\"50\" height\"50\" /><div class=\"iss-image-content\">"+this.addMatchClass(item.title,q)+"<span class=\"iss-sub\">"+item.categories+"</span></div></li>";
									} else {
										isshtml += "<li class=\"iss-image\"><img src=\""+item.image+"\" width=\"50\" height\"50\" /><div class=\"iss-image-content\">"+this.addMatchClass(item.title,q)+"<span class=\"iss-sub\">"+item.posttype+"</span></div></li>";
									}
								} else {
									if (item.type == 'post') {
										isshtml += "<li class=\"iss-textstrap\">"+this.addMatchClass(item.title,q)+"<span class=\"iss-sub\">"+item.categories+"</span></li>";
									} else {
										isshtml += "<li class=\"iss-textstrap\">"+this.addMatchClass(item.title,q)+"<span class=\"iss-sub\">"+item.posttype+"</span></li>";
									}
								}
							break;
							
						}

					break;
					
					case 'taxonomy':

						switch (iss_options.iss_style) {

							case 'text':
								isshtml += "<li class=\"iss-text\">"+this.addMatchClass(item.title,q)+"</li>";
							break;
							
							case 'textstrap':
							case 'image':
								isshtml += "<li class=\"iss-textstrap\">"+this.addMatchClass(item.title,q)+"<span class=\"iss-sub\">"+item.taxonomy+" ("+item.count+")</span></li>";
							break;
							
						}
											
					break;
					
					case 'more':
						isshtml += "<li class=\"iss-text iss-more\">"+this.addMatchClass(item.title,q)+"</li>";
					break;
				
				}
				
				
				return isshtml;
			},
			
			selectItemText:function(item) {
				return item.title;
			},
			
			onSelect: function(item) {
				$(this).focus().keyup();
				if (iss_options.iss_magic != 0) { 
					window.location.href= item.permalink;
				}
				return false;
			}
		}
	);
		
	if (iss_options.iss_content != 0) {		
		var wpiss = {
			
			http: null,
			last_query: null,
			html: null,
			
			init: function() {
			
				// grab current html to be output if we clear the search box
				html = $(iss_options.iss_content).html();
			
				// perform an instant search
				$(".iss,input[name='s']").keyup(function(event) {
					
					// if search box is within content area return false
					if ($(this).parents().is(iss_options.iss_content)) { return false; }
					
					event.preventDefault();
					
					// grab the query
					var q = $.trim($(this).val());
					
					// if query is empty then reload original html
					if(q.length == 0 && wpiss.last_query != null) {
						$(iss_options.iss_content).html(html);
						return false;
					}
						
					if(q.length < 2 || wpiss.last_query == q)
						return false;
						
					$(this).addClass('iss-on');
	
					// cancel running requests
					if(wpiss.http) wpiss.http.abort();
					 
					// test JS only
					// http://css-tricks.com/8392-ajax-load-container-contents/
					// $(iss_options.iss_content).load(iss_options.iss_instant_url+'&s='+q+' '+iss_options.iss_content+' > *');
	
					wpiss.http = $.get(iss_options.iss_instant_url+'&s=' + q, function(data) {
						wpiss.last_query = q;
						$(".iss,input[name='s']").removeClass('iss-on');
						
						// if empty data then likely nonce fail, reset page
						if (data == '') {
							$(iss_options.iss_content).html(html);
						} else {
							$(iss_options.iss_content).html(data);
						}
					});
	
				});
			}
		};
	
	wpiss.init();
	
	}

});