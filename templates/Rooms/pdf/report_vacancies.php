<h1><?=__('Vacancies for {1} on {0}', (string)$aDate, $counter);?></h1>

<table>
<?php
    $roomType = null;
    $totalBeds = 0;
foreach ($rooms as $r) {
    if ($roomType != $r->room_type_id) {
?>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td colspan="2"><h2><?= h($r->room_type->title); ?></h2></td>

</tr>
<?php
    }
?>
<tr>
<td><?=h($r->toString());?></td>
<td><?= $r->beds; ?></td>
</tr>
<?php
$roomType = $r->room_type_id;
$totalBeds += $r->beds;
}
?>

<tr>
    <td colspan="2">&nbsp;</td>
</tr>
<tr>
    <td colspan="2"><hr /></td>
</tr>

<tr>
    <td><?=__('Total Free Bed Count');?>:</td>
    <td><?= $totalBeds; ?></td>
</tr>
</table>
