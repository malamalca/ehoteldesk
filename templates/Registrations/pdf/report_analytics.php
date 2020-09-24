<h1><?=__('Registrations for {1} on {0}', (string)$aDate, h($counter));?></h1>

<table>
<tr>
    <th><?=__('Room');?></th>
    <th><?=__('Date Span');?></th>

</tr>

<?php
foreach ($registrations as $r) {
?>
<tr>
<td><?=h($r->room->toString());?></td>
<td><?=$r->start;
?> - <?=$r->end;?></td>

</tr>
<?php
}
?>

</table>
