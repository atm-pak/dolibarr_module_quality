<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	<table width="100%" class="border">
		<tbody>
			<tr class="label">
				<td width="25%">[langs.transnoentities(TrackTitle)]</td>
				<td>
					[view.showTitle;strconv=no]
				</td>
			</tr>

			<tr class="status">
				<td width="25%">[langs.transnoentities(TrackAuthor)]</td>
				<td>
					[view.showAuthor;strconv=no]
				</td>
			</tr>
			
			<tr class="status">
				<td width="25%">[langs.transnoentities(TrackType)]</td>
				<td>
					[view.showType;strconv=no]
				</td>
			</tr>
			
			<tr class="status">
				<td width="25%">[langs.transnoentities(TrackBitrate)]</td>
				<td>
					[view.showBitrate;strconv=no]
				</td>
			</tr>
			
			[onshow;block=begin;when [view.mode]='edit']
			<tr class="status">
				<td width="25%">[langs.transnoentities(AssociatedPlaylist)]</td>
				<td>
					[view.showPlaytlists;strconv=no]
				</td>
			</tr>
			[onshow;block=end]
		</tbody>
	</table>

</div> <!-- Fin div de la fonction dol_fiche_head() -->

[onshow;block=begin;when [view.mode]='edit']
<div class="center">
	
	<!-- '+-' est l'équivalent d'un signe '>' (TBS oblige) -->
	[onshow;block=begin;when [object.getId()]+-0]
	<input type='hidden' name='id' value='[object.getId()]' />
	<input type="submit" value="[langs.transnoentities(Save)]" class="button" />
	[onshow;block=end]
	
	[onshow;block=begin;when [object.getId()]=0]
	<input type="submit" value="[langs.transnoentities(CreateTrack)]" class="button" />
	
	[onshow;block=end]
	
	<input type="button" onclick="javascript:history.go(-1)" value="[langs.transnoentities(Cancel)]" class="button">
	
</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]!='edit']
<div class="tabsAction">
	
		<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.getId()]&action=edit" class="butAction">[langs.transnoentities(Modify)]</a></div>
		<div class="inline-block divButAction"><a onclick="if (!confirm('Sur ?')) return false;" href="[view.urlcard]?id=[object.getId()]&action=delete" class="butAction">[langs.transnoentities(Delete)]</a></div>
	
</div>
[onshow;block=end]