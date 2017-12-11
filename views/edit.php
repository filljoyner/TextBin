<div class="wrap textbin">
	<div id="icon-textbin" class="icon32 icon32-textbin">
		<br />
	</div>
	<h2>TextBin - Edit <a href="<?php echo $this->plugin_add; ?>" class="add-new-h2">Add New</a></h2>
	
	<?php if($this->msg): ?>
	<div class="updated"><p><strong><?php echo $this->msg; ?></strong></p></div>
	<?php endif; ?>
	
	<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<input type="hidden" id="textbinId" name="textbinId" value="<?php echo $textbin->id; ?>" />
		
		<p><strong>Slug</strong><br />
		<input type="text" id="textbinName" name="textbinName" value="<?php echo $this->format($textbin->name, false); ?>" />
		<small><em>You'll use this slug to find your text.</em></small></p>
		
		<?php the_editor($this->format($textbin->val, false), 'textbinVal'); ?>
		
		<p><input type="checkbox" id="textbinFilter" name="textbinFilter" value="filter" <?php if($textbin->filter) echo 'checked'; ?> /> Format Text</p>
		
		<p><input type="submit" class="button button-large button-primary" value="Save Item" name="update_textBin" /></p>
	</form>
</div>