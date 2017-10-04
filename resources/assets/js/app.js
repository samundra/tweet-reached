
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

window.common = {
    message: {
        noRetweet: "This tweet hasn't been retweeted.",
    }
};

var mainApp = new Vue({
    el: '#app',
    data: {
        query: '',
        inProgress: false,
        firstTime: true,
        totalCount: 0,
        tweet: {
            retweetCount: 0,
            retweeters: [],
        },
        response: null,
        showMessage: false,
        message: {},
        links: []
    },
    methods: {
        resetFields: function () {
            $('#tweet_url').popover('hide');
            $('#searchForm').removeClass('has-error');
        },
        validateInputField: function (queryField) {
            var self = this;
            if (queryField == '') {
                $('#tweet_url').popover('show');
                $('#tweet_url').focus();
                $('#searchForm').addClass('has-error');
                return false;
            }
        },
        calculate: function (event) {
            var self = this;
            self.resetFields();
            self.validateInputField(this.query);
            self.inProgress = true;
            let $calculateButton = $(event.target);

            // Disable the current element
            $calculateButton.attr("disabled", "disabled");
            $calculateButton.text("Calculating ...");

            $.ajax({
                url: 'calculate',
                data: { query: self.query },
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        self.response = response;
                        self.totalCount = response.data.sum;
                        self.links.unshift({ text: self.query });
                        self.tweet = response.data.tweet;
                        self.firstTime = false;
                        self.message = response.data.message;
                        self.showMessage = true;
                    }

                    if (response.success == false) {
                        self.message = response.data.message;
                        self.showMessage = true;
                    }
                    // Reset back to previou states
                    self.inProgress = false;
                    $calculateButton.text("Calculate");
                    $calculateButton.removeAttr("disabled");
                },
                error: function (error, response) {
                    console.log(response);
                    self.inProgress = false;
                    $calculateButton.text("Calculate");
                    $calculateButton.removeAttr("disabled");
                    $('#tweet_url').popover();
                }
            });
        }
    }
});

