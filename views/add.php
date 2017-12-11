<div class="wrap textbin">
	<div id="icon-textbin" class="icon32 icon32-textbin">
		<br />
	</div>
	<h2>TextBin - Add</h2>
	
	<?php if($this->msg): ?>
	<div class="updated"><p><strong><?php echo $this->msg; ?></strong></p></div>
	<?php endif; ?>
	
	<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<p><strong>Slug</strong><br />
		<input type="text" id="textbinName" name="textbinName" value="" />
		<small><em>You'll use this slug to find your text.</em></small></p>
		
		<?php the_editor('', 'textbinVal'); ?>
		
		<p><input type="checkbox" id="textbinFilter" name="textbinFilter" value="filter" /> Format Text</p>
		
		<p><input type="submit" class="button button-large button-primary" value="Save Item" name="create_textBin" /></p>
	</form>
</div>