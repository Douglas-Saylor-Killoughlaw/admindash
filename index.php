<?php
require_once 'init.php';

require_once 'topnav.php';


$start_date = empty($_REQUEST['start_date']) ? date('Y-m-d', strtotime('-1 day')) : $_REQUEST['start_date']; 
$start_time = empty($_REQUEST['start_time']) ? '00:00' : $_REQUEST['start_time'];
$end_date = empty($_REQUEST['end_date']) ? date('Y-m-d') : $_REQUEST['end_date']; 
$end_time = empty($_REQUEST['end_time']) ? '23:59' : $_REQUEST['end_time']; 


$ip = empty($_REQUEST['ip']) ? '' : $_REQUEST['ip'];
$exclude = empty($_REQUEST['exclude']) ? '' : $_REQUEST['exclude'];



$group_by = empty($_REQUEST['group_by']) ? '' : $_REQUEST['group_by'];

?>


<div class="pure-g" >
<form class="pure-form">
<div class="pure-u-5-5">
    <label>Start date</label> 
    <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>"/> 
    <input type="time" name="start_time" value="<?= htmlspecialchars($start_time) ?>"/> 

    &nbsp;&nbsp;&nbsp;&nbsp;
    <label>End date</label> 
    <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>"  /> 
    <input type="time" name="end_time" value="<?= htmlspecialchars($end_time) ?>" /> 

    <br><br>

    <input type="text" name="ip" value="<?= htmlspecialchars($ip) ?>" placeholder="Search regex" />
    <input type="text" style="width:300px" name="exclude" id="exclude_filter" placeholder="Exclude regex" value="<?= htmlspecialchars($exclude) ?>" />



    <label>Group by:</label>
    <select name="group_by">
        <option value=""> </option>
        <option value="path" <?= $group_by == 'path' ? 'selected' : '' ?> >path</option>
        <option value="useragent" <?= $group_by == 'useragent' ? 'selected' : '' ?> >useragent</option>    
        <option value="ip" <?= $group_by == 'ip' ? 'selected' : '' ?> >ip</option>  
    </select>

    <input type="submit" class="pure-button">

    <br>
    <span style="font-size:8px;color:#999;cursor:hand" onclick="document.getElementById('exclude_filter').value = '\\.css|\\.js|\\.svg|\\.jpg|\\.png|\\.gif|\\.jpeg'">Exclude .css|.js|.svg|.jpg|.png|.gif</span>
</div><br><br>

</form>
</div>


<?php
if (!empty($_REQUEST['start_time'])): ?>
<iframe src="console.php?<?=$_SERVER['QUERY_STRING'] ?>" width="100%" height="800" />

<?php endif; ?>


<?php require_once 'footer.php' ?>