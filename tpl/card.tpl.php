<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	<table width="100%" class="border">
		<tbody>
			<tr class="ref">
				<td width="25%">[langs.transnoentities(Ref)]</td>
				<td>[view.showRef;strconv=no]</td>
			</tr>

			<tr class="label">
				<td width="25%">[langs.transnoentities(Label)]</td>
				<td>[view.showLabel;strconv=no]</td>
			</tr>

			<tr class="status">
				<td width="25%">[langs.transnoentities(Status)]</td>
				<td>[missionorder.getLibStatut(1);strconv=no]</td>
			</tr>

			<tr class="project">
				<td width="25%" class="fieldrequired">[langs.transnoentities(ProjectLinked)]</td>
				<td>[view.showProject;strconv=no]</td>
			</tr>

			<tr class="users">
				<td width="25%" class="fieldrequired">[langs.transnoentities(UsersLinked)]</td>
				<td>[view.showUsers;strconv=no]</td>
			</tr>

			<tr class="location">
				<td width="25%">[langs.transnoentities(Location)]</td>
				<td>[view.showLocation;strconv=no]</td>
			</tr>

			<tr class="date_start">
				<td width="25%" class="fieldrequired">[langs.transnoentities(DateStart)]</td>
				<td>[view.showDateStart;strconv=no]</td>
			</tr>

			<tr class="date_end">
				<td width="25%" class="fieldrequired">[langs.transnoentities(DateEnd)]</td>
				<td>[view.showDateEnd;strconv=no]</td>
			</tr>

			<tr class="reason">
				<td width="25%">[langs.transnoentities(Reason)]</td>
				<td>[view.showReason;strconv=no]</td>
			</tr>

			<tr class="carriage">
				<td width="25%">[langs.transnoentities(Carriage)]</td>
				<td>[view.showCarriage;strconv=no]</td>
			</tr>

			<tr class="note">
				<td width="25%">[langs.transnoentities(Note)]</td>
				<td>[view.showNote;strconv=no]</td>
			</tr>
		</tbody>
	</table>

</div> <!-- Fin div de la fonction dol_fiche_head() -->

[onshow;block=begin;when [view.mode]='edit']
<div class="center">
	
	<!-- '+-' est l'équivalent d'un signe '>' (TBS oblige) -->
	[onshow;block=begin;when [missionorder.getId()]+-0]
	<input type='hidden' name='id' value='[missionorder.getId()]' />
	<input type="submit" value="[langs.transnoentities(Save)]" class="button" />
	[onshow;block=end]
	
	[onshow;block=begin;when [missionorder.getId()]=0]
	<input type="submit" value="[langs.transnoentities(CreateDraft)]" class="button" />
	[onshow;block=end]
	
	<input type="button" onclick="javascript:history.go(-1)" value="[langs.transnoentities(Cancel)]" class="button">
</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]!='edit']
<div class="tabsAction">
	[onshow;block=begin;when [user.rights.missionorder.write;noerr]=1]
	
		[onshow;block=begin;when [missionorder.status]=0]
		<div class="inline-block divButAction"><a href="[view.urlcard]?id=[missionorder.getId()]&action=validate" class="butAction">[langs.transnoentities(Validate)]</a></div>
		<div class="inline-block divButAction"><a href="[view.urlcard]?id=[missionorder.getId()]&action=edit" class="butAction">[langs.transnoentities(Modify)]</a></div>
		[onshow;block=end]
		
		[onshow;block=begin;when [missionorder.status]=1]
			[onshow;block=begin;when [conf.valideur.enabled;noerr]=1]
			<div class="inline-block divButAction"><a href="[view.urlcard]?id=[missionorder.getId()]&action=to_approve" class="butAction">[langs.transnoentities(SendToBeApprove)]</a></div>
			[onshow;block=end]
		<div class="inline-block divButAction"><a href="[view.urlcard]?id=[missionorder.getId()]&action=modif" class="butAction">[langs.transnoentities(Reopen)]</a></div>
		[onshow;block=end]

		
		<div class="inline-block divButAction"><a href="[view.urlcard]?id=[missionorder.getId()]&action=clone" class="butAction">[langs.transnoentities(ToClone)]</a></div>
		
		[onshow;block=begin;when [missionorder.status]-+2]
		<div class="inline-block divButAction"><a href="[view.urlcard]?id=[missionorder.getId()]&action=delete" class="butActionDelete">[langs.transnoentities(Delete)]</a></div>
		[onshow;block=end]
		
	[onshow;block=end]
</div>
[onshow;block=end]