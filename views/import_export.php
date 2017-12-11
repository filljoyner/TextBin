<div class="wrap textbin">
	<div id="icon-textbin" class="icon32 icon32-textbin">
		<br />
	</div>
	<h2>TextBin - Import/Export</h2>
	
	<p>This feature <strong>ONLY IMPORTS TEXT</strong> and does not import any images/assets linked to within the text.</p>
	
	
	<?php if($this->msg): ?>
	<div class="updated"><p><strong><?php echo $this->msg; ?></strong></p></div>
	<?php endif; ?>
	
	
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data" method="post">
		<p>
			<input type="submit" class="button button-large button-primary" value="Export" name="export_textBin" />
			<a href="#" id="btn_textbin_import" class="button button-large button-secondary">Import</a>
		</p>
		
		<div id="textbin_input_form_fields" class="tb_hide">
			<p>Select a .txtbin file to import</p>
			<input type="file" name="file">
			<input type="submit" class="button button-primary" value="Import File" name="import_textBin" />
		</div>
	</form>
</div>