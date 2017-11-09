<?php
# Step 1 check binary rights
$binary_path = Core_Model_Directory::getBasePathTo("lib/Siberian/Wrapper/Sqlite/bin");

chmod("{$binary_path}/sqlite3", 0777);
chmod("{$binary_path}/sqlite3.osx", 0777);
chmod("{$binary_path}/sqlite3.exe", 0777);
chmod("{$binary_path}/sqlite3_64", 0777);

$dbPath = Core_Model_Directory::getBasePathTo("metrics/siberiancms.db");
$dbSchema = "CREATE TABLE app_installation (
    appId INTEGER NOT NULL,
    latitude REAL,
    longitude REAL,
    timestampGMT INTEGER NOT NULL,
    OS TEXT NOT NULL,
    OSVersion TEXT NOT NULL,
    Device TEXT NOT NULL,
    DeviceVersion TEXT NOT NULL,
    deviceUUID TEXT NOT NULL
);

CREATE TABLE app_loaded (
    id INTEGER PRIMARY KEY,
    appId INTEGER NOT NULL,
    latitude REAL,
    longitude REAL,
    startTimestampGMT INTEGER NOT NULL,
    endTimestampGMT INTEGER,
    OS TEXT NOT NULL,
    OSVersion TEXT NOT NULL,
    Device TEXT NOT NULL,
    DeviceVersion TEXT NOT NULL,
    locale TEXT NOT NULL,
    deviceUUID TEXT NOT NULL
);

CREATE TABLE page_navigation (
    featureId INTEGER NOT NULL,
    latitude REAL,
    longitude REAL,
    timestampGMT INTEGER NOT NULL,
    OS TEXT NOT NULL,
    OSVersion TEXT NOT NULL,
    Device TEXT NOT NULL,
    DeviceVersion TEXT NOT NULL,
    locale TEXT NOT NULL,
    deviceUUID TEXT NOT NULL
);

CREATE TABLE mcommerce_product_navigation (
    productId INTEGER NOT NULL,
    name TEXT NOT NULL,
    latitude REAL,
    longitude REAL,
    timestampGMT INTEGER,
    OS TEXT NOT NULL,
    OSVersion TEXT NOT NULL,
    Device TEXT NOT NULL,
    DeviceVersion TEXT NOT NULL,
    locale TEXT NOT NULL,
    deviceUUID TEXT NOT NULL
);

CREATE TABLE mcommerce_product_sold (
    productId INTEGER NOT NULL,
    categoryId INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    latitude REAL,
    longitude REAL,
    timestampGMT INTEGER,
    OS TEXT NOT NULL,
    OSVersion TEXT NOT NULL,
    Device TEXT NOT NULL,
    DeviceVersion TEXT NOT NULL,
    locale TEXT NOT NULL,
    deviceUUID TEXT NOT NULL
);

CREATE TABLE app_installation_daily (
    id INTEGER PRIMARY KEY,
    appId INTEGER NOT NULL,
    ios_install INTEGER NOT NULL,
    android_install INTEGER NOT NULL,
    timestampGMT INTEGER NOT NULL
);

CREATE TABLE app_loaded_daily (
    id INTEGER PRIMARY KEY,
    appId INTEGER NOT NULL,
    visits INTEGER NOT NULL,
    time_spend INTEGER NOT NULL,
    timestampGMT INTEGER NOT NULL
);

CREATE TABLE app_navigation_daily (
    id INTEGER PRIMARY KEY,
    appId INTEGER NOT NULL,
    visits INTEGER NOT NULL,
    feature_id INTEGER NOT NULL,
    timestampGMT INTEGER NOT NULL,
    featureName TEXT NOT NULL
);

CREATE TABLE app_localization_daily (
    id INTEGER PRIMARY KEY,
    appId INTEGER NOT NULL,
    latitude REAL NOT NULL,
    longitude REAL NOT NULL,
    timestampGMT INTEGER NOT NULL
);

CREATE TABLE mcommerce_product_visit_daily (
    id    INTEGER PRIMARY KEY AUTOINCREMENT,
    appId INTEGER NOT NULL,
    productId INTEGER NOT NULL,
    productName   TEXT NOT NULL,
    timestampGMT  INTEGER NOT NULL,
    visits    INTEGER NOT NULL
);

CREATE TABLE mcommerce_payment_method_daily (
    id    INTEGER,
    appId INTEGER NOT NULL,
    paymentMethodId    INTEGER,
    occurency INTEGER,
    timestampGMT  INTEGER,
    PRIMARY KEY(id)
);

CREATE TABLE mcommerce_sales_per_store_daily (
    id    INTEGER,
    appId INTEGER,
    storeId   INTEGER,
    occurency INTEGER,
    timestampGMT  INTEGER,
    PRIMARY KEY(id)
);

CREATE TABLE mcommerce_sales_per_category_daily (
    id  INTEGER,
    appId   INTEGER,
    categoryId  INTEGER,
    occurency   INTEGER,
    timestampGMT    INTEGER,
    categoryName    TEXT,
    PRIMARY KEY(id)
);

CREATE TABLE mcommerce_product_sale_count_daily (
    id    INTEGER,
    appId INTEGER,
    productId   INTEGER,
    total INTEGER,
    timestampGMT  INTEGER,
    productName  TEXT,
    PRIMARY KEY(id)
);

CREATE TABLE mcommerce_cart_average_daily (
    id    INTEGER,
    appId INTEGER,
    average INTEGER,
    timestampGMT  INTEGER,
    PRIMARY KEY(id)
);

CREATE TABLE discount_count_validation_daily (
    id    INTEGER,
    appId INTEGER,
    promotionId INTEGER,
    promotionName TEXT,
    total INTEGER,
    timestampGMT  INTEGER,
    PRIMARY KEY(id)
);

CREATE TABLE loyalty_card_daily (
    id    INTEGER NOT NULL,
    appId INTEGER NOT NULL,
    cardId    INTEGER NOT NULL,
    cardName    TEXT NOT NULL,
    timestampGMT  INTEGER NOT NULL,
    validation    INTEGER DEFAULT 0,
    rewardUsed    INTEGER  DEFAULT 0,
    averageActifUser    INTEGER  DEFAULT 0,
    averageAllUser    INTEGER DEFAULT 0,
    PRIMARY KEY(id)
);

CREATE TABLE loyalty_card_validation_per_user_daily (
    id    INTEGER NOT NULL,
    appId INTEGER NOT NULL,
    cardId    INTEGER NOT NULL,
    customerId    INTEGER NOT NULL,
    timestampGMT  INTEGER NOT NULL,
    validation    INTEGER NOT NULL,
    PRIMARY KEY(id)
);
";

$wrapperDB = Siberian_Wrapper_Sqlite::getInstance()
            ->setSchema($dbSchema)
            ->setDbPath($dbPath);

if(!$wrapperDB->dbExists()) {
    $wrapperDB->createDb();
}
