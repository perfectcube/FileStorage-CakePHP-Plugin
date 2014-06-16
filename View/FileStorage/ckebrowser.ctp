<!doctype html>
<html class="no-js" lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Buildrr Browser</title>
<link rel="stylesheet" href="/FileStorage/css/app.css" />
<script src="/FileStorage/bower_components/modernizr/modernizr.js"></script>
</head>
<body>
	<div class="off-canvas-wrap" data-offcanvas>
		<div class="inner-wrap">
			<nav class="tab-bar">
				<section class="left-small">
					<a class="left-off-canvas-toggle menu-icon" href="#"><span></span></a>
				</section>

				<section class="middle tab-bar-section">
					<h1 class="title">Menu</h1>
				</section>
			</nav>

			<aside class="left-off-canvas-menu">
				<ul class="off-canvas-list">
					<li><label>Media Types</label></li>
					<li><a href="#">Images</a></li>
					<li><a href="#">Videos</a></li>
					<li><a href="#">Files</a></li>
				</ul>
			</aside>

			<section class="main-section">
				<ul id="mainTabs" class="tabs" data-tab>
					<li class="tab-title active"><a href="#fileBrowser">Browser</a></li>
					<li class="tab-title"><a href="#uploadPanel">Upload</a></li>
				</ul>
				<div class="tabs-content">
					<div class="content active" id="fileBrowser">
						<div class="panel">
							<ul id="browserList" class="small-block-grid-6 medium-block-grid-8 large-block-grid-12">
	  							<?php echo $this->Element('FileStorage.media_list', array('media', $media)); ?>
							</ul>
						</div>
					</div>
					<div class="content" id="uploadPanel">
						<div class="panel">
							<?php echo $this->Element('FileStorage.upload_form'); ?>
						</div>
					</div>
				</div>
			</section>

			<a class="exit-off-canvas"></a>

		</div>
	</div>
	
	<script src="/FileStorage/bower_components/jquery/dist/jquery.min.js"></script>
	<script src="/FileStorage/bower_components/foundation/js/foundation.min.js"></script>
	<script src="/FileStorage/bower_components/jquery-form/jquery.form.js"></script>
	<script src="/FileStorage/bower_components/underscore/underscore.js"></script>
	<script src="/FileStorage/bower_components/backbone/backbone.js"></script>
	<script src="/FileStorage/js/app.js"></script>
	
	
	<script type="text/javascript">
		
		// Helper function to get parameters from the query string.
		function getUrlParam( paramName ) {
		    var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' ) ;
		    var match = window.location.search.match(reParam) ;
	
		    return ( match && match.length > 1 ) ? match[ 1 ] : null ;
		}

		var funcNum = getUrlParam( 'CKEditorFuncNum' );

		function sendUrl( fileUrl ) {
			window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
		}


		$(document).ready(function() { 
		    var options = { 
		        target:        '#browserList',   // target element(s) to be updated with server response 
		        beforeSubmit:  showRequest,  // pre-submit callback 
		        success:       showResponse  // post-submit callback 
		 
		        // other available options: 
		        //url:       url         // override for form's 'action' attribute 
		        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
		        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
		        //clearForm: true        // clear all form fields after successful submit 
		        //resetForm: true        // reset the form after successful submit 
		 
		        // $.ajax options can be used here too, for example: 
		        //timeout:   3000 
		    }; 
		 
		    // bind form using 'ajaxForm' 
		    $('#FileBrowserForm').ajaxForm(options); 

			//Click Handler for ckeditor
			$("#browserList").on('click', '.select-media', function(e) {
				var url = $(this).data('url');
				sendUrl(url);
				window.close();
			});

		    
		}); 
		 
		// pre-submit callback 
		function showRequest(formData, jqForm, options) { 
		   
		    return true; 
		} 
		 
		// post-submit callback 
		function showResponse(responseText, statusText, xhr, $form)  { 
		    // for normal html responses, the first argument to the success callback 
		    // is the XMLHttpRequest object's responseText property 
		 
		    // if the ajaxForm method was passed an Options Object with the dataType 
		    // property set to 'xml' then the first argument to the success callback 
		    // is the XMLHttpRequest object's responseXML property 
		 
		    // if the ajaxForm method was passed an Options Object with the dataType 
		    // property set to 'json' then the first argument to the success callback 
		    // is the json data object returned by the server 
		 	$form.resetForm();
		    $('a[href=#fileBrowser]').trigger("click");
		}  
		
	</script>
	
	
</body>
</html>
