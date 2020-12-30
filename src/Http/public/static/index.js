window.onload = function () {
    var pmd = new Vue({
        el: '#wrapper',
        data: {
            tips: "",
            message: '页面加载于 ' + new Date().toLocaleString(),
            address: '',
            socketList: {},
            processList: {}
        },
        mounted: function () {
            this.address = this.getUrlKey("address");
            this.getSocketList();
        },
        methods: {
            selectQuery: function () {
                window.location.href = "/?address=" + this.address;
            },
            getSocketList: function () {
                this.$http.post('/socketList').then(function (response) {
                    if (200 === response.status) {
                        this.socketList = response.data;
                        if (this.address === null) {
                            Object.keys(response.data).forEach(function (k) {
                                if (response.data[k]['ip'] === '127.0.0.1') {
                                    pmd.address = k;
                                }
                            });
                        }
                    } else {
                        this.tips = response.data;
                    }
                    this.getProcessList();
                }).catch(function (error) {
                    this.tips = error.data;
                });
            },
            getProcessList: function () {
                this.$http.post('/processList', {
                    address: this.address
                }).then(function (response) {
                    console.log(response);
                }).catch(function (error) {
                    console.log(error);
                });
            },
            getUrlKey(name) {
                return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.href) || [, ""])[1].replace(/\+/g, '%20')) || null;
            }
        }

    });
};