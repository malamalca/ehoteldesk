<?php
    use Cake\I18n\Date;
?>
<h1><?=__('Booked Rooms for {0}', $counter);?></h1>

<table>
<?php
    $totalRooms = 0;
foreach ($days as $day => $rooms) {
    $curDay = new Date($day);
    if (!$rooms->isEmpty()) {
?>
<tr>
    <td colspan="2"><?= $curDay; ?></td>
</tr>
<?php
    foreach ($rooms as $r) {
?>
<tr>
    <td>&nbsp;&nbsp;&nbsp;<?=h($r->toString());?></td>
    <td><?= $r->beds_sold; ?></td>
</tr>
<?php
$totalRooms += 1;
}
}
}
?>

<tr>
    <td colspan="2">&nbsp;</td>
</tr>
<tr>
    <td colspan="2"><hr /></td>
</tr>

<tr>
    <td colspan="2"><?=__('Total Room Count');?>:
    <?= $totalRooms; ?></td>
</tr>


</table>
