//
//  UIViewController+previewLoginViewController.h
//  SiberianCMS
//
//  Created by Adrien Sala on 02/10/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "CDVLoaderView.h"
#import "CDVCommon.h"
#import "CDVUrl.h"
#import "CDVRequest.h"
#import "previewerInfoViewController.h"
#import "previewerListingViewController.h"
#import "CDVAES256.h"

NSString *urlDomain;

@interface previewerLoginViewController : UIViewController <CDVRequest, UITableViewDataSource, UITableViewDelegate> {

    // Loader
    CDVLoaderView *loader;
    float loginViewDefaultY;
    bool keyboardIsVisible;
    bool autocompleteViewIsVisible;
    int keyboardHeight;

    UIImageView *loginImageView;
    UIImageView *passwordImageView;
    NSArray *tableData;

}

@property (strong, nonatomic) IBOutlet CDVLoaderView *loader;

@property (strong, nonatomic) IBOutlet UIView *loginView;

@property (weak, nonatomic) IBOutlet UITextField *urlField;
@property (strong, nonatomic) IBOutlet UITextField *email;
@property (strong, nonatomic) IBOutlet UITextField *password;
@property (strong, nonatomic) IBOutlet UIButton *login;

@property (nonatomic, retain) NSMutableDictionary *historyParts;
@property (nonatomic, retain) NSMutableArray *autocompleteHistory;
@property (nonatomic, retain) UITableView *autocompleteTableView;


- (IBAction)leavingAutocompleteField:(id)sender;
- (IBAction)enteringAutocompleteField:(id)sender;
- (IBAction)editingAutocompleteField:(id)sender;
- (IBAction)closeKeyboard:(id)sender;
- (IBAction)login:(id)sender;

- (void)searchAutocompleteEntries;
- (void)loadApplications:(NSDictionary *)withData;



@end
