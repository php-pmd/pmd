window.onload = function () {
    var pmd = new Vue({
        el: '#wrapper',
        data: {
            tips: "",
            nowTime: new Date().toLocaleString(),
            message: '页面加载于 ' + new Date().toLocaleString(),
            socketList: {},
            add: {
                name: '',
                ip: '',
                port: '',
                app_key: '',
                app_secret: ''
            }
        },
        mounted: function () {
            this.getSocketList();
            setInterval(function () {
                pmd.getSocketList();
                pmd.message = '页面加载于 ' + new Date().toLocaleString();
            }, 1000);
        },
        methods: {
            back: function () {
                window.location.href = "/";
            },
            addSocket: function () {
                this.$http.post('/addSocket',
                    this.add
                ).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error.bodyText;
                });
            },
            del: function (addr) {
                this.$http.post('/delSocket', {
                    addr: addr,
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error.bodyText;
                });
            },
            getSocketList: function () {
                this.$http.post('/socketList').then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.socketList = response.data.data;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error.bodyText;
                });
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