<?php 
	$title = 'Dashboard';
	require_once(dirname(__FILE__) . '/includes/header.php'); 
?>

<div class="col py-3">
	<textarea name="editor" class="tiny">
		<p>This is the initial content.</p>
	</textarea>
</div>

<script>	
	tinymce.init({
		selector: 'textarea.tiny',
		plugins: 'paste image imagetools table code save link responsivefilemanager media fullscreen lists template autoresize textcolor colorpicker',
		external_plugins: { 'filemanager' : '../responsive_filemanager/filemanager/plugin.min.js'},
		external_filemanager_path: 'js/responsive_filemanager/filemanager/',
		menubar: 'file edit view format insert table',
		toolbar: '',
		relative_urls: true,
		remove_script_host: true,
		content_css: '../css/style.min.css'
	});
</script>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>