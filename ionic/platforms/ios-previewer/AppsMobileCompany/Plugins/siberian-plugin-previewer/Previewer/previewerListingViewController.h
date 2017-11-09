//
//  UIViewController+previewListingViewController.h
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
#import "MainViewController.h"

@interface previewerListingViewController : UIViewController <UITableViewDelegate, UITableViewDataSource, CDVRequest> {
    NSArray *tableData;
    UIImage *splashScreenImage;
}

@property (strong, nonatomic) IBOutlet CDVLoaderView *loader;

@property (nonatomic, strong) UIImage *noIcon;
@property (nonatomic, strong) NSOperationQueue *imageDownloadingQueue;
@property (nonatomic, strong) NSCache *imageCache;

@property (strong, nonatomic) NSArray *tableData;
@property (strong, nonatomic) IBOutlet UITableView *tableView;



- (IBAction)back:(id)sender;

@end
