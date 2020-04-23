<script type="text/html" id="tmpl-init-categories">
	<div class="category-filter" ><?php echo __( 'Categories', 'boldgrid-inspirations' ); ?></div>

	<div class="sub-category active" data-display-order="0" >
		<input type="radio" name="sub-category" checked data-sub-category-id="0" >
		<span class="sub-category-name"><?php echo __( 'All', 'boldgrid-inspirations' ); ?></span>
	</div>

	<# _.each( data, function( category ) { #>
		<# _.each( category.subcategories, function( sub_category ) { #>
			<div class="sub-category" data-display-order="{{sub_category.displayOrder}}" >
				<input type="radio" name="sub-category" data-sub-category-id="{{sub_category.id}}"> <span class="sub-category-name">{{sub_category.name}}</span>
			</div>
		<# }); #>
	<# }); #>
</script>

<script type="text/html" id="tmpl-theme">
	<#
		// Configure all / category order.
		var allOrder = data.build.isDefault ? data.build.defaultOrder : data.build.AllOrder,
			categoryOrder = data.build.isDefault ? data.build.defaultOrder : data.build.CategoryOrder;

		// Format our theme title.
		data.build.ThemeName = data.build.ThemeRevisionTitle !== undefined ?
			data.build.ThemeRevisionTitle : data.build.ThemeName.replace( 'boldgrid-', '' );
		data.key = IMHWPB.configs.api_key;
		data.build.pde = JSON.stringify( data.build.pde );

		// Make the theme name's first character uppercase.
		data.build.ThemeName = BoldGrid.Utility.ucfirst( data.build.ThemeName );
	#>
	<div class="theme" tabindex="0" data-category-id="{{data.build.ParentCategoryId}}"
		data-sub-category-id="{{data.build.CategoryId}}"
		data-sub-category-title="{{data.build.SubCategoryName}}"
		data-page-set-id="{{data.build.PageSetId}}"
		data-theme-id="{{data.build.ThemeId}}"
		data-theme-title="{{data.build.ThemeName}}"
		data-pde="{{data.build.pde}}"
		data-all-order="{{allOrder}}"
		data-category-order="{{categoryOrder}}"
		data-is-default="{{data.build.isDefault}}"
		data-build-id="{{data.build.Id}}">

		<div class="theme-screenshot">
			<a class="fancybox" rel="gallery1" href="{{data.configs.asset_server}}/api/asset/get?key={{data.configs.api_key}}&id={{data.build.AssetId}}" title="{{data.build.ThemeName}} - {{data.build.SubCategoryName}}">
				<img class="lazy" data-original="{{data.configs.asset_server}}/api/asset/get?key={{data.configs.api_key}}&id={{data.build.AssetId}}&thumbnail=1" alt="" width="290" height="194">
			</a>
		</div>

		<div class="theme-id-container">

			<h2 class="theme-name" >
				<span class="sub-category-name">{{data.build.SubCategoryName}} - </span>
				<span class="name">{{data.build.ThemeName}}</span>
			</h2>

			<div class="theme-actions">
				<a class="button button-primary hide-if-no-customize"><?php echo esc_html__( 'Select', 'boldgrid-inspirations' ); ?></a>
			</div>

		</div>

	</div>
</script>

<script type="text/html" id="tmpl-pagesets">
	<# _.each( data, function( pageset ) {
		pageset.is_default = pageset.is_default_page_set;
		pageset.is_default_page_set = ( '1' === pageset.is_default_page_set ? 'checked' : '' );
		pageset.is_active = ( '1' === pageset.is_default ? 'active' : '' );
		pageset.page_set_description = pageset.page_set_description.replace(/'/g, "&#39;");
	#>
		<div class="pageset-option {{pageset.is_active}}" title="{{pageset.page_set_description}}">
			<input type="radio" name="pageset" data-is-default="{{pageset.is_default}}" data-page-set-id="{{pageset.id}}" {{pageset.is_default_page_set}} >
			<span class="pointer">{{pageset.page_set_name}}</span>
		</div>
	<# }); #>
</script>

<script type="text/html" id="tmpl-social-media">
	<div class="social-media" data-provider="{{data.icon}}">
		<span><i class="fa fa-{{data.icon}}" aria-hidden="true"></i></span>
		<input type="text" value="{{data.url}}" name="survey[social][{{data.icon}}]">
		<i class="fa fa-times" aria-hidden="true"></i>
	</div>
</script>