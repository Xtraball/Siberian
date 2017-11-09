/* global
 App, angular, lazyLoadResolver, BASE_PATH
 */

/** CART.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-cart-view', {
            url: BASE_PATH + '/mcommerce/mobile_cart/index/value_id/:value_id',
            controller: 'MCommerceCartViewController',
            templateUrl: 'templates/mcommerce/l1/cart.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** CATEGORY.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-category-list', {
            url: BASE_PATH + '/mcommerce/mobile_category/index/value_id/:value_id',
            controller: 'MCommerceListController',
            templateUrl: 'templates/html/l3/list.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        }).state('mcommerce-subcategory-list', {
            url: BASE_PATH + '/mcommerce/mobile_category/index/value_id/:value_id/category_id/:category_id',
            controller: 'MCommerceListController',
            templateUrl: 'templates/html/l3/list.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        }).state('mcommerce-redirect', {
            url: BASE_PATH + '/mcommerce/redirect/index/value_id/:value_id',
            controller: 'MCommerceRedirectController',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** PRODUCT.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-product-view', {
            url: BASE_PATH + '/mcommerce/mobile_product/index/value_id/:value_id/product_id/:product_id',
            controller: 'MCommerceProductViewController',
            templateUrl: 'templates/mcommerce/l1/product.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/CONFIRMATION.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-confirmation', {
            url: BASE_PATH + '/mcommerce/mobile_sales_confirmation/index/value_id/:value_id',
            controller: 'MCommerceSalesConfirmationViewController',
            templateUrl: 'templates/mcommerce/l1/sales/confirmation.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        }).state('mcommerce-sales-confirmation-cancel', {
            url: BASE_PATH + '/mcommerce/mobile_sales_confirmation/cancel/value_id/:value_id',
            controller: 'MCommerceSalesConfirmationCancelController',
            templateUrl: 'templates/mcommerce/l1/sales/confirmation.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        }).state('mcommerce-sales-confirmation-payment', {
            url: BASE_PATH + '/mcommerce/mobile_sales_confirmation/confirm/token/:token/payer_id/:payer_id/value_id/:value_id',
            controller: 'MCommerceSalesConfirmationConfirmPaymentController',
            templateUrl: 'templates/mcommerce/l1/sales/confirmation.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/CUSTOMER.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-customer', {
            url: BASE_PATH + '/mcommerce/mobile_sales_customer/index/value_id/:value_id',
            controller: 'MCommerceSalesCustomerViewController',
            templateUrl: 'templates/mcommerce/l1/sales/customer.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});


/** SALES/DELIVERY.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-delivery', {
            url: BASE_PATH + '/mcommerce/mobile_sales_delivery/index/value_id/:value_id',
            controller: 'MCommerceSalesDeliveryViewController',
            templateUrl: 'templates/mcommerce/l1/sales/delivery.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/ERROR.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-error', {
            url: BASE_PATH + '/mcommerce/mobile_sales_error/index/value_id/:value_id',
            controller: 'MCommerceSalesErrorViewController',
            templateUrl: 'templates/mcommerce/l1/sales/error.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/HISTORY.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-history', {
            url: BASE_PATH + '/mcommerce/mobile_sales_customer/history/value_id/:value_id',
            controller: 'MCommerceSalesHistoryViewController',
            templateUrl: 'templates/mcommerce/l1/sales/history.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        }).state('mcommerce-sales-history-details', {
            url: BASE_PATH + '/mcommerce/mobile_sales_customer/history_details/value_id/:value_id/order_id/:order_id',
            controller: 'MCommerceSalesHistoryDetailsController',
            templateUrl: 'templates/mcommerce/l1/sales/history_details.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/PAYMENT.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-payment', {
            url: BASE_PATH + '/mcommerce/mobile_sales_payment/index/value_id/:value_id',
            controller: 'MCommerceSalesPaymentViewController',
            templateUrl: 'templates/mcommerce/l1/sales/payment.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/STORE.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-store', {
            url: BASE_PATH + '/mcommerce/mobile_sales_storechoice/index/value_id/:value_id',
            controller: 'MCommerceSalesStoreChoiceController',
            templateUrl: 'templates/mcommerce/l1/sales/store.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/STRIPE.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-stripe', {
            url: BASE_PATH + '/mcommerce/mobile_sales_stripe/index/value_id/:value_id',
            controller: 'MCommerceSalesStripeViewController',
            templateUrl: 'templates/mcommerce/l1/sales/stripe.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});

/** SALES/SUCCESS.JS */
angular.module('starter').config(function ($stateProvider) {
    $stateProvider
        .state('mcommerce-sales-success', {
            url: BASE_PATH + '/mcommerce/mobile_sales_success/index/value_id/:value_id',
            controller: 'MCommerceSalesSuccessViewController',
            templateUrl: 'templates/mcommerce/l1/sales/success.html',
            cache: false,
            resolve: lazyLoadResolver('m_commerce')
        });
});
