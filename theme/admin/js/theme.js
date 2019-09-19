
var vueNorthMenu = new Vue({
    el: '#north-menu',
    data: {
        current: ['home'],
        aboutModel: false
    },
    methods: {
        about: function() {
            this.$info({
                title: '关于',
                content: "当前BE系统版本号：v <?php echo Be::getVersion(); ?>",
                onOk: function() {}
            });
        }
    }
});


var sWestMenuCollapsedKey = '_westMenuCollapsed';
var vueWestMenu = new Vue({
    el: '#app-west',
    data : {
        current: [],
        openKeys: [],
        collapsed: this.$cookies.isKey(sWestMenuCollapsedKey) && this.$cookies.get(sWestMenuCollapsedKey) == '1'
    },
    methods: {
        toggleMenu: function (e) {
            this.collapsed = !this.collapsed;
            console.log(this.collapsed);
            document.getElementById("be-middle").style.left = this.collapsed ? "60px" : "200px";
            this.$cookies.set(sWestMenuCollapsedKey, this.collapsed ? '1' : '0', 86400 * 180);
        }
    },
    created: function () {
        if (this.collapsed) {
            document.getElementById("be-middle").style.left = "60px";
        }
    }
});

new Vue({el: '#app-center-title'});