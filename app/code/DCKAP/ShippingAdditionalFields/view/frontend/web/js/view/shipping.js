define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'Dckap_ShippingAdditionalFields/js/warehouse/service/warehouse-service',
        'mage/url',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/action/get-payment-information',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service',
        "mage/calendar"
    ],function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        warehouseService,
        url,
        totals,
        getPaymentInformationAction,
        registry,
        $t
    ) {
        'use strict';
        var mdsConfig = window.checkoutConfig.mdsConfiguration;
         $("#ddi-pickup-date").calendar({
            showsTime: false,
            hideIfNoPrevNext: true,
            buttonText: "<?php echo __('Select Date') ?>"
        });


       /* setTimeout(function() {
                //$('table.table-checkout-shipping-method > tbody > tr:first').css("display","none");
                $('input[value="ddistorepickup_ddistorepickup"]').parents('tr').css("display","none");
            }, 5000);*/


        var mixin = {

            defaults: {
                template: 'Dckap_ShippingAdditionalFields/shipping',
                mdsStartTime: [],
                mdsCloseTime: [],
                storePickupStartTime: [],
                storePickupCloseTime: [],
                localWeekend: [],
            },
            isStorePickup: ko.observable(false),
            initialize: function (config) {

                this.warehouseCollection = ko.observableArray();
                this.getMdsTruckShippingMessage = ko.observable();
                this.selectedWarehouse = ko.observable();
                this.warehouseEntireDetails = ko.observableArray();
                this.mdsTruckAlertMessage = ko.observable(false);
                this.mdsTruckAlert = ko.observable('');
                this.mdsTrucknextDayDelivery = ko.observable(0);
                this.storeCustomDate = ko.observable();
                this.mdsCustomDate = ko.observable();

                
                this.shipping_comment = ko.observable();
                
                this._super();

                var self = this;
              
                quote.shippingMethod.subscribe(function () {
                   var all_rates = shippingService.getShippingRates();
                   
                    if (quote.shippingMethod().method_code == "ddistorepickup") {
                        if (all_rates().length == 1) {
                            if($('#flagformsg').val()=='0'){

                            $('.no-shippment-block').css('display','block');
                        }else{
                            $('.customize-pickup').css('display','inline-block;');
                           $('.no-shippment-block').css('display','none'); 
                        }
                            self.isStorePickup(false);
                             
                        } else {

                            self.isStorePickup(true);
                        }
                   
                    } else {
                        self.isStorePickup(false);
                    }
                });
            },

            initObservable: function () {
                this._super();

               quote.shippingMethod.subscribe(
                    function (method) {
                        var selectedMethods = method != null ? method.carrier_code + '_' + method.method_code : null;
                       
                    }, this
                );

                this.selectedMethod = ko.computed(
                    function () {
                        var method = quote.shippingMethod();
                        var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                        return selectedMethod;
                    }, this
                );

                this.selectedWarehouse.subscribe(
                    function (warehouse) {
                        var nextDayDelivery = this.warehouseEntireDetails()[5];
                        var currentDate = this.warehouseEntireDetails()[6];
                        var flag = false;
                        var mdsTruckAlertMessage = this.mdsTruckAlertMessage;

                        $.each(
                            nextDayDelivery, function (i, val) {
                                if (val == warehouse) {
                                    mdsTruckAlertMessage(true);
                                } else {
                                    if (quote.totals().grand_total < 1000 || currentDate > 11) {
                                        mdsTruckAlertMessage(true);
                                    } else {
                                        mdsTruckAlertMessage(false);
                                    }
                                }
                            }, this
                        );
                    }, this
                );


                this.mdsTruckAlertMessage = ko.computed(
                    {
                        read: function () {
                            var selectedWarehouse = this.selectedWarehouse();
                            var nextDayDelivery = this.warehouseEntireDetails()[5];
                            var flag = false;
                            $.each(
                                nextDayDelivery, function (i, val) {
                                    if (val == selectedWarehouse) {
                                        flag = true;
                                        return false;
                                    } else {
                                        flag = false;
                                    }
                                }, this
                            );

                            var currentDate = this.warehouseEntireDetails()[6];
                            if (quote.totals().grand_total < 1000 || currentDate > 11 || flag) {
                                this.mdsTruckAlert("The Items from the Selected Warehouse will be delivered Next Day.");
                                this.mdsTrucknextDayDelivery(1);
                                return true;
                            } else {
                                this.mdsTruckAlert("");
                                this.mdsTrucknextDayDelivery(0);
                                return false;
                            }

                        },
                        write: function (newValue) {
                            if (newValue) {
                                this.mdsTruckAlert("The Items from the Selected Warehouse will be delivered Next Day.");
                                this.mdsTrucknextDayDelivery(1);
                            } else {
                                this.mdsTruckAlert("");
                                this.mdsTrucknextDayDelivery(0);
                            }
                        }
                    }, this
                );
                this.initMage();

                return this;
            },

            initMage: function(element) {
               
            },

            getMdsStartTime: function() {
                return parseInt(this.mdsStartTime);
            },

            getMdsCloseTime: function() {
                return parseInt(this.mdsCloseTime);
            },

            getStorePickupStartTime: function() {
                return parseInt(this.storePickupStartTime[0]);
            },

            getStorePickupCloseTime: function() {
                return parseInt(this.storePickupCloseTime[0]);
            },

            getLocalWeekend: function() {
                return this.localWeekend;
            },

            getMdsTruckMinDate: function() {
                var currentDate = new Date(), currentTime, currentDay, i, localeWeekend = this.getLocalWeekend(), startTime = this.getMdsStartTime();
                currentTime = currentDate.getHours();

                if(currentTime >= this.getMdsCloseTime()){
                    currentDate = new Date(currentDate.setDate(currentDate.getDate() + 1));
                }
                currentDay = currentDate.getDay();

                var dateFactor = function(currentDate) {

                    if(localeWeekend.indexOf(currentDay) >=0 ) {
                        currentDate = new Date(currentDate.setDate(currentDate.getDate() + 1));
                        currentDay = currentDate.getDay();
                        return dateFactor(currentDate);
                    }
                    return currentDate;
                };
                currentDate = dateFactor(currentDate);
                currentDate.setHours(startTime);
                currentDate.setMinutes(0);
                return currentDate;
            },

            getStorePickupMinDate: function() {
                var currentDate = new Date(), currentTime, currentDay, i, localeWeekend = this.getLocalWeekend(), startTime = this.getStorePickupStartTime();
                currentTime = currentDate.getHours();

                if(currentTime >= this.getStorePickupCloseTime()){
                    currentDate = new Date(currentDate.setDate(currentDate.getDate() + 1));
                }
                currentDay = currentDate.getDay();
                var dateFactor = function(currentDate) {
                    if(localeWeekend.indexOf(currentDay) >=0 ) {
                        currentDate = new Date(currentDate.setDate(currentDate.getDate() + 1));
                        currentDay = currentDate.getDay();
                        return dateFactor(currentDate);
                    }
                    return currentDate;
                };
                currentDate = dateFactor(currentDate);
                currentDate.setHours(startTime);
                currentDate.setMinutes(0);
                return currentDate;
            },

            getTermsAndCondition: function () {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: "Terms and Conditions for the Same Day Delivery"
                };
                var content = "<p>Orders must be greater than or equal to $1000</p><p>Orders must be placed before 11 am EST</p><p>Delivery address should be less than 60 miles from the selected warehouse location</p>";

                var popupdata = $('<div class="modal-same-day-delivery" />').append(content);
                modal(options, popupdata);
                popupdata.modal('openModal');
            },

            getWarehouseCollection: function () {
                warehouseService.getWarehouseList(this);
            },

            setWarehouseCollectionList: function (list) {
                this.warehouseEntireDetails(list);
                this.getMdsTruckShippingMessage(list[4]);
                this.warehouseCollection(list[3]);
            },

            getMdsCustomShippingMessage: function () {
                return 'Address outside of MDS Truck Routes.';
            },

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                if (this.validateDdiShipping()) {
                    if (this.validateShippingInformation()) {
                        setShippingInformationAction().done(
                            function () {
                                stepNavigator.next();
                            }
                        );
                    }
                }
            },


            validateDdiShipping: function () {
                if(!quote.shippingMethod()){
                     this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );
                return false;
                }
                var shippingMethod = quote.shippingMethod().method_code+'_'+quote.shippingMethod().carrier_code;
                
                if (!quote.shippingMethod() || ($('#customize-shipping').hasClass('active')&& shippingMethod == "ddistorepickup_ddistorepickup")) {
                this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );
                return false;
                }

                if (this.source.get('mdsShippingMethod') && shippingMethod == "mdstruckshipping_mdstruckshipping") {
                    var mds_pref_warehouse = $('select[name="mds_truck_shipping[mds_pref_warehouse]"]').val();
                    var mds_pref_delivery_date = $('input[name="mds_truck_shipping[mds_pref_delivery_date]"]').val();
                    this.source.set('params.invalid', false);
                    this.source.trigger('mdsShippingMethod.data.validate');
                    if (mds_pref_warehouse == 0) {
                        $("#error-pre-warehouse").css('display','block');
                        $("#error-pre-warehouse-span").text("This is a required field.");
                    } else {
                        $("#error-pre-warehouse").css('display','none');
                    }
                    if (mds_pref_delivery_date == '') {
                        $("#error-pre-delivery").css('display','block');
                        $("#error-pre-delivery-span").text("This is a required field.");
                    } else {
                        $("#error-pre-delivery").css('display','none');
                    }
                    if (this.source.get('params.invalid') || mds_pref_warehouse == 0 || mds_pref_delivery_date == '') {
                        return false;
                    }
                }

                if (shippingMethod == "ddistorepickup_ddistorepickup") {
                    $('.no-shippment-block').css('display','none');
                    var err=0;
                    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                    var email = $('input[name="ddi_store_pickup[ddi_delivery_contact_email]"]').val();
                    
                    var contact = $('input[name="ddi_store_pickup[ddi_delivery_contact_no]"]').val();
                    var prefwarehouse = $('#warehouselist').val();
                    var daterequireconfig = $.parseJSON(window.checkoutConfig.pickupdatereq);
                    var pickupdateconfig = $.parseJSON(window.checkoutConfig.pickupdate);
                    if(pickupdateconfig == 1 ){
                        var pickupdate = $('#ddi_pickup_date').val();
                    }else{
                        var pickupdate = "";
                    }

                    this.source.set('params.invalid', false);
                    this.source.trigger('ddiStorePickupShippingMethod.data.validate');
                    if (email == "" && contact=="") {
                        var err=1;
                        $("#error-ddi-contact").css('display','block');
                        $("#error-ddi-contact-span").text("Please Enter your Email and/or Mobile No.");
                    } 
                    else {
                        $("#error-ddi-contact").css('display','none');
                    }

                    if(daterequireconfig == 1 && pickupdateconfig == 1){
                        if (pickupdate == "") {
                            var err=1;
                            $("#error-ddi-pickup-date").css('display','block');
                            $("#error-ddi-contact-pickup-date").text("Please Enter Your Pickup Date.");
                        }
                        else {
                            $("#error-ddi-pickup-date").css('display','none');
                        }
                    }

                    if (prefwarehouse == "") {
                        var err=1;
                        $("#error-pre-warehouse").css('display','block');
                        $("#error-pre-warehouse-span").text("Please Enter Your Preferred Warehouse .");
                    } 
                    else {
                        $("#error-pre-warehouse").css('display','none');
                    }

                   if(email!='' && !reg.test(email)){
                        var err=1;
                       $("#error-ddi-email").css('display','block');
                       $("#error-ddi-email-span").text("Invalid Email"); 
                    }else{
                        $("#error-ddi-email").css('display','none');

                    }
                    if (this.source.get('params.invalid') || err==1 ) {
                        $('.customize-pickup').trigger("click");
                        return false;
                    }
                }
                return true;
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);