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
            start: function (name) {
                this.$http.post('/start', {
                    name: name,
                    address: this.address
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.getProcessList();
                        this.tips = response.data.data.msg;
                    } else {
                        this.tips = response.data.data.msg;
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
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
                    if (200 === response.status) {
                        this.processList = response.data.data;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
            getUrlKey(name) {
                return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.href) || [, ""])[1].replace(/\+/g, '%20')) || null;
            },
            timestampToString(timestamp) {
                var date = new Date(timestamp * 1000);
                var y = date.getFullYear();
                var m = date.getMonth() + 1;
                m = m < 10 ? ('0' + m) : m;
                var d = date.getDate();
                d = d < 10 ? ('0' + d) : d;
                var h = date.getHours();
                h = h < 10 ? ('0' + h) : h;
                var minute = date.getMinutes();
                var second = date.getSeconds();
                minute = minute < 10 ? ('0' + minute) : minute;
                second = second < 10 ? ('0' + second) : second;
                return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
            },
            timeToString(value) {
                var theTime = parseInt(value); // 需要转换的时间秒
                var theTime1 = 0; // 分
                var theTime2 = 0; // 小时
                var theTime3 = 0; // 天
                if (theTime > 60) {
                    theTime1 = parseInt(theTime / 60);
                    theTime = parseInt(theTime % 60);
                    if (theTime1 > 60) {
                        theTime2 = parseInt(theTime1 / 60);
                        theTime1 = parseInt(theTime1 % 60);
                        if (theTime2 > 24) {
                            // 大于24小时
                            theTime3 = parseInt(theTime2 / 24);
                            theTime2 = parseInt(theTime2 % 24);
                        }
                    }
                }
                var result = '';
                if (theTime >= 0) {
                    result = "" + parseInt(theTime) + "秒";
                }
                if (theTime1 > 0) {
                    result = "" + parseInt(theTime1) + "分" + result;
                }
                if (theTime2 > 0) {
                    result = "" + parseInt(theTime2) + "小时" + result;
                }
                if (theTime3 > 0) {
                    result = "" + parseInt(theTime3) + "天" + result;
                }
                return result;
            }
        }

    });
};