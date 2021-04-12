//
//  StartAppAd.h
//  StartAppAdSDK
//
//  Copyright (c) 2013 StartApp. All rights reserved.
//  SDK version 4.5.0

typedef NS_ENUM(NSUInteger, STAError) {
    //Unexpected error occured
    STAErrorUnexpected              = 0,
    
    //Connection issue occured
    STAErrorNoInternetConnection    = 1,
    
    //Internal error occured
    STAErrorInternal                = 2,
    
    //appID not set in STAStartAppSDK
    STAErrorAppIDNotSet             = 3,
    
    //Invalid or insufficient params set when requesting ad
    STAErrorInvalidParams           = 4,
    
    //Ad was not loaded because of internal rules
    STAErrorAdRules                 = 5,
    
    //Invalid or missing params in loaded ad
    STAErrorExpectedAdParamsMissingOrInvalid = 6,
    
    //Some of ad types are unsupported for old iOS versions
    STAErrorAdTypeNotSupported      = 7,
    
    //Failed to show ad because another ad is being shown at the moment
    STAErrorAdAlreadyDisplayed      = 8,
    
    //Failed to show ad because it has expired
    STAErrorAdExpired               = 9,
    
    //Failed to show ad because it is not ready
    STAErrorAdNotReady              = 10,
    
    //loadAd was called for native ad while it is in loading state
    STAErrorAdIsLoading             = 11,
    
    //Demand issue which means that there are no active ads at current region for requested params
    STAErrorNoContent               = 12,
};
