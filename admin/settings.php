<?php 
	$title = 'Settings';
	require_once(dirname(__FILE__) . '/includes/header.php'); 
?>

<div class="col-lg-3 bg-light py-3">
	<h3>Website Details</h3>
	
	<form id="websiteDetails" action="" method="post">
		<div class="form-group mb-3">
			<label>Website Name</label>
			<input type="text" class="form-control" name="websiteName" value="">
		</div>
		
		<div class="form-group mb-3">
			<label>Address Line 1</label>
			<input type="text" class="form-control" name="address1" value="">
		</div>
		
		<div class="form-group mb-3">
			<label>Address Line 2</label>
			<input type="text" class="form-control" name="address2" value="">
		</div>
		
		<div class="form-group mb-3">
			<label>City</label>
			<input type="text" class="form-control" name="city" value="">
		</div>
		
		<div class="form-group mb-3">
			<label>County</label>
			<input type="text" class="form-control" name="county" value="">
		</div>
		
		<div class="form-group mb-3">
			<label>Postcode</label>
			<input type="text" class="form-control" name="postcode" value="">
		</div>
		
		<hr>
		
		<div class="form-group mb-3">
			<label>Phone</label>
			<input type="text" class="form-control" name="phone" value="">
		</div>
		
		<div class="form-group mb-3">
			<label>Email</label>
			<input type="email" class="form-control" name="email" value="">
		</div>
		
		<hr>
		
		<div class="form-group mb-3">
			<label>Logo</label>
			<input type="hidden" id="logo" name="logo" value="">
			
			<div class="buttons mt-3 mb-n1">
				<a class="btn btn-secondary mb-1" data-fancybox="mediamanager" data-type="iframe" data-src="js/responsive_filemanager/filemanager/dialog.php?type=1&field_id=logo">Select Image</a>
				<input type="button" class="btn btn-dark mb-1" name="clearImage" value="Clear Image">
			</div>
		</div>
		
		<div class="form-group">
			<input type="submit" class="btn btn-primary" value="Save Details">
		</div>
	</form>
</div>
	
<?php $socials = $mysqli->query("SELECT * FROM `social_links`"); ?>

<?php if($socials->num_rows > 0) : ?>
	<div class="col-lg-3 py-3">
		<h3>Social Media</h3>
		
		<form id="socialLinks" action="" method="post">
			<?php while($social = $socials->fetch_assoc()) : ?>
				<div class="form-group mb-3">
					<label><span class="fab fa-<?php echo str_replace(' ', '-', strtolower($social['name'])); ?>"></span> <?php echo ucwords($social['name']); ?></label>
					<input type="text" class="form-control" name="<?php echo $social['name']; ?>" placeholder="https://<?php echo str_replace(' ', '-', strtolower($social['name'])); ?>.com/user-id"value="<?php echo $social['link']; ?>">
				</div>
			<?php endwhile; ?>
			
			<div class="form-group">
				<input type="submit" class="btn btn-primary" value="Save Socials">
			</div>
		</form>
	</div>
<?php endif; ?>

<div class="col-lg-3 bg-light py-3">
	<h3>Other Settings</h3>
	
	<form id="otherSettings" action="" method="post">
		<div class="form-group mb-3">
			<label>Google Analytics</label>
			<input type="text" class="form-control" name="googleAnalytics" placeholder="UA-12345678-9" value="">
		</div>
		
		<div class="form-group mb-3">
			<label><span class="fab fa-google"></span> ReCaptcha<sub>v3</sub> Site Key</label>
			<input type="text" class="form-control" name="siteKey" value="">
		</div>
		
		<div class="form-group mb-3">
			<label><span class="fab fa-google"></span> ReCaptcha<sub>v3</sub> Secret Key</label>
			<input type="text" class="form-control" name="secretKey" value="">
		</div>
		
		<div class="form-group">
			<input type="submit" class="btn btn-primary" value="Save Settings">
		</div>
	</form>
</div>

<script>
	$("input[name='logo']").change(function() {
		$(this).val($(this).val().split("\"")[0]);
		$(this).siblings("img").remove();
		
		if($(this).val() != "") {
			$("<img src='" + $(this).val() + "' class='img-fluid'>").insertAfter($(this));
		}
	});
	
	$("input[name='clearImage']").click(function() {
		$(this).parents(".form-group").first().children("input[type='hidden']").val("").trigger("change");
	});
</script>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>