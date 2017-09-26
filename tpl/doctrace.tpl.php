<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	<table width="100%" class="border">
		<tbody>
			<tr class="label">
				<td width="15%">Numero de lot</td>
				<td>
					[view.inputNlot;strconv=no]
				</td>
			</tr>
			<tr class="label">
				<td width="15%">Date</td>
				<td>
					[view.inputDate;strconv=no]
				</td>
			</tr>
			<tr class="label">
				<td width="15%">Commentaire</td>
				<td>
					[view.inputCom;strconv=no]
				</td>
			</tr>

