<table id="textbinSortable" class="wp-list-table widefat fixed pages sortable" cellspacing="0">
	<thead>
		<tr>
			<th id="textbin-sort"></th>
			<th id="textbin-slug">Slug</th>
			<th id="textbin-format">Format</th>       
			<th id="textbin-content">Content</th>
			<th id="textbin-actions">Actions</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th></th>
			<th>Slug</th>
			<th>Format</th>       
			<th>Content</th>
			<th>Actions</th>
		</tr>
	</tfoot>
	<tbody>
		<?php
		if($textbins):
			foreach($textbins as $textbin):
		?>
		<tr id="textbin_<?php echo $textbin->id; ?>">
			<td class="center_content"><div class="tbHandle"><img src="<?php echo $this->img_url; ?>pan.png" width="15" height="15" /> </div></td>
			<td><?php echo $this->format($textbin->name, false); ?><br /><input type="text" value='[textbin "<?php echo stripslashes($textbin->name); ?>"]' readonly /></td>
			<td><?php if($textbin->filter) echo 'Yes'; else echo 'No'; ?></td>
			<td><?php if($textbin->filter) echo $this->format(do_shortcode($textbin->val)); else echo do_shortcode(stripslashes($textbin->val)); ?></td>
			<td>
				<a href="<?php echo $this->plugin_edit . '&id=' . $textbin->id; ?>">Edit</a> 
				&nbsp;|&nbsp; 
				<a href="<?php echo $this->plugin_del . '&id=' . $textbin->id; ?>" class="tb_delete">Delete</a></td>
		</tr>
		<?php
			endforeach;
		endif;
		?>
	</tbody>
</table>