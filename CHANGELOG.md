*Scratch-Card* `1.3.6` Allows for smaller win percentage with a 0.000001 step (0.000000 to 100.000000), Win rate stepped down to 1 for 100 Million (edited)

*PE* `4.12.10` `Cron` Moves paypal checkrecurrencies inside the regular cron in case it's running (otherwise the old action, will work as before) (edited)

*PE* `4.12.10` `Paypal > Invoices` Fixes `cancelled` paypal recurrencies creating duplicate invoices every day.

*SAE/MAE/PE* `4.12.10` `Cron` Moves the disk usage command inside the cron to improve backoffice dashboard loading time. (edited)

*SAE/MAE/PE* `4.12.10` `iOS > Drop-down menus` Adds the done bar & button to validate input.

*SAE/MAE/PE* `4.12.10` `Admin > Global push notifications` Fixes the global push not finding the selected apps when selecting individuals rather than `send to all`.

*SAE/MAE/PE* `4.12.10` `Apps > Images` Fixes images not allowing scrolling (regression ionic update). (edited)

*SAE/MAE/PE* `4.12.10` `Core` Fixes db export with double-quoted column defaults.

*SAE/MAE/PE* `4.12.10` `Installer` Moves cron scheduler setup at the end of the installation to prevent crash.

*SAE/MAE/PE* `4.12.10` `Feature > Discount` Fixes use this coupon not working.

*SAE/MAE/PE* `4.12.10` `Feature > Discount` Feats new layout Slider revival for discounts.

*SAE/MAE/PE* `4.12.10` `Feature > Push` Fixes push & in-app messages missing Ok/View buttons.

*SAE/MAE/PE* `4.12.10` `Apps > Background images` Changes ratio detection for tablet from 1.7 to 1.3 which should prevent phones to grab Tablet background.

*SAE/MAE/PE* `4.12.10` `Editor > Pages/Features` Fixes javascript issues when opening/closing features.

*SAE/MAE/PE* `4.12.10` `Apps > Loading` Fixes a possible loop on `loadv3` request when the first fetch is failing.

*SAE/MAE/PE* `4.12.10` `Features > CMS, Places, Inbox` Fixes a random issue, when a forms fails, all the blocks were deleted.

*SAE/MAE/PE* `4.12.10` `Features > CMS, Places, Inbox` Fixes support of `a, img & iframe` tags in Text blocks.

*SAE/MAE/PE* `4.12.10` `Features > CMS, Places, Inbox` Fixes a random issue not saving images in Text blocks & also re-ordering the named Text blocks randomly.

*SAE/MAE/PE* `4.12.10` `Backoffice > Let's Encrypt` Adds a new rule to check against API rate limit.

*SAE/MAE/PE* `4.12.10` `Editor > Features` Handles feature block size when image is broken for Flat design.

*SAE/MAE/PE* `4.12.10` `Feature > M-Commerce` Fixes an issue with Layout 9 which was redirecting customer to the Homepage.

*SAE/MAE/PE* `4.12.10` `Features > Discount/QRCoupon` Removes dependency of a third-party QRCode generation API.

*SAE/MAE/PE* `4.12.10` `App > Offline Mode` Prevents Offline mode contents updates (not the First download) while App is active to stop network throttling. (edited)

*SAE/MAE/PE* `4.12.10` `Android > APK` Creates a copy of the pks at generation time with timestamp/uuid to prevent lost.

*SAE/MAE/PE* `4.12.10` `Modules > Editor/Backoffice` Adds new icofont http://icofont.com/icons/ along with already existing FontAwesome.

*SAE/MAE/PE* `4.12.10` `Modules > Editor/Backoffice` Allows custom menus in Editor & Backoffice to use IcoFont & FontAwesome icons.
