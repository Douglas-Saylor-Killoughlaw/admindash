<?php
require_once 'init.php';
require_once 'topnav.php';





?>

<script src="resources/moment.min.js"></script>
<script src="resources/moment-timezone-with-data-2012-2022.js"></script>
<script src="resources/vue.js"></script>
<script src="resources/lodash.core.min.js"></script>
<script src="resources/axios.min.js"></script>
<style>
.flip-list-move {
  transition: transform 1s;
}

.highlight-enter-active {
    animation: highlight 5000ms ease-out 2 alternate;
}

.ip-counter {
    display: inline-block;
    max-width: 25px;
    padding: 5px;
}

@keyframes highlight {
    0% {
        background-color: lemonchiffon;
    },
  100% {
    background-color: white;
  }
}

</style>

<div id="app-2">

<transition-group name="flip-list">
<div v-for="ip in ips" :key="ip[1]">
    <transition name="highlight" mode="out-in">
    <div :key="ip[0]" class="ip-counter">
    {{ ip[0] }}
    </div> 
    </transition>

    {{ ip[1] }}
    
</div>
</transition-group>

</div>


<script>
    var app2 = new Vue({
      el: '#app-2',
      data: {
        ips: []
    },
    computed: {

    },
    created: function() {
        var self = this
        self.populateIps();

        window.setInterval(self.populateIps, 3000);
    },
    methods: {
        populateIps: function() {
            var self = this;
            axios.get('10min_api.php')
                .then(function (response) {
                    
                    self.ips = response.data;
                })

                .catch(function (error) {
                    console.log(error);
                });;
        },
    }
})
</script>



<?php require_once 'footer.php' ?>