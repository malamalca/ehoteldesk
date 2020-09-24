<h1><?=__('Guests for {1} on {0}', (string)$aDate, h($counter));?></h1>

<table>
<tr>
    <th><?=__('Room');?></th>
    <th><?=__('Client');?></th>
    <th><?=__('Date span');?></th>
</tr>

<?php
    $roomId = null;
foreach ($registrations as $r) {
    if ($r->room_id != $roomId) {
?>
<tr>
<td colspan="3">&nbsp;</td>
</tr>
<?php
    }
?>
<tr>
<td><?php echo ($roomId != $r->room_id) ? h($r->room->toString()) : '&nbsp;';?></td>
<td><?=h($r->surname);?> <?=h($r->name);?></td>
<td><?=$r->start;
?> - <?=$r->end;?></td>
</tr>
<?php
$roomId = $r->room_id;
}
?>

</table>
