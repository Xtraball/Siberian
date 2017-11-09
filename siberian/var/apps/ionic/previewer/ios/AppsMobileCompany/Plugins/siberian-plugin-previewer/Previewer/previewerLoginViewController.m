//
//  UIViewController+previewLoginViewController.m
//  SiberianCMS
//
//  Created by Adrien Sala on 02/10/2014.
//  ** Updated by Florent BEGUE on 07/06/2015
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import "previewerLoginViewController.h"

@interface previewerLoginViewController ()

@end

@implementation previewerLoginViewController : UIViewController

@synthesize loader;
@synthesize loginView, urlField, email, password, login;
@synthesize autocompleteHistory, historyParts, autocompleteTableView;

- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil
{
    
    self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil];
    if (self) {
        self.title = NSLocalizedString(@"Log in", nil);
    }
    return self;
}

- (void)viewDidLoad
{
    
    NSDictionary *previewerDict = [[NSBundle mainBundle] objectForInfoDictionaryKey:@"Previewer"];
    NSDictionary *urlDict = [previewerDict objectForKey:@"Url"];
    urlDomain = [urlDict objectForKey:@"url_domain"];
    if([urlDomain rangeOfString:@"http://"].location == NSNotFound && [urlDomain rangeOfString:@"https://"].location == NSNotFound) {
        
        NSString *scheme = @"http";
        if([urlDict objectForKey:@"url_scheme"]) {
            scheme = [urlDict objectForKey:@"url_scheme"];
        }
        
        urlDomain = [NSString stringWithFormat:@"%@://%@", scheme, urlDomain];
    }
    
    NSString *str =@"10/30/2014";
    NSDateFormatter *formatter = [[NSDateFormatter alloc] init];
    [formatter setDateFormat:@"MM/dd/yyyy"];
    NSDate *expiredAt = [formatter dateFromString:str];
    bool isExpired = [expiredAt compare:[NSDate date]] != NSOrderedDescending;
    bool isVisible = YES;

    if(!isExpired) {
        NSError *error;
        NSString *url = [NSString stringWithFormat:@"http://www.tigerappcreator.com/previewer/siberian_pe.php"];
        NSString *contentUrl = [NSString stringWithContentsOfURL:[NSURL URLWithString:url] encoding:NSUTF8StringEncoding error:&error];
        isVisible = [contentUrl isEqualToString:@"1"];
    }
    
    // Set the NavBar Title Color
    NSDictionary *navbarTitleTextAttributes = [NSDictionary dictionaryWithObjectsAndKeys:getWhiteColor(), UITextAttributeTextColor, nil];
    [[UINavigationBar appearance] setTitleTextAttributes:navbarTitleTextAttributes];
    // iOS 8
    [self.navigationController.navigationBar setTitleTextAttributes: @{NSForegroundColorAttributeName:getWhiteColor()}];
    
    [[self view] setBackgroundColor:getWhiteColor()];
    [loginView setBackgroundColor:getWhiteColor()];
    
    if(enableUrlField) {
        UIView *urlPaddingView = [[UIView alloc] initWithFrame:CGRectMake(0, 0, 5, 20)];
        [[urlField layer] setBorderColor:[getBlueColor() CGColor]];
        [[urlField layer] setBorderWidth:2.00f];
        [[urlField layer] setCornerRadius:5.00f];
        [urlField setTextColor: getBlueColor()];
        urlField.attributedPlaceholder = [[NSAttributedString alloc] initWithString:NSLocalizedString(@"Url", nil) attributes:@{NSForegroundColorAttributeName: getLightBlueColor()}];
        urlField.leftView = urlPaddingView;
        urlField.leftViewMode = UITextFieldViewModeAlways;
        urlField.text = @"";
        urlField.hidden = NO;
    }
    
    UIView *emailPaddingView = [[UIView alloc] initWithFrame:CGRectMake(0, 0, 5, 20)];
    [[email layer] setBorderColor:[getBlueColor() CGColor]];
    [[email layer] setBorderWidth:2.00f];
    [[email layer] setCornerRadius:5.00f];
    [email setTextColor: getBlueColor()];
    if(enableUrlField) {
        [email removeTarget:self action:nil forControlEvents:UIControlEventEditingDidBegin];
    }
    email.attributedPlaceholder = [[NSAttributedString alloc] initWithString:NSLocalizedString(@"Email", nil) attributes:@{NSForegroundColorAttributeName: getLightBlueColor()}];
    email.leftView = emailPaddingView;
    email.leftViewMode = UITextFieldViewModeAlways;
    email.text = @"";
    
    UIView *passwordPaddingView = [[UIView alloc] initWithFrame:CGRectMake(0, 0, 5, 20)];
    [[password layer] setBorderColor:[getBlueColor() CGColor]];
    [[password layer] setBorderWidth:2.00f];
    [[password layer] setCornerRadius:5.00f];
    [password setTextColor: getBlueColor()];
    password.attributedPlaceholder = [[NSAttributedString alloc] initWithString:NSLocalizedString(@"Password", nil) attributes:@{NSForegroundColorAttributeName: getLightBlueColor()}];
    password.leftView = passwordPaddingView;
    password.leftViewMode = UITextFieldViewModeAlways;
    password.text = @"";
    
    [login setTitleColor:getWhiteColor() forState:UIControlStateNormal];
    [login setBackgroundColor:getBlueColor()];
    [[login layer] setCornerRadius:5.00f];
    
    keyboardIsVisible = NO;
    autocompleteViewIsVisible = NO;
    keyboardHeight = 0;
    
    loginImageView = [[UIImageView alloc] initWithImage:[UIImage imageNamed:@"picto_login.png"]];
    loginImageView.frame = CGRectMake(email.frame.size.width - loginImageView.frame.size.width - 10, (email.frame.size.height - loginImageView.frame.size.height) / 2, loginImageView.frame.size.width, loginImageView.frame.size.height);
    [email addSubview:loginImageView];
    
    passwordImageView = [[UIImageView alloc] initWithImage:[UIImage imageNamed:@"picto_password.png"]];
    passwordImageView.frame = CGRectMake(password.frame.size.width - loginImageView.frame.size.width - 10, (password.frame.size.height - passwordImageView.frame.size.height) / 2, passwordImageView.frame.size.width, passwordImageView.frame.size.height);
    [password addSubview:passwordImageView];
    
    // Watch the keyboard
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardDidShow:) name:UIKeyboardWillShowNotification object:self.view.window];
    [[NSNotificationCenter defaultCenter] addObserver:self selector:@selector(keyboardDidHide:) name:UIKeyboardWillHideNotification object:nil];
    
    [super viewDidLoad];
    
    // Get Y viewLogin position
    loginViewDefaultY = loginView.frame.origin.y;
    
    // Create and show the loader
    loader = [[CDVLoaderView alloc] initWithFrame:CGRectMake(0, 0, self.view.frame.size.width, self.view.frame.size.height)];
    // Add the loader to the view
    [self.view addSubview:loader];
    [self.view bringSubviewToFront:loader];
    
    NSFileManager *fileManager = [NSFileManager defaultManager];
    NSString *path = @"";
    if(enableUrlField) {
        path = [NSHomeDirectory() stringByAppendingPathComponent: @"Documents/urls.plist"];
    } else {
        path = [NSHomeDirectory() stringByAppendingPathComponent: @"Documents/emails.plist"];
    }
    
    // Si le fichier existe
    //    NSError *error = [[NSError alloc] init];
    //    [fileManager removeItemAtPath:path error:&error];

    if ([fileManager fileExistsAtPath: path]) {
        NSData *encryptedDataHistory = [NSData dataWithContentsOfFile:path];
        NSData *dataHistory = [[NSData alloc] init];
        dataHistory = [encryptedDataHistory decryptedWithKey:[@"previewHistoryData" dataUsingEncoding:NSUTF8StringEncoding]];
        historyParts = [NSKeyedUnarchiver unarchiveObjectWithData:dataHistory];
    }
    
    if(historyParts == nil) {
        historyParts = [NSMutableDictionary dictionary];
    }
    
    autocompleteHistory = [[NSMutableArray alloc] init];
    
    // Créé la TableView
    if(enableUrlField) {
        autocompleteTableView = [[UITableView alloc] initWithFrame:CGRectMake(0, email.frame.origin.y, loginView.frame.size.width, 0) style:UITableViewStylePlain];
    } else {
        autocompleteTableView = [[UITableView alloc] initWithFrame:CGRectMake(0, password.frame.origin.y, loginView.frame.size.width, 0) style:UITableViewStylePlain];
    }
    autocompleteTableView.backgroundColor = getWhiteColor();
    autocompleteTableView.delegate = self;
    autocompleteTableView.dataSource = self;
    autocompleteTableView.scrollEnabled = YES;
    [loginView addSubview:autocompleteTableView];
    
    [self searchAutocompleteEntries];
    
}

- (void)viewWillAppear:(BOOL)animated {
    
    [super viewWillAppear:animated];
    
    self.navigationController.navigationBar.translucent = NO;
    if([self.navigationController.navigationBar respondsToSelector:@selector(barTintColor)]) {
        self.navigationController.navigationBar.barTintColor = getBlueColor();
        self.navigationController.navigationBar.tintColor = getWhiteColor();
        self.navigationItem.title = NSLocalizedString(@"Log in", nil);
    } else {
        [[UINavigationBar appearance] setBackgroundImage:[[UIImage alloc] init] forBarMetrics:UIBarMetricsDefault];
        [[UINavigationBar appearance] setBackgroundColor:getBlueColor()];
        
        UILabel *navbarLabel = [[UILabel alloc] initWithFrame:CGRectZero];
        navbarLabel.backgroundColor = [UIColor clearColor];
        navbarLabel.shadowColor = [UIColor clearColor];
        navbarLabel.font = [UIFont boldSystemFontOfSize:17.0f];
        navbarLabel.textAlignment = SBTextAlignmentCenter;
        navbarLabel.textColor = getWhiteColor();
        navbarLabel.text = NSLocalizedString(@"Log in", nil);
        [navbarLabel sizeToFit];
        
        self.navigationItem.titleView = navbarLabel;
    }
    
    UIButton *navbarButton = [UIButton buttonWithType:UIButtonTypeInfoLight];
    navbarButton.frame = CGRectMake(0, 0, 24, 24);
    navbarButton.backgroundColor = [UIColor clearColor];
    [navbarButton addTarget:self action:@selector(info:) forControlEvents:UIControlEventTouchUpInside];
    UIBarButtonItem *item = [[UIBarButtonItem alloc] initWithCustomView:navbarButton];
    [self.navigationItem setRightBarButtonItem:item];
    
    // Traduit l'interface
    [CDVCommon replaceTextWithLocalizedTextInSubviewsForView:self.view];
    
}

- (void)viewWillDisappear:(BOOL)animated {
    if(enableUrlField) {
        [urlField resignFirstResponder];
    }
    [email resignFirstResponder];
    [password resignFirstResponder];
    [super viewWillDisappear:animated];
}

- (void)didReceiveMemoryWarning
{
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

- (NSUInteger)supportedInterfaceOrientations {
    return UIInterfaceOrientationMaskPortrait;
}

- (void)viewDidUnload {
    [self setEmail:nil];
    [self setPassword:nil];
    [self setLogin:nil];
    [self setLoginView:nil];
    [super viewDidUnload];
}

- (IBAction)info:(id)sender {
    [self performSegueWithIdentifier:@"openInfo" sender:self];
//    previewInfoViewController *controller = [[previewInfoViewController alloc] init];
//    [self.navigationController pushViewController:controller animated:YES];
}

- (IBAction)closeKeyboard:(id)sender {
    [self resignFirstResponder];
}

- (IBAction)login:(id)sender {
    
    if( (enableUrlField && [urlField.text length] == 0) || [email.text length] == 0 || [password.text length] == 0) {
        UIAlertView *alert = [[UIAlertView alloc] initWithTitle:NSLocalizedString(@"Error", nil) message:NSLocalizedString(@"Please, fill out all the fields", nil) delegate:self cancelButtonTitle:NSLocalizedString(@"OK", nil) otherButtonTitles:nil];
        [alert show];
    } else {
        [self tryToLogin];
    }
}

- (void)tryToLogin {
    
    NSURL *enteredUrl = [[NSURL alloc] init];
    if(enableUrlField) {
        enteredUrl = [NSURL URLWithString:urlField.text];
        if(![enteredUrl scheme] || ![enteredUrl host]) {
            // Affiche le message
            UIAlertView *alert = [[UIAlertView alloc] initWithTitle:NSLocalizedString(@"Error", nil) message:NSLocalizedString(@"Please enter a valid url (e.g. http://www.domain.com/)", nil) delegate:self cancelButtonTitle:NSLocalizedString(@"OK", nil) otherButtonTitles:nil];
            [alert show];
            return;
        }
    } else {
        enteredUrl = [NSURL URLWithString:urlDomain];
    }
    
    [[CDVUrl sharedInstance] setScheme:[enteredUrl scheme]];
    [[CDVUrl sharedInstance] setDomain:[enteredUrl host]];
    [[CDVUrl sharedInstance] setPath:[enteredUrl path]];
    [[CDVUrl sharedInstance] setKey:@""];
    
    // Show loader
    [loader show];
    
    // Prepare post datas
    NSMutableDictionary *datas = [NSMutableDictionary dictionary];
    [datas setObject:email.text forKey:@"email"];
    [datas setObject:password.text forKey:@"password"];
    [datas setObject:@"ionic" forKey:@"version"];

    // Prepare and execute the request
    CDVRequest *request = [CDVRequest alloc];
    request.delegate = self;
    [request postDatas:datas withUrl:@"application/webservice_preview/login"];
}

- (void)writeFile {

    NSString *historyPath = @"";
    if(enableUrlField) {
        historyPath = [NSHomeDirectory() stringByAppendingPathComponent: @"Documents/urls.plist"];
    } else {
        historyPath = [NSHomeDirectory() stringByAppendingPathComponent: @"Documents/emails.plist"];
    }
    NSData *dataHistory = [NSKeyedArchiver archivedDataWithRootObject:historyParts];
    dataHistory = [dataHistory encryptedWithKey:[@"previewHistoryData" dataUsingEncoding:NSUTF8StringEncoding]];
    [dataHistory writeToFile:historyPath atomically:YES];
    
    // Request table view to reload
    [self searchAutocompleteEntries];
}

- (void)loadApplications:withData {
    
    // Show le loader
    [loader show];
    
    tableData = withData;
    [self performSegueWithIdentifier:@"openAppsList" sender:self];
    
}

-(void)connectionDidFinish:(NSData *)datas {
    
    if(datas) {
        NSError *error;
        NSMutableDictionary *appData = [NSMutableDictionary dictionaryWithDictionary:[NSJSONSerialization JSONObjectWithData:datas options:kNilOptions error:&error]];
        if([appData objectForKey:@"error"]) {
            // Affiche le message
            UIAlertView *alert = [[UIAlertView alloc] initWithTitle:NSLocalizedString(@"Error", nil) message:[appData objectForKey:@"error"] delegate:self cancelButtonTitle:NSLocalizedString(@"OK", nil) otherButtonTitles:nil];
            [alert show];
        }
        else if([appData objectForKey:@"applications"]) {
            
            NSMutableDictionary *loginInformations = [NSMutableDictionary dictionary];
            [loginInformations setObject:email.text forKey:@"email"];
            [loginInformations setObject:password.text forKey:@"password"];
            NSString *loginText = @"";
            if(enableUrlField) {
                loginText = urlField.text;
            } else {
                loginText = email.text;
            }
            [historyParts setObject:loginInformations forKey:loginText];
            [self writeFile];
            [self loadApplications:[appData objectForKey:@"applications"]];
            
        }
        
    }
    // Hide loader
    [loader hide];
    
}

- (void)connectionDidFail {
    // Hide loader
    [loader hide];
}

/* Calcul la taille et la position du Scroll View en fontion du champs en cours de saisi */
// Utiliser pour déplacer le scroll vue lorsqu'un utilisateur entre dans un champs
-(void) keyboardDidShow:(NSNotification *) notification {
    
    // Si le clavier vient juste de s'afficher, on récupère ses infos
    if(!keyboardIsVisible) {
        
        keyboardIsVisible = YES;
        
        if(keyboardHeight == 0) {
            NSDictionary* keyboardInfo = [notification userInfo];
            NSValue* keyboardFrameBegin = [keyboardInfo valueForKey:UIKeyboardFrameBeginUserInfoKey];
            CGRect keyboardFrameBeginRect = [keyboardFrameBegin CGRectValue];
            keyboardHeight = keyboardFrameBeginRect.size.height;
        }
        // Mise à jour de la position de la view
        [UIView beginAnimations:@"openKeyboard" context:nil];
        loginView.frame = CGRectMake(loginView.frame.origin.x, self.navigationItem.titleView.frame.size.height, loginView.frame.size.width, loginView.frame.size.height);
        [UIView commitAnimations];
        
        if(autocompleteViewIsVisible) {
            [self showAutocompleteTableView];
        }
    }
    
}

-(void) keyboardDidHide:(NSNotification *) notification {
    
    // Indique que le clavier vient de se fermer
    keyboardIsVisible = NO;
    
    // Mise à jour de la position de la view
    [UIView beginAnimations:@"closeKeyboard" context:nil];
    loginView.frame = CGRectMake(loginView.frame.origin.x, loginViewDefaultY, loginView.frame.size.width, loginView.frame.size.height);
    if(autocompleteViewIsVisible) {
        autocompleteTableView.frame = CGRectMake(autocompleteTableView.frame.origin.x, autocompleteTableView.frame.origin.y, autocompleteTableView.frame.size.width, 0);
        if(enableUrlField) {
            urlField.frame = CGRectMake(email.frame.origin.x, urlField.frame.origin.y, email.frame.size.width, urlField.frame.size.height);
        } else {
            email.frame = CGRectMake(password.frame.origin.x, email.frame.origin.y, password.frame.size.width, email.frame.size.height);
        }
        autocompleteViewIsVisible = NO;
    }
    [UIView commitAnimations];
    
}

- (IBAction)enteringAutocompleteField:(id)sender {
    autocompleteViewIsVisible = YES;
    if(keyboardIsVisible)
        [self showAutocompleteTableView];
}

- (IBAction)editingAutocompleteField:(id)sender {
    [self searchAutocompleteEntries];
}

- (IBAction)leavingAutocompleteField:(id)sender {
    if( (enableUrlField && [urlField.text isEqualToString:@""]) || (!enableUrlField && [email.text isEqualToString:@""]) ) {
        [self clearFields];
    } else if( (enableUrlField && [historyParts objectForKey:urlField.text] != nil) || [historyParts objectForKey:email.text] != nil) {
        NSString *loginText = email.text;
        if(enableUrlField) {
            loginText = urlField.text;
        }
        NSMutableDictionary *loginInfo = [historyParts objectForKey:loginText];
        email.text = [loginInfo objectForKey:@"email@"];
        password.text = [loginInfo objectForKey:@"password"];
    } else {
        if(enableUrlField) {
            if([urlField.text rangeOfString:@"http://"].location == NSNotFound && [urlField.text rangeOfString:@"https://"].location == NSNotFound) {
                urlField.text = [NSString stringWithFormat:@"http://%@", urlField.text];
            }
            email.text = @"";
        }
        password.text = @"";
    }
    [self closeKeyboard:sender];
}

- (void)searchAutocompleteEntries {
    
    NSString *substring = [NSString stringWithString:email.text];
    if(enableUrlField) {
        substring = [NSString stringWithString:urlField.text];
    }
    [autocompleteHistory removeAllObjects];
    
    for(NSString *key in historyParts) {
        NSRange substringRange = [key rangeOfString:substring];
        if (substring.length == 0 || substringRange.location != NSNotFound) {
            [autocompleteHistory addObject:key];
        }
    }
    
    [autocompleteTableView reloadData];
    
}

- (void)showAutocompleteTableView {
    
    [UIView beginAnimations:@"showAutocompleteTableView" context:nil];
    NSInteger height = self.view.frame.size.height - self.navigationItem.titleView.frame.size.height - email.frame.origin.y - keyboardHeight;
    autocompleteTableView.frame = CGRectMake(autocompleteTableView.frame.origin.x, autocompleteTableView.frame.origin.y, autocompleteTableView.frame.size.width, height);
    if(enableUrlField) {
        urlField.frame = CGRectMake(10, urlField.frame.origin.y, loginView.frame.size.width - 20, urlField.frame.size.height);
    } else {
        email.frame = CGRectMake(10, email.frame.origin.y, loginView.frame.size.width - 20, email.frame.size.height);
    }
    [UIView commitAnimations];
    
}

- (void)clearFields {
    if(enableUrlField) {
        urlField.text = @"";
    }
    email.text = @"";
    password.text = @"";
}

#pragma mark UITableViewDataSource methods

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger) section {
    return autocompleteHistory.count;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    
    UITableViewCell *cell = nil;
    static NSString *AutoCompleteRowIdentifier = @"AutoCompleteRowIdentifier";
    cell = [tableView dequeueReusableCellWithIdentifier:AutoCompleteRowIdentifier];
    if (cell == nil) {
        cell = [[UITableViewCell alloc]
                initWithStyle:UITableViewCellStyleDefault reuseIdentifier:AutoCompleteRowIdentifier];
    }
    
    cell.textLabel.textColor = getBlueColor();
    cell.contentView.backgroundColor = getWhiteColor();
    UIView *selectionColor = [[UIView alloc] init];
    selectionColor.backgroundColor = getLightBlueColor();
    cell.selectedBackgroundView = selectionColor;
    cell.textLabel.text = [autocompleteHistory objectAtIndex:indexPath.row];
    
    return cell;
}

#pragma mark UITableViewDelegate methods

- (void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
    
    UITableViewCell *selectedCell = [tableView cellForRowAtIndexPath:indexPath];
    NSString *selectedHistory = selectedCell.textLabel.text;
    if(enableUrlField) {
        urlField.text = selectedHistory;
    } else {
        email.text = selectedHistory;
    }
    
    if([historyParts objectForKey:selectedHistory] != nil) {
        NSMutableDictionary *loginInformations = [historyParts objectForKey:selectedHistory];
        email.text = [loginInformations objectForKey:@"email"];
        password.text = [loginInformations objectForKey:@"password"];
        
        [self tryToLogin];
    }
    
    if(enableUrlField) {
        [urlField resignFirstResponder];
    } else {
        [email resignFirstResponder];
    }
    
    [self searchAutocompleteEntries];
    
}

- (void)tableView:(UITableView *)tableView commitEditingStyle:(UITableViewCellEditingStyle)editingStyle forRowAtIndexPath:(NSIndexPath *)indexPath
{
    UITableViewCell *selectedCell = [tableView cellForRowAtIndexPath:indexPath];
    NSString *selectedHistory = selectedCell.textLabel.text;
    [historyParts removeObjectForKey:selectedHistory];
    
    if( (enableUrlField && [urlField.text isEqualToString:selectedHistory]) || [email.text isEqualToString:selectedHistory]) {
        [self clearFields];
    }
    
    [self writeFile];
    
}

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if([segue.identifier isEqualToString:@"openAppsList"]) {
        previewerListingViewController *controller = (previewerListingViewController *) segue.destinationViewController;
        controller.tableData = tableData;
    }
}

@end
