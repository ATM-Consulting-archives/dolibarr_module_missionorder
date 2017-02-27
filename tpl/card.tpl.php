<input type="hidden" name="action" value="[view.action]" />
<table width="100%" class="border">
	<tbody>
		<tr class="ref">
			<td>[langs.trans(Ref)]</td>
			<td>[missionorder.ref]</td>
		</tr>
		
		<tr class="label">
			<td>[langs.trans(Label)]</td>
			<td>[missionorder.label]</td>
		</tr>
		
		<tr class="project">
			<td>[langs.trans(ProjectLinked)]</td>
			<td>[formproject->select_projects(-1, [missionorder.fk_project])]</td>
		</tr>
		
		<tr class="users">
			<td>[langs.trans(UsersLinked)]</td>
			<td>[view.multiselectUser]</td>
		</tr>
		
		<tr class="location">
			<td>[langs.trans(Location)]</td>
			<td>[missionorder.location]</td>
		</tr>
		
		<tr class="date_start">
			<td class="fieldrequired">[langs.trans(DateStart)]</td>
			<td>[form.select_date([missionorder.date_start], 're', 1, 1)]</td>
		</tr>
		
		<tr class="date_end">
			<td class="fieldrequired">[langs.trans(DateEnd)]</td>
			<td>[form.select_date([missionorder.date_end], 're', 1, 1)]</td>
		</tr>
		
		<tr class="reason">
			<td>[langs.trans(Reason)]</td>
			<td>[view.showReason]</td>
		</tr>
		
		<tr class="carriage">
			<td>[langs.trans(Carriage)]</td>
			<td>[view.showCarriage]</td>
		</tr>
		
		<tr class="note">
			<td>[langs.trans(Note)]</td>
			<td>[missionorder.note]</td>
		</tr>
	</tbody>
</table>

[onshow;block=begin;when [view.mode]='edit']
<div class="center">
	[onshow;block=begin;when [missionorder.id]>0]
	<input type='hidden' name='id' value='[missionorder.id]' />
	<input type="submit" value="[langs.trans(Save)]" class="button" />
	[onshow;block=end]
	[onshow;block=begin;when [missionorder.id]=0]
	<input type="submit" value="[langs.trans(CreateDraft)]" class="button" />
	[onshow;block=end]
	
	<input type="button" onclick="javascript:history.go(-1)" value="[langs.trans(Cancel)]" class="button">
</div>
[onshow;block=end]