//
//  UIViewController+previewListingViewController.m
//  SiberianCMS
//
//  Created by Adrien Sala on 02/10/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import "previewerListingViewController.h"

@implementation previewerListingViewController
@synthesize noIcon, imageDownloadingQueue, imageCache;
@synthesize tableView, tableData;
@synthesize loader;

- (void)viewDidLoad {
    
    [super viewDidLoad];
    
    // Create the loader
    loader = [[CDVLoaderView alloc] initWithFrame:CGRectMake(0, 0, self.view.frame.size.width, self.view.frame.size.height)];
    // Add the loader
    [self.view addSubview:loader];
    [self.view bringSubviewToFront:loader];
    
    self.imageDownloadingQueue = [[NSOperationQueue alloc] init];
    self.imageDownloadingQueue.maxConcurrentOperationCount = 4;
    self.imageCache = [[NSCache alloc] init];
    NSURL *iconWeb = [NSURL URLWithString: [[CDVUrl sharedInstance] getImage:@"media/images/applications/no-image.png"]];
    noIcon = [UIImage imageWithData:[NSData dataWithContentsOfURL: iconWeb]];
    
    self.view.backgroundColor = getWhiteColor();
    
    UIView* tableViewBackgroundView = [[UIView alloc] init];
    tableViewBackgroundView.backgroundColor = getWhiteColor();
    [tableView setBackgroundView:tableViewBackgroundView];

    self.tableView.backgroundColor = getWhiteColor();
    
    self.navigationController.navigationBar.translucent = NO;
    
}

- (void)viewWillAppear:(BOOL)animated {
    
    [super viewWillAppear:animated];

    self.navigationController.navigationBarHidden = NO;
    
    if(isAtLeastiOS7()) {
        [[UIApplication sharedApplication] setStatusBarStyle:UIStatusBarStyleLightContent];
    }
    
    // Remet la couleur bleue après être passé par le mainViewController
    if([self.navigationController.navigationBar respondsToSelector:@selector(barTintColor)]) {
        self.navigationController.navigationBar.barTintColor = getBlueColor();
        self.navigationController.navigationBar.tintColor = getWhiteColor();
    }
    else {
        [[UINavigationBar appearance] setBackgroundImage:[[UIImage alloc] init] forBarMetrics:UIBarMetricsDefault];
        [[UINavigationBar appearance] setBackgroundColor:getBlueColor()];
    }
    
    self.navigationController.navigationBar.translucent = NO;
    if([self.navigationController.navigationBar respondsToSelector:@selector(barTintColor)]) {
        self.navigationController.navigationBar.barTintColor = getBlueColor();
        self.navigationController.navigationBar.tintColor = getWhiteColor();
        self.navigationItem.title = NSLocalizedString(@"My Apps", nil);
    }
    else {
        
        [[UINavigationBar appearance] setBackgroundImage:[[UIImage alloc] init] forBarMetrics:UIBarMetricsDefault];
        [[UINavigationBar appearance] setBackgroundColor:getBlueColor()];
        UIButton *navbarButton = [UIButton buttonWithType:UIButtonTypeCustom];
        navbarButton.frame = CGRectMake(0, 0, 80, 30);
        navbarButton.backgroundColor = [UIColor clearColor];
        [navbarButton setTitle:NSLocalizedString(@"Back", nil) forState:UIControlStateNormal];
        [navbarButton setTitleColor:getWhiteColor() forState:UIControlStateNormal];
        [navbarButton setImage:[UIImage imageNamed:@"back_arrow_white.png"] forState:UIControlStateNormal];
        [navbarButton setTitleColor:getBlueColor() forState:UIControlStateHighlighted];
        [navbarButton.titleLabel setFont:[UIFont systemFontOfSize:16.0f]];
        [navbarButton setImage:[UIImage imageNamed:@"back_arrow_black.png"] forState:UIControlStateHighlighted];
        [navbarButton addTarget:self action:@selector(back:) forControlEvents:UIControlEventTouchUpInside];
        UIBarButtonItem *item = [[UIBarButtonItem alloc] initWithCustomView:navbarButton];
        self.navigationItem.leftBarButtonItem = item;

        
        UILabel *navbarLabel = [[UILabel alloc] initWithFrame:CGRectZero];
        navbarLabel.backgroundColor = [UIColor clearColor];
        navbarLabel.shadowColor = [UIColor clearColor];
        navbarLabel.font = [UIFont boldSystemFontOfSize:17.0f];
        navbarLabel.textAlignment = SBTextAlignmentCenter;
        navbarLabel.textColor = getWhiteColor();
        self.navigationItem.titleView = navbarLabel;
        navbarLabel.text = NSLocalizedString(@"My Apps", nil);
        [navbarLabel sizeToFit];
        
    }
    
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}

- (BOOL)shouldAutorotate {
    UIDeviceOrientation deviceOrientation = (UIDeviceOrientation) [[UIDevice currentDevice] orientation];
    return (deviceOrientation == UIDeviceOrientationPortrait);
}

- (NSUInteger)supportedInterfaceOrientations
{
    return UIInterfaceOrientationMaskPortrait;
}


- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    return (NSInteger) [tableData count];
}

- (UITableViewCell *)tableView:(UITableView *)currentTableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    
    static NSString *identifier = @"appListing";
    UITableViewCell *cell = [currentTableView dequeueReusableCellWithIdentifier:identifier];
    
    if (cell == nil) {
        NSArray *nib = [[NSBundle mainBundle] loadNibNamed:@"customTableCell" owner:self options:nil];
        cell = [nib objectAtIndex:0];
    }
    
    cell.contentView.backgroundColor = getWhiteColor();
    UIView *selectionColor = [[UIView alloc] init];
    selectionColor.backgroundColor = getLightBlueColor();
    cell.selectedBackgroundView = selectionColor;
    
    NSMutableDictionary *datas = [tableData objectAtIndex:indexPath.row];
    
    NSString *iconWeb = [datas objectForKey:@"icon"];
//    iconWeb = [[CDVUrl sharedInstance] getImage:iconWeb];
    NSURL *iconWebUrl = [NSURL URLWithString: iconWeb];
    UIImage *cachedImage = [self.imageCache objectForKey:iconWebUrl];
    
    UILabel *labelView = (UILabel *)[cell viewWithTag:101];
    labelView.text = [datas objectForKey:@"name"];
    labelView.textColor = getBlueColor();
    labelView.highlightedTextColor = getBlueColor();
    
    UIImageView *imageView = (UIImageView *)[cell viewWithTag:100];
    CALayer *imageViewLayer = [imageView layer];
    [imageViewLayer setMasksToBounds:YES];
    [imageViewLayer setBorderColor:[getBlueColor() CGColor]];
    [imageViewLayer setBorderWidth:2.00f];
    [imageViewLayer setCornerRadius:7.00f];
    
    if (cachedImage) {
        imageView.image = cachedImage;
    }
    else {
        
        cell.tag = indexPath.row;
        
        imageView.image = noIcon;
        
        [self.imageDownloadingQueue addOperationWithBlock:^{
            
            NSData *iconData = [NSData dataWithContentsOfURL:iconWebUrl];
            UIImage *image = nil;
            if (iconData) image = [UIImage imageWithData:iconData];
            
            if (image) {
                
                [self.imageCache setObject:image forKey:iconWebUrl];
                
                [[NSOperationQueue mainQueue] addOperationWithBlock:^{
                    
                    if (cell.tag == indexPath.row) {
                        imageView.image = image;
                        [cell setNeedsLayout];
                    }
                    
                }];
            }
        }];
        
    }
    
    return cell;
}

- (UIImage *)cellBackgroundForRowAtIndexPath:(NSIndexPath *)indexPath
{
    NSInteger rowCount = [self tableView:[self tableView] numberOfRowsInSection:0];
    NSInteger rowIndex = indexPath.row;
    UIImage *background = nil;
    
    if (rowIndex == 0) {
        background = [UIImage imageNamed:@"cell_top.png"];
    } else if (rowIndex == rowCount - 1) {
        background = [UIImage imageNamed:@"cell_bottom.png"];
    } else {
        background = [UIImage imageNamed:@"cell_middle.png"];
    }
    
    return background;
}

- (CGFloat)tableView:(UITableView *)tableView heightForRowAtIndexPath:(NSIndexPath *)indexPath {
    return 70.0f;
}

- (void)tableView: (UITableView *)tableView didSelectRowAtIndexPath: (NSIndexPath *)indexPath {
    
    // Met à jour les données d'url de la webview
    NSDictionary *appDatas = [tableData objectAtIndex:indexPath.row];
    [CDVUrl sharedInstance].appId = [appDatas objectForKey:@"id"];
    [[CDVUrl sharedInstance] setScheme:[appDatas objectForKey:@"scheme"]];
    [[CDVUrl sharedInstance] setDomain:[appDatas objectForKey:@"domain"]];
    [[CDVUrl sharedInstance] setPath:[appDatas objectForKey:@"path"]];
    [[CDVUrl sharedInstance] setKey:[appDatas objectForKey:@"key"]];
    
    // Récupère le splashscreen depuis le cache
    splashScreenImage = [imageCache objectForKey:[[CDVUrl sharedInstance] get:@""]];
    // S'il est en cache
    if(splashScreenImage) {
        // Affiche la webview
        [self loadWebview:splashScreenImage];
    }
    else {
        
        // Réinitialise l'image
        splashScreenImage = [[UIImage alloc] init];
        
        // Show loader
        [loader show];
        // Récupère l'url du splashscreen en fonction de la taille de l'écran
        NSString *splashScreenImageUrl;
        if(isScreeniPhone5()) {
            splashScreenImageUrl = [appDatas objectForKey:@"startup_image_retina"];
        }
        else {
            splashScreenImageUrl = [appDatas objectForKey:@"startup_image"];
        }
        
        // Récupère l'image depuis le serveur
        CDVRequest *request = [CDVRequest alloc];
        request.delegate = self;
        [request loadImage:splashScreenImageUrl];
    }
}

- (void)connectionDidFinish:(NSData *)datas {

    // Récupère les données l'image
    splashScreenImage = [UIImage imageWithData:datas];
    
    if (splashScreenImage) {
        // Stock l'image en cache
        [imageCache setObject:splashScreenImage forKey:[[CDVUrl sharedInstance] get:@""]];
    }
    
    // Affiche la webview
    [self loadWebview:splashScreenImage];
    
    [loader hide];
}

- (void)connectionDidFail {
    [loader hide];
}

- (void)viewDidUnload {
    [self setTableView:nil];
    [self setTableData:nil];
    [super viewDidUnload];
}

- (void)loadWebview:(UIImage *)withImage {
    [self performSegueWithIdentifier:@"openApplication" sender:self];
}

- (IBAction)back:(id)sender {
    [self.navigationController popViewControllerAnimated:YES];
}

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if([segue.identifier isEqualToString:@"openApplication"]) {
        MainViewController *controller = (MainViewController *) segue.destinationViewController;
        controller.previewerAppDomain = [[NSString alloc] initWithFormat:@"%@://%@", [CDVUrl sharedInstance].scheme, [CDVUrl sharedInstance].domain];
        controller.previewerAppKey = [CDVUrl sharedInstance].key;
//        controller.previewerAppDomain = [[CDVUrl sharedInstance] get:@""];
//        controller.splashScreenImage = splashScreenImage;
    }
}


@end
