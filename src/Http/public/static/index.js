window.onload = function () {
    var pmd = new Vue({
        el: '#wrapper',
        data: {
            tips: "",
            nowTime: new Date().toLocaleString(),
            message: '页面加载于 ' + new Date().toLocaleString(),
            address: '',
            socketList: {},
            processList: {},
            add: {
                name: 'HelloPmd',
                cmd: "echo 'Hello PMD'",
                count: 1,
                autostart: 0
            }
        },
        mounted: function () {
            this.address = this.getUrlKey("address");
            this.getSocketList();
            setInterval(function () {
                pmd.getProcessList();
                pmd.message = '页面加载于 ' + new Date().toLocaleString();
            }, 1000);
        },
        methods: {
            tail: function (name) {
                window.open("/tail?address=" + this.address + "&name=" + name);
            },
            restartall: function () {
                this.$http.post('/restartall', {
                    address: this.address
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            stopall: function () {
                this.$http.post('/stopall', {
                    address: this.address
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            restart: function (name) {
                this.$http.post('/restart', {
                    name: name,
                    address: this.address
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            start: function (name) {
                this.$http.post('/start', {
                    name: name,
                    address: this.address
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            stop: function (name) {
                this.$http.post('/stop', {
                    name: name,
                    address: this.address
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            del: function (name) {
                this.$http.post('/delete', {
                    name: name,
                    address: this.address
                }).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            addProcess: function () {
                this.add.address = this.address;
                this.$http.post('/add',
                    this.add
                ).then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        this.tips = response.data.msg;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            selectQuery: function () {
                window.location.href = "/?address=" + this.address;
            },
            getSocketList: function () {
                this.$http.post('/socketList').then(function (response) {
                    if (200 === response.status && 0 === response.data.code) {
                        if (this.address === null) {
                            for (key in response.data.data) {
                                this.address = key;
                                break;
                            }
                        }
                        this.socketList = response.data.data;
                    } else {
                        this.tips = response.data.msg;
                    }
                }).catch(function (error) {
                    this.tips = error;
                });
            },
            getProcessList: function () {
                if (this.address != null) {
                    this.$http.post('/processList', {
                        address: this.address
                    }).then(function (response) {
                        if (200 === response.status && response.data.code === 0 && response.data.data.error === undefined) {
                            this.processList = response.data.data;
                        } else {
                            this.tips = response.data.msg;
                        }
                    }).catch(function (error) {
                        this.tips = error;
                    });
                }
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