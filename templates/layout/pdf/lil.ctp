<style>
    h1 {
    }
    div {
    }
    th {
        font-weight: bold;
    }
</style>
<table>
    <tr>
        <td><?=h($currentUser['company']['name']);?></td>
    </tr>
    <tr>
        <td><?=h($currentUser['company']['street']);?></td>
    </tr>
    <tr>
        <td><?=h($currentUser['company']['zip']);?> <?=h($currentUser['company']['city']);?></td>
    </tr>
</table>
<?php
	echo $this->fetch('content');