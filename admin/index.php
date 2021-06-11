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
		height: 800,
		plugins: 'paste image imagetools table code save link responsivefilemanager media fullscreen lists template textcolor colorpicker visualblocks',
		external_plugins: { 'filemanager' : '../responsive_filemanager/filemanager/plugin.min.js'},
		external_filemanager_path: 'js/responsive_filemanager/filemanager/',
		menubar: 'file edit view format insert table',
		toolbar1: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | outdent indent | formatselect fontselect fontsizeselect forecolor',
		toolbar2: 'image media | link | bullist numlist | table tableprops | tableinsertcolafter tableinsertcolbefore tabledeletecol | tableinsertrowafter tableinsertcolafter tabledeleterow | visualblocks | fullscreen code template ',
		toolbar_sticky: true,
		relative_urls: true,
		remove_script_host: true,
		content_css: '../css/style.min.css',
		font_formats: 'Roboto=roboto,Open Sans=open-sans,Arial=arial,helvetica,sans-serif; Times new Roman=times new roman,serif; Courier New=courier new,courier,monospace; AkrutiKndPadmini=Akpdmi-n',
		end_container_on_empty_block: true,
		templates: [
			{
				title: 'Two Columns',
				description: 'Two reponsive columns',
				url: 'js/tinymce/templates/two-column.html'
			},
			{
				title: 'Three Columns',
				description: 'Three responsive columns',
				url: 'js/tinymce/templates/three-column.html'
			},
		]
	});
</script>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>