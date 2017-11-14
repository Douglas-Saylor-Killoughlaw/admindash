<?php
require_once 'init.php';
require_once 'topnav.php';
?>

<script src="resources/moment.min.js"></script>
<script src="resources/moment-timezone-with-data-2012-2022.js"></script>
<script src="resources/vue.js"></script>

<div id="app-2">
<form class="pure-form">
    <h3>Timestamp to time</h3>
  <input type="text" v-model="timestamp" /> <br><br>
  <br><br>Local time: <input type="text" :value='timestamp_date' /> 
  <br><br>Server (UTC): <input type="text" :value='timestamp_server_date' /> 
  <br><br>Apache2 log format: <input type="text" :value='timestamp_server_date_apache' /> 
  <br><br>Delaware: <input type="text" :value='timestamp_delaware_date' /> 
  <br><br><br>

</form>
</div>


<script>
    var app2 = new Vue({
      el: '#app-2',
      data: {
        timestamp: '',
        date: ''
    },
    computed: {
        timestamp_date: function() {
            return moment.unix(this.timestamp).format('DD-MM-YYYY HH:mm:ss')
        },
        timestamp_server_date: function() {
            // "Etc/UTC|Universal"
            return moment.unix(this.timestamp).tz("Etc/UTC").format('DD-MM-YYYY HH:mm:ss')
        },
        timestamp_server_date_apache: function() {
            // "Etc/UTC|Universal"
            return moment.unix(this.timestamp).tz("Etc/UTC").format('DD/MMM/YYYY:HH:mm:ss')
        },
        timestamp_delaware_date: function() {
            // "Etc/UTC|Universal"
            return moment.unix(this.timestamp).tz('America/New_York').format('DD-MM-YYYY HH:mm:ss')
        }
    },
    created: function() {
        this.timestamp = Date.now() / 1000 | 0;
        
        var t = new Date();
        t.setSeconds( this.timestamp );
        var formatted = moment().format("YYYY-MM-DD HH:MM:ss"); // dd.mm.yyyy hh:MM:ss
        //console.log(formatted);
        this.date = formatted
    }
})
</script>



<?php require_once 'footer.php' ?>