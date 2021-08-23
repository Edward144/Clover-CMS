<?php
	$menuItems = [
		[
			'name' => 'Dashboard',
			'link' => 'admin/',
			'icon' => 'fa-chart-line',
            'filename' => 'index.php'
		],
        [
			'name' => 'Forms',
			'link' => 'admin/manage-forms',
			'icon' => 'fa-pen-alt',
            'filename' => 'manage-forms.php'
		],
		[
			'name' => 'Navigation',
			'link' => 'admin/manage-navigation',
			'icon' => 'fa-sitemap',
            'filename' => 'manage-navigation.php'
		],
		[
			'name' => 'Users',
			'link' => 'admin/manage-users',
			'icon' => 'fa-users',
            'filename' => 'manage-users.php'
		],
		[
			'name' => 'Settings',
			'link' => 'admin/settings',
			'icon' => 'fa-cogs',
            'filename' => 'settings.php'
		],
	];

	$postTypes = $mysqli->query("SELECT * FROM `post_types`");
	$postItems = [];
	$pi = 0;

	if($postTypes->num_rows > 0) {
		while($postType = $postTypes->fetch_assoc()) {
            if(checkaccess('posttype_' . $postType['name'], true) !== false) {
                $postItems[$pi] = [
                    'name' => ucwords(str_replace('-', ' ', $postType['name'])),
                    'link' => 'admin/manage-content/' . $postType['name'],
                    'icon' => (!empty($postType['icon']) ? $postType['icon'] : 'fa-file-alt')
                ];

                $pi++;
            }
		}
		
		array_splice($menuItems, 1, 0, $postItems);
	}
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
        <?php if((!empty($item['filename']) && file_exists(dirname(__DIR__) . '/' . $item['filename']) && checkaccess($item['filename'], true) !== false) || empty($item['filename']) || (!empty($item['filename']) && !file_exists(dirname(__DIR__) . '/' . $item['filename']))) : ?>
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
        <?php endif; ?>
	<?php endforeach; ?>
	
	<li class="nav-item">
		<a class="nav-link btn-dark" data-fancybox="mediamanager" data-type="iframe" data-src="js/responsive_filemanager/filemanager/dialog.php" href="javascript:;">
			<span>Media Manager</span>
			<span class="fa fa-photo-video"></span>
		</a>
	</li>
</ul>

<footer id="pageFooter">
    <span class="small"><a href="https://github.com/Edward144/Clover-CMS" target="_blank"><span class="fab fa-github"></span> Edward144/Clover-CMS</a> <?php echo CMS_VERSION; ?></span>
</footer>