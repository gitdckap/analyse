define([
    'uiComponent',
    'ko',
    'jquery',
    'mage/url',
    'mage/storage',
    'Magento_Ui/js/modal/alert'
], function (uiComponent, ko, $, urlBuilder, storage , alert) {
    return  uiComponent.extend({
        initialize: function () {
            this._super();
            var self = this;
        },
        quoteRequest: function () {
            var checkoutUrl = urlBuilder.build('checkout');
            $.ajax({
                url: urlBuilder.build('quickrfq/quote/allowquote'),
                success: function(result) {
                    if (result.login) {
                        if(result.success){
                            window.location.href = checkoutUrl + '?type=quote';
                        } else {
                            alert({
                                title: "Quote Request Unavailable",
                                content: "We're sorry, your account is not configured to accept quote requests",
                                autoOpen: true,
                                clickableOverlay: false,
                                focus: "",
                                actions: {
                                    always: function(){
                                        console.log("modal closed");
                                    }
                                }
                            });
                        }
                    } else {
                        alert({
                            title: "Quote Request Unavailable",
                            content: "Please Login to request a quote",
                            autoOpen: true,
                            clickableOverlay: false,
                            focus: "",
                            actions: {
                                always: function(){
                                    console.log("modal closed");
                                }
                            }
                        });
                    }
                }
             });
        },
        getWebsitemode: function () {
                var checkmode = this.isgetWebsitemode();
                return true;
        },
        isgetWebsitemode: function () {
            var serviceUrl = urlBuilder.build('theme/website/mode');
            storage.get(serviceUrl).done(
                function (response) {
                    if (response.success) {
                        if(response.value=='b2b') {
                            $('#minicart-quote-request').css('display','block');
                           return true;
                        }else if(response.value=='b2c'){
                             $('#minicart-quote-request').css('display','none');
                        }else if(response.success=="true"){
                             $('#minicart-quote-request').css('display','block');
                           return true;
                        }
                    }
                }
            );
        }
    });
});
