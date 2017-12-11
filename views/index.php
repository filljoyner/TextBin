<div class="wrap textbin">
	<div id="icon-textbin" class="icon32 icon32-textbin">
		<br />
	</div>
	<h2>TextBin <a href="<?php echo $this->plugin_add; ?>" class="add-new-h2">Add New</a></h2>
	
	<?php if($this->msg): ?>
	<div class="updated"><p><strong><?php echo $this->msg; ?></strong></p></div>
	<?php endif; ?>
	
	<div class="alignleft actions">
		<p>
			<?php echo count($textbins); ?> Item(s)
			<span id="textbin_save_indicator" class="tb_hide">
				&nbsp;&nbsp;&nbsp;&nbsp;
				Saving &nbsp;
				<img src="<?php echo $this->img_url; ?>ajax-loader.gif" /> 
			</span>
		</p>
	</div>
	
	<form id="textbin_filter" action="" method="get">
		<p class="search-box">
			<img src="<?php echo $this->img_url; ?>ajax-loader.gif" id="textbin_search_indicator" class="tb_hide" />
			<input type="search" id="textbin-search-input" value="" />
			<input type="submit" name="" id="textbin-search-submit" class="button" value="Search"  />
		</p>
	</form>
	
	
	<div id="textbin_table_list">
		<?php require_once('list.php'); ?>
	</div>
</div>