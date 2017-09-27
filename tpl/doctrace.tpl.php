<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	[view.hiddenIdProd;strconv=no]
	[view.hiddenSendIt;strconv=no]
	[view.hiddenEntity;strconv=no]
	<table width="100%" class="border">
		<tbody>
			<tr class="label">
				<td width="15%" class="fieldrequired">Numero de lot</td>
				<td>
					[view.inputNlot;strconv=no]
				</td>
			</tr>
			<tr class="label">
				<td width="15%" class="fieldrequired">Date</td>
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
			<tr class="label">
				<td width="15%">Fichier joint</td>
				<td>
					[view.inputFile;strconv=no]
				</td>
			</tr>
		</tbody>
	</table>
	<div class="center">
		[view.btSubmit;strconv=no]
		<input type="button" onclick="javascript:history.go(-1)" value="[langs.transnoentities(Cancel)]" class="button">
	</div>
</div>

