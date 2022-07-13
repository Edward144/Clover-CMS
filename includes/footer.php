                        </div>
                    </div>
                </div>
            </div>

			<footer id="pageFooter" class="bg-dark text-white">
				<div class="footerInner container-xl">
                    <div class="row py-3">
                        <div class="address col-sm-6 col-lg-3 mb-3">
                            <?php 
                                $addressFields = ['address_1', 'address_2', 'city', 'county', 'postcode'];
                                $hasAddress = false;
                            
                                foreach($addressFields as $addressField) {
                                    if(array_key_exists($addressField, $settingsArray) && !empty($settingsArray[$addressField])) {
                                        $hasAddress = true;
                                        break;
                                    }
                                }
                            ?>
                            
                            <?php if($hasAddress == true) : ?>
                                <h3>Find Us</h3>
                            
                                <address>
                                    <?php echo 
                                        (!empty($settingsArray['address_1']) ? $settingsArray['address_1'] . '<br>' : '') . 
                                        (!empty($settingsArray['address_2']) ? $settingsArray['address_2'] . '<br>' : '') . 
                                        (!empty($settingsArray['city']) ? $settingsArray['city'] . '<br>' : '') . 
                                        (!empty($settingsArray['county']) ? $settingsArray['county'] . '<br>' : '') . 
                                        (!empty($settingsArray['postcode']) ? $settingsArray['postcode'] : '');
                                    ?>
                                </address>
                            <?php endif; ?>
                        </div>
                        
                        <div class="contact col-sm-6 col-lg-3 mb-3">
                            <?php if(!empty($settingsArray['phone']) || !empty($settingsArray['email'])) : ?>
                                <h3>Contact Us</h3>
                            
                                <div class="contact">
                                    <?php if(!empty($settingsArray['phone'])) : ?>
                                        <p class="phone mb-0">
                                            <span class="fa fa-phone me-1"></span>
                                            <a href="tel: <?php echo $settingsArray['phone']; ?>" class="link-light"><?php echo $settingsArray['phone']; ?></a>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($settingsArray['phone'])) : ?>
                                        <p class="email mb-0">
                                            <span class="fa fa-envelope me-1"></span>
                                            <a href="mailto: <?php echo $settingsArray['email']; ?>" class="link-light"><?php echo $settingsArray['email']; ?></a>
                                        </p>
                                    <?php endif; ?>                                    
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="socials col-sm-6 col-lg-3 mb-3">
                            <?php $socials = $mysqli->query("SELECT * FROM `social_links` WHERE link <> '' AND link IS NOT NULL"); ?>
                            
                            <?php if($socials->num_rows > 0) : ?>
                                <h3>Connect With Us</h3>
                            
                                <ul class="nav">
                                    <?php while($social = $socials->fetch_assoc()) : ?>
                                        <li class="social" id="<?php echo $social['name']; ?>">
                                            <a href="<?php echo $social['link']; ?>" target="_blank" class="link-light"><span class="fab fa-<?php echo $social['name']; ?>"></span></a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        
                        <div class="links col-sm-6 col-lg-3 mb-3">
                            <?php 
                                $menuId = 1;
                                $footerMenu = $mysqli->prepare(
                                    "SELECT nm.name AS menu_name FROM `navigation_structure` AS ns
                                        LEFT OUTER JOIN `navigation_menus` AS nm ON nm.id = ns.menu_id
                                    WHERE ns.menu_id = ? AND ns.visible = 1 AND ns.parent_id = 0 AND nm.name <> '' AND nm.name IS NOT NULL LIMIT 1"
                                );
                                $footerMenu->bind_param('i', $menuId);
                                $footerMenu->execute();
                                $menuResult = $footerMenu->get_result();
                            
                                if($menuResult->num_rows > 0) {
                                    $menu = $menuResult->fetch_assoc();
                                    
                                    echo '<h3>' . $menu['menu_name'] . '</h3>';
                                    new verticalnav(1);
                                }
                             ?>
                        </div>
                    </div>
                </div>
			</footer>
		</div>
	</body>

    <script src="js/main.min.js"></script>
    <?php echo $__pluginManager->loadjs(); ?>
    <script src="js/retina.min.js"></script>
</html>