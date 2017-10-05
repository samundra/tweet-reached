
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/*var searchForm = new Vue({
    el: '#searchForm',
    data: {
        message: {},
        query: '',
    },
    methods: {
        calculate: function (event) {
            axios.get('/calculate/?query=' + this.query)
            .then(response => {
                console.log(response.data);
            })
            .catch(e => {
                console.log(e);
            });
        }
    }
});*/

var mainApp = new Vue({
    el: '#app',
    data: {
        query: '',
        inProgress: false,
        hideResults: true,
        peopleReached: 0,
        formError: false,
        tweet: {
            retweetCount: 0,
            retweeters: [],
        },
        showMessage: false,
        message: {},
        links: []
    },
    watch : {
        formError: function (error) {
            if (error) {
                $('#tweet_url').popover('show');
                $('#tweet_url').focus();
            }
        }
    },
    methods: {
        resetFields: function () {
            var self = this;
            self.hideResults = true;
            self.formError = false;
            self.inProgress = false;
            $('#tweet_url').popover('hide');
        },
        validateInputField: function () {
            var self = this;
            if (self.query == '') {
                self.formError = true;
                $('#tweet_url').popover('show');
                $('#tweet_url').focus();
                return false;
            }
        },
        calculate: function (event) {
            var self = this;
            self.inProgress = true;
            self.resetFields();
            self.validateInputField();
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
                        self.peopleReached = response.data.peopleReached;
                        self.links.unshift({ text: self.query });
                        self.tweet = response.data.tweet;
                        self.message = response.data.message;
                        self.hideResults = false;
                    }

                    if (response.success == false) {
                        self.message = response.data.message;
                        self.error = true;
                    }

                    if (response.errors || response.exception) {
                        self.message = response.message;
                        self.error = true;
                    }

                    // Reset back to previou states
                    self.inProgress = false;
                    self.showMessage = true;
                    $calculateButton.text("Calculate");
                    $calculateButton.removeAttr("disabled");
                },
                error: function (jqXhr, options, error) {
                    const response = jqXhr.responseJSON;

                    self.message = response.errors.query.join(',');
                    self.formError = true;
                    self.showMessage = true;
                    self.hideResults = true;
                    self.inProgress = false;

                    $calculateButton.text("Calculate");
                    $calculateButton.removeAttr("disabled");
                    $('#tweet_url').popover();
                }
            });
        }
    }
});

