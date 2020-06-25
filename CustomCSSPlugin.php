<?php
/*
Plugin Name: Custom CSS
Description: Add custom CSS to theme
Version: 0.1
Author: Pavol Bokor
Author URI: https://www.4enzo.sk
Licence: GNU DPL v 3.0
*/

# get correct id for plugin
$thisfile_customcss=basename(__FILE__, ".php");
$customcss_file=GSDATAOTHERPATH .'CustomCSS.xml';

# add in this plugin's language file
i18n_merge($thisfile_customcss) || i18n_merge($thisfile_customcss, 'en_US');

# register plugin
register_plugin(
	$thisfile_customcss, 								# ID of plugin, should be filename minus php
	i18n_r($thisfile_customcss.'/CUSTOMCSS_TITLE'), 	# Title of plugin
	'0.1', 												# Version of plugin
	'Pavol Bokor',										# Author of plugin
	'https://www.4enzo.sk', 							# Author URL
	i18n_r($thisfile_customcss.'/CUSTOMCSS_DESC'), 		# Plugin Description
	'theme', 											# Page type of plugin
	'customcss_show'  									# Function that displays content
);


# hooks
add_action('theme-footer','customcss_echo_to_theme'); 
add_action('theme-sidebar','createSideMenu',array($thisfile_customcss, i18n_r($thisfile_customcss.'/CUSTOMCSS_TITLE'))); 

# load codemirror
register_script('codemirror', $SITEURL.$GSADMIN.'/template/js/codemirror/lib/codemirror-compressed.js', '0.2.0', FALSE);
register_style('codemirror-css',$SITEURL.$GSADMIN.'/template/js/codemirror/lib/codemirror.css','screen',FALSE);
register_style('codemirror-theme',$SITEURL.$GSADMIN.'/template/js/codemirror/theme/default.css','screen',FALSE);
queue_script('codemirror', GSBACK);
queue_style('codemirror-css', GSBACK);
queue_style('codemirror-theme', GSBACK);

# get XML data
if (file_exists($customcss_file)) {
	$customcss_data = getXML($customcss_file);
}
# print custom CSS to theme footer
$echo_to_theme = '';
if(isset($customcss_data->customcss_content)) $echo_to_theme = $customcss_data->customcss_content;

function customcss_echo_to_theme() {
	global $echo_to_theme;
	echo 
"
<!-- Custom CSS -->
<style>
" . $echo_to_theme . "
</style>
";
}

function customcss_show() {
	global $customcss_file, $customcss_data, $thisfile_customcss;
	$success=$error=null;
	
	// submitted form
	if (isset($_POST['submit'])) {		
		
		if ($_POST['customcss_content'] != '') {
			$resp['customcss_content'] = $_POST['customcss_content'];
		}	
		
		# if there are no errors, save data
		if (!$error) {
			$xml = @new SimpleXMLElement('<item></item>');
			if(isset($resp['customcss_content'])) $xml->addChild('customcss_content', htmlspecialchars($resp['customcss_content']));
							
			if (! $xml->asXML($customcss_file)) {
				$error = i18n_r('CHMOD_ERROR');
			} else {
				$customcss_data = getXML($customcss_file);
				$success = i18n_r('SETTINGS_UPDATED');
			}
		}
	}

	?>

	<h3><?php i18n($thisfile_customcss.'/CUSTOMCSS_TITLE'); ?></h3>
	
	<?php 
	if($success) { 
		echo '<p style="color:#669933;"><b>'. $success .'</b></p>';
	} 
	if($error) { 
		echo '<p style="color:#cc0000;"><b>'. $error .'</b></p>';
	}
	?>
	<form method="post" action="<?php	echo $_SERVER ['REQUEST_URI']?>">
		
		<?php
			$value = '';
			if(isset($customcss_data->customcss_content)) $value = $customcss_data->customcss_content;
		?>
		
		<p>
			<textarea id="lb_customcss_title" name="customcss_content" class="text" type="text"><?php echo $value; ?></textarea>
		</p>

		<p><input type="submit" id="submit" class="submit" value="<?php i18n('BTN_SAVESETTINGS'); ?>" name="submit" /></p>

	</form>
<script>
window.onload = function() {
	  var foldFunc = CodeMirror.newFoldFunction(CodeMirror.braceRangeFinder);
	  function keyEvent(cm, e) {
	    if (e.keyCode == 81 && e.ctrlKey) {
	      if (e.type == "keydown") {
	        e.stop();
	        setTimeout(function() {foldFunc(cm, cm.getCursor().line);}, 50);
	      }
	      return true;
	    }
	  }
	  function toggleFullscreenEditing()
	    {
	        var editorDiv = $('.CodeMirror-scroll');
	        if (!editorDiv.hasClass('fullscreen')) {
	            toggleFullscreenEditing.beforeFullscreen = { height: editorDiv.height(), width: editorDiv.width() }
	            editorDiv.addClass('fullscreen');
	            editorDiv.height('100%');
	            editorDiv.width('100%');
	            editor.refresh();
	        }
	        else {
	            editorDiv.removeClass('fullscreen');
	            editorDiv.height(toggleFullscreenEditing.beforeFullscreen.height);
	            editorDiv.width(toggleFullscreenEditing.beforeFullscreen.width);
	            editor.refresh();
	        }
	    }
      var editor = CodeMirror.fromTextArea(document.getElementById("lb_customcss_title"), {
        lineNumbers: true,
        matchBrackets: true,
        indentUnit: 4,
        indentWithTabs: true,
        enterMode: "keep",
        mode:"text/css",
        tabMode: "shift",
        theme:'default',
    	onGutterClick: foldFunc,
    	extraKeys: {"Ctrl-Q": function(cm){foldFunc(cm, cm.getCursor().line);},
    				"F11": toggleFullscreenEditing, "Esc": toggleFullscreenEditing},
        onCursorActivity: function() {
		   	editor.setLineClass(hlLine, null);
		   	hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
		}
      	});
     var hlLine = editor.setLineClass(0, "activeline");
    
     }
     
</script>
	<?php
}

?>
