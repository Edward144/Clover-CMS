<?php
	$menuItems = [
		[
			'name' => 'Dashboard',
			'link' => 'admin/',
			'icon' => 'fa-chart-line'
		],
		[
			'name' => 'Navigation',
			'link' => 'admin/manage-navigation',
			'icon' => 'fa-sitemap'
		],
		[
			'name' => 'Users',
			'link' => 'admin/manage-users',
			'icon' => 'fa-users'
		],
		[
			'name' => 'Settings',
			'link' => 'admin/settings',
			'icon' => 'fa-cogs'
		],
	];
?>

<ul class="nav flex-column">
	<a id="toggleSidebar" class="floatingToggle btn-dark"><span class="fa fa-bars"></span></a>
	<li class="nav-item">
		<a id="toggleSidebar" class="nav-link btn-dark">
			<span>Toggle Menu</span>
			<span class="fa fa-bars"></span>
		</a>
	</li>
	
	<?php foreach($menuItems as $item) : ?>
		<?php
			if($item['target'] == 'popup') {
				$width = (isset($item['popup_width']) && is_numeric($item['popup_width']) ? $item['popup_width'] : 1000);
				$height = (isset($item['popup_height']) && is_numeric($item['popup_height']) ? $item['popup_height'] : 625);
				
				$hw = ',\'width=' . $width . ',height=' . $height . '\'';
				
				$target = 'target="popup" onclick="window.open(\'' . $item['link'] . '\', \'' . $item['name'] . '\'' . $hw . '); return false;"';
			}
			elseif(!empty($item['target'])) {
				$target = 'target="' . $item['target'] . '"';
			}
			else {
				$target = '';
			}
		?>
	
		<li class="nav-item shadow-sm">
			<a class="nav-link btn-dark" href="<?php echo $item['link']; ?>" <?php echo $target; ?>>
				<span><?php echo $item['name']; ?></span>
				<?php echo (!empty($item['icon']) ? '<span class="fa ' . $item['icon'] . '"></span>' : ''); ?>
			</a>
		</li>
	<?php endforeach; ?>
	
	<li class="nav-item">
		<a class="nav-link btn-dark" data-fancybox="mediamanager" data-type="iframe" data-src="js/responsive_filemanager/filemanager/dialog.php" href="javascript:;">
			<span>Media Manager</span>
			<span class="fa fa-photo-video"></span>
		</a>
	</li>
</ul>