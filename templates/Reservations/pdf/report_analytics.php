<h1><?=__('Reservations for {1} on {0}', (string)$aDate, $counter);?></h1>

<table>
<tr>
    <th><?=__('Room');?></th>
    <th><?=__('Date Span');?></th>

</tr>

<?php
foreach ($reservations as $r) {
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
