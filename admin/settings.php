<?php 
	$title = 'Settings';
	require_once(dirname(__FILE__) . '/includes/header.php');

    checkaccess(basename(__FILE__));

    //Save Website Details
    if(isset($_POST['saveWebDetails'])) {
        $details = $mysqli->prepare(
            "INSERT INTO `settings` (name, value) VALUES
                ('website_name', ?),
                ('address_1', ?),
                ('address_2', ?),
                ('city', ?),
                ('county', ?),
                ('postcode', ?),
                ('phone', ?),
                ('email', ?),
                ('logo', ?)
            ON DUPLICATE KEY UPDATE name = VALUES(name), value = VALUES(value)"
        );
        $details->bind_param('sssssssss', $_POST['websiteName'], $_POST['address1'], $_POST['address2'], $_POST['city'], $_POST['county'], $_POST['postcode'], $_POST['phone'], $_POST['email'], $_POST['logo']);
        $details->execute();
        
        if($other->error) {
            $status = 'danger';
            $detailsMessage = 'Failed to save changes';
        }
        else {
            $status = 'success';
            $detailsMessage = 'Settings saved successfully';
        }
    }

    //Save Page Settings
    if(isset($_POST['savePageSettings'])) {
        $page = $mysqli->prepare(
            "INSERT INTO `settings` (name, value) VALUES
                ('homepage', ?),
                ('newspage', ?),
                ('comment_approval', ?)
            ON DUPLICATE KEY UPDATE name = VALUES(name), value = VALUES(value)"
        );
        $page->bind_param('sss', $_POST['homepage'], $_POST['newspage'], $_POST['commentapproval']);
        $page->execute();
        
        if($page->error) {
            $status = 'danger';
            $pageMessage = 'Failed to save changes';
        }
        else {
            $status = 'success';
            $pageMessage = 'Settings saved successfully';
        }
    }

    //Save Social Media
    if(isset($_POST['saveSocialMedia'])) {
        $social = $mysqli->prepare(
            "INSERT INTO `social_links` (name, link) VALUES
                ('facebook', ?),
                ('twitter', ?),
                ('instagram', ?),
                ('youtube', ?),
                ('linkedin', ?)
            ON DUPLICATE KEY UPDATE name = VALUES(name), link = VALUES(link)"
        );
        $social->bind_param('sssss', $_POST['facebook'], $_POST['twitter'], $_POST['instagram'], $_POST['youtube'], $_POST['linkedin']);
        $social->execute();
        
        if($social->error) {
            $status = 'danger';
            $socialMessage = 'Failed to save changes';
        }
        else {
            $status = 'success';
            $socialMessage = 'Settings saved successfully';
        }
    }

    //Save Other Settings
    if(isset($_POST['saveOtherSettings'])) {
        $other = $mysqli->prepare(
            "INSERT INTO `settings` (name, value) VALUES
                ('google_analytics', ?),
                ('recaptcha_sitekey_v3', ?),
                ('recaptcha_secretkey_v3', ?),
                ('recaptcha_sitekey_v2', ?),
                ('recaptcha_secretkey_v2', ?)
            ON DUPLICATE KEY UPDATE name = VALUES(name), value = VALUES(value)"
        );
        $other->bind_param('sssss', $_POST['googleAnalytics'], $_POST['sitekeyv3'], $_POST['secretkeyv3'], $_POST['sitekeyv2'], $_POST['secretkeyv2']);
        $other->execute();
        
        if($other->error) {
            $status = 'danger';
            $otherMessage = 'Failed to save changes';
        }
        else {
            $status = 'success';
            $otherMessage = 'Settings saved successfully';
        }
    }

    $settings = $mysqli->query("SELECT * FROM `settings`");
    $settingValues = [];

    while($setting = $settings->fetch_assoc()) {
        $settingValues[$setting['name']] = $setting['value'];
    }

    $socials = $mysqli->query("SELECT * FROM `social_links`"); 
?>

<div class="col-md-6 col-lg-3 bg-light py-3">
	<h3>Website Details</h3>
	
	<form id="websiteDetails" method="post">
		<div class="form-group mb-3">
			<label>Website Name</label>
			<input type="text" class="form-control" name="websiteName" value="<?php echo $settingValues['website_name']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label>Address Line 1</label>
			<input type="text" class="form-control" name="address1" value="<?php echo $settingValues['address_1']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label>Address Line 2</label>
			<input type="text" class="form-control" name="address2" value="<?php echo $settingValues['address_2']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label>City</label>
			<input type="text" class="form-control" name="city" value="<?php echo $settingValues['city']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label>County</label>
			<input type="text" class="form-control" name="county" value="<?php echo $settingValues['county']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label>Postcode</label>
			<input type="text" class="form-control" name="postcode" value="<?php echo $settingValues['postcode']; ?>">
		</div>
		
		<hr>
		
		<div class="form-group mb-3">
			<label>Phone</label>
			<input type="text" class="form-control" name="phone" value="<?php echo $settingValues['phone']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label>Email</label>
			<input type="email" class="form-control" name="email" value="<?php echo $settingValues['email']; ?>">
		</div>
		
		<hr>
		
		<div class="form-group mb-3">
                <label>Logo</label>
                <input type="hidden" id="logo" name="logo" value="<?php echo $settingValues['logo']; ?>">

                <?php if(!empty($settingValues['logo'])) : ?>
                    <img src="<?php echo $settingValues['logo']; ?>" class="d-block img-fluid">
                <?php endif; ?>
                
                <div class="buttons mt-3 mb-n1">
                    <a class="btn btn-secondary mb-1" data-fancybox="mediamanager" data-type="iframe" data-src="js/responsive_filemanager/filemanager/dialog.php?type=1&field_id=logo">Select Image</a>
                    <input type="button" class="btn btn-dark mb-1" name="clearImage" value="Clear Image">
                </div>
            </div>
		
		<div class="form-group">
			<input type="submit" class="btn btn-primary" name="saveWebDetails" value="Save Details">
		</div>
        
        <?php if(isset($detailsMessage)) : ?>
            <div class="alert alert-<?php echo $status; ?> mt-3">
                <?php echo $detailsMessage; ?>
            </div>
       <?php endif; ?>
	</form>
</div>

<div class="col-md-6 col-lg-3 py-3">
	<h3>Page Settings</h3>
	
	<form id="pageSettings" method="post">
		<div class="form-group mb-3">
			<label>Homepage</label>
			<select class="form-control" name="homepage">
				<option selected disabled>--Select Homepage--</option>
                
                <?php $pages = $mysqli->query("SELECT id, name FROM `posts` ORDER BY post_type_id, name ASC"); ?>
                
                <?php if($pages->num_rows > 0) : ?>
                    <?php while($page = $pages->fetch_assoc()) : ?>
                        <option value="<?php echo $page['id']; ?>" <?php echo ($page['id'] == $settingValues['homepage'] ? 'selected' : ''); ?>><?php echo $page['name']; ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
			</select>
		</div>
		
		<div class="form-group mb-3">
			<label>News Page</label>
			<select class="form-control" name="newspage">
				<option value="" selected>No News Page</option>
                
                <?php $pages = $mysqli->query("SELECT id, name FROM `posts` WHERE post_type_id <> 2 ORDER BY post_type_id, name ASC"); ?>
                
                <?php if($pages->num_rows > 0) : ?>
                    <?php while($page = $pages->fetch_assoc()) : ?>
                        <option value="<?php echo $page['id']; ?>" <?php echo ($page['id'] == $settingValues['newspage'] ? 'selected' : ''); ?>><?php echo $page['name']; ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
			</select>
		</div>
		
        <div class="form-group mb-3">
            <label>Default Comment Approval</label>
            <select class="form-control" name="commentapproval">
                <option value="unapproved" <?php echo ($settingValues['comment_approval'] == 'unapproved' ? 'selected' : ''); ?>>Unapproved</option>
                <option value="approved" <?php echo ($settingValues['comment_approval'] == 'approved' ? 'selected' : ''); ?>>Approved</option>
            </select>
        </div>
        
		<div class="form-group">
			<input type="submit" class="btn btn-primary" name="savePageSettings" value="Save Pages">
		</div>
        
        <?php if(isset($pageMessage)) : ?>
            <div class="alert alert-<?php echo $status; ?> mt-3">
                  <?php echo $pageMessage; ?>
            </div>
       <?php endif; ?>
	</form>
</div>

<?php if($socials->num_rows > 0) : ?>
	<div class="col-md-6 col-lg-3 bg-light py-3">
		<h3>Social Media</h3>
		
		<form id="socialLinks" method="post">
			<?php while($social = $socials->fetch_assoc()) : ?>
				<div class="form-group mb-3">
					<label><span class="fab fa-<?php echo str_replace(' ', '-', strtolower($social['name'])); ?>"></span> <?php echo ucwords($social['name']); ?></label>
					<input type="text" class="form-control" name="<?php echo $social['name']; ?>" placeholder="https://<?php echo str_replace(' ', '-', strtolower($social['name'])); ?>.com/user-id"value="<?php echo $social['link']; ?>">
				</div>
			<?php endwhile; ?>
			
			<div class="form-group">
				<input type="submit" class="btn btn-primary" name="saveSocialMedia" value="Save Socials">
			</div>
            
            <?php if(isset($socialMessage)) : ?>
                <div class="alert alert-<?php echo $status; ?> mt-3">
                    <?php echo $socialMessage; ?>
                </div>
           <?php endif; ?>
		</form>
	</div>
<?php endif; ?>

<div class="col-md-6 col-lg-3 py-3">
	<h3>Other Settings</h3>
	
	<form id="otherSettings" method="post">
		<div class="form-group mb-3">
			<label>Google Analytics</label>
			<input type="text" class="form-control" name="googleAnalytics" placeholder="UA-12345678-9" value="<?php echo $settingValues['google_analytics']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label><span class="fab fa-google"></span> ReCaptcha<sub>v3</sub> Site Key</label>
			<input type="text" class="form-control" name="sitekeyv3" value="<?php echo $settingValues['recaptcha_sitekey_v3']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label><span class="fab fa-google"></span> ReCaptcha<sub>v3</sub> Secret Key</label>
			<input type="text" class="form-control" name="secretkeyv3" value="<?php echo $settingValues['recaptcha_secretkey_v3']; ?>">
		</div>
        
        <div class="form-group mb-3">
            <small class="text-muted">Recaptcha v3 is used for verifying form submissions and can be integrated into custom actions</small>
        </div>
        
        <div class="form-group mb-3">
			<label><span class="fab fa-google"></span> ReCaptcha<sub>v2</sub> Site Key</label>
			<input type="text" class="form-control" name="sitekeyv2" value="<?php echo $settingValues['recaptcha_sitekey_v2']; ?>">
		</div>
		
		<div class="form-group mb-3">
			<label><span class="fab fa-google"></span> ReCaptcha<sub>v2</sub> Secret Key</label>
			<input type="text" class="form-control" name="secretkeyv2" value="<?php echo $settingValues['recaptcha_secretkey_v2']; ?>">
		</div>
		
        <div class="form-group mb-3">
            <small class="text-muted">Recaptcha v2 is used for verifying comments and can be integrated into custom actions</small>
        </div>
        
		<div class="form-group">
			<input type="submit" class="btn btn-primary" name="saveOtherSettings" value="Save Settings">
		</div>
        
        <?php if(isset($otherMessage)) : ?>
            <div class="alert alert-<?php echo $status; ?> mt-3">
                <?php echo $otherMessage; ?>
            </div>
        <?php endif; ?>
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