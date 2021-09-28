					</div>
				</div>
			</div>
		</div>

        <script src="js/css_colors.js"></script>
		<script src="js/admin.js"></script>
		<script src="js/tinyConfig.min.js"></script>

        <?php if(!empty($_POST)) : ?>
            <script>
                //Remove post data to avoid resubmission dialog
                if(window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>
        <?php endif; ?>
	</body>
</html>