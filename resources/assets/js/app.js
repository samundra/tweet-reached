
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

var mainApp = new Vue({
    el: '#app',
    data: {
        query: '',
        inProgress: false,
        totalCount: 0,
        error: false,
        response: null,
        links: [
            { text: 'https://twitter.com/CNN/status/915109755902988289'},
            { text: 'https://twitter.com/CNN/status/915109755902988290'},
        ]
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

