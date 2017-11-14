<?php
require_once 'init.php';


if (!empty($_POST['op'])) {
   if ($_POST['op'] == 'create' && !empty($_POST['ip'])) {

    try {
        $result = cfban($_POST['ip'], $_POST['notes'], $_POST['type']);
    } catch (\Exception $e) {
        echo $e->getMessage();
        exit();
    }
     header("Location: cf.php");  
     exit();

   }
}

if (!empty($_GET['op'])) {
   if ($_GET['op'] == 'remove' && !empty($_REQUEST['id'])) {

    try {
        $result = cfunban($_REQUEST['id']);
    } catch (\Exception $e) {
        echo $e->getMessage();
        exit();
    }

     header("Location: cf.php");  
     exit();

   }
}

require_once 'topnav.php';



$rules = cflist();

?>
<table class="pure-table">
    <thead>
        <tr>
            <th>CF mode</th>
            <th></th>
            <th>Notes</th>
            <th>Ip</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
<?php foreach ($rules as $rule): ?>
    <tr>
        <td>
        <?= $rule['mode'] ?>
        </td>
        <td>
        <?= $rule['status'] ?>
        </td>
        <td>
        <?= $rule['notes'] ?>
        </td>

        <td>
            <?= $rule['configuration']['value'] ?>
        </td>
        <td>
            <?= substr($rule['created_on'], 0, 10) ?>
        </td>
        <td>
            <?= l('Remove', 'cf.php?op=remove&id=' . $rule['id']) ?>
        </td>
            
    </tr>
<?php endforeach ?>
</table>
<p><i style="color:#999;font-size: smaller"><?= count($rules) ?> rules in CF</i></p>


<form action="cf.php" class="pure-form" method="post" >
    <input type="hidden"  name="op" value="create" />
    <input type="text" required name="ip" placeholder="Ip address" />
    <select name="type">
        <option value="challenge">challenge</option>
        <option value="block">block</option>
        <option value="whitelist">whitelist</option>
    </select>
    <input type="text" required name="notes" placeholder="Notes" />
    <input type="submit" class="pure-button" value="Save" />

</form>