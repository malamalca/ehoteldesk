<h1><?=__('Services for {1} on {0}', (string)$aDate, $counter);?></h1>

<table>
<tr>
    <th>&nbsp;&nbsp;&nbsp;<?=__('Room');?></th>
    <th><?=__('Client');?></th>
    <th><?=__('Date span');?></th>
</tr>

<?php
$serviceId = null;
$roomId = null;
foreach ($registrations as $r) {
    if ($serviceId != $r->service_id) {
?>
<tr>
    <td colspan="3">&nbsp;</td>
</tr>
<tr>
    <th colspan="3" style="font-size: 140%;"><?=h($r->service_type->title);?></th>

</tr>
<?php
    }
?>
<tr>
<td>&nbsp;&nbsp;&nbsp;<?php echo ($roomId != $r->room_id) ? h($r->room->toString()) : '&nbsp;';?></td>
<td><?=h($r->surname);?> <?=h($r->name);?></td>
<td><?=$r->start;
?> - <?=$r->end;?></td>
</tr>
<?php
    $serviceId = $r->service_id;
    $roomId = $r->room_id;
}
?>

</table>
