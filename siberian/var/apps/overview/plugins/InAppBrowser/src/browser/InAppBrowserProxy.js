cordova.define("InAppBrowser.InAppBrowserProxy", function(require, exports, module) { /*
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 */

var modulemapper = require('cordova/modulemapper');

var browserWrap, popup, navigationButtonsDiv, navigationButtonsDivInner, backButton, forwardButton, closeButton;

function attachNavigationEvents (element, callback) {
    var onError = function () {
        try {
            callback({ type: 'loaderror', url: this.contentWindow.location.href }, { keepCallback: true }); // eslint-disable-line standard/no-callback-literal
        } catch (err) {
            // blocked by CORS :\
            callback({ type: 'loaderror', url: null }, { keepCallback: true }); // eslint-disable-line standard/no-callback-literal
        }
    };

    element.addEventListener('pageshow', function () {
        try {
            callback({ type: 'loadstart', url: this.contentWindow.location.href }, { keepCallback: true }); // eslint-disable-line standard/no-callback-literal
        } catch (err) {
            // blocked by CORS :\
            callback({ type: 'loadstart', url: null }, { keepCallback: true }); // eslint-disable-line standard/no-callback-literal
        }
    });

    element.addEventListener('load', function () {
        try {
            callback({ type: 'loadstop', url: this.contentWindow.location.href }, { keepCallback: true }); // eslint-disable-line standard/no-callback-literal
        } catch (err) {
            // blocked by CORS :\
            callback({ type: 'loadstop', url: null }, { keepCallback: true }); // eslint-disable-line standard/no-callback-literal
        }
    });

    element.addEventListener('error', onError);
    element.addEventListener('abort', onError);
}

var IAB = {
    close: function (win, lose) {
        if (browserWrap) {
            // use the "open" function callback so that the exit event is fired properly
            if (IAB._win) IAB._win({ type: 'exit' });

            browserWrap.parentNode.removeChild(browserWrap);
            browserWrap = null;
            popup = null;
        }
    },

    show: function (win, lose) {
        if (browserWrap) {
            browserWrap.style.display = 'block';
        }
    },

    open: function (win, lose, args) {
        var strUrl = args[0];
        var target = args[1];
        var features = args[2];

        IAB._win = win;

        if (target === '_self' || !target) {
            window.location = strUrl;
        } else if (target === '_system') {
            modulemapper.getOriginalSymbol(window, 'window.open').call(window, strUrl, '_blank');
        } else {
            // "_blank" or anything else
            if (!browserWrap) {
                browserWrap = document.createElement('div');
                browserWrap.style.position = 'absolute';
                browserWrap.style.top = '0';
                browserWrap.setAttribute('class', 'inappbrowser-modal');
                browserWrap.style.left = '0';
                browserWrap.style.boxSizing = 'border-box';
                browserWrap.style.borderWidth = '0';
                browserWrap.style.width = '100vw';
                browserWrap.style.height = '100vh';
                browserWrap.style.zIndex = '65536';
                browserWrap.style.backgroundColor = 'lightgrey';

                browserWrap.onclick = function () {
                    setTimeout(function () {
                        IAB.close();
                    }, 0);
                };

                document.body.appendChild(browserWrap);
            }

            if (features.indexOf('hidden=yes') !== -1) {
                browserWrap.style.display = 'none';
            }

            popup = document.createElement('iframe');
            popup.setAttribute('rel', 'iab-iframe');
            popup.style.border = '0';
            popup.style.width = '100%';
            popup.style.margin = '0';
            popup.style.width = '100%';
            popup.style.top = '0';
            popup.style.left = '0';
            popup.style.position = 'absolute';
            popup.style.background = 'url(/app/sae/design/desktop/flat/images/customization/ajax/ajax-loader-black.gif) white';
            popup.style.backgroundRepeat = 'no-repeat';
            popup.style.backgroundPosition = '50% 50%';

            browserWrap.appendChild(popup);

            if (features.indexOf('location=yes') !== -1 || features.indexOf('location') === -1) {
                popup.style.height = 'calc(100% - 28px)';

                navigationButtonsDiv = document.createElement('div');
                navigationButtonsDiv.setAttribute('class', 'iab-footer-nav');
                navigationButtonsDiv.style.zIndex = '999';
                navigationButtonsDiv.style.height = '28px';
                navigationButtonsDiv.style.backgroundColor = '#e0e0e0';
                navigationButtonsDiv.style.position = 'absolute';
                navigationButtonsDiv.style.bottom = '0';
                navigationButtonsDiv.style.left = '0';
                navigationButtonsDiv.style.width = '100%';

                navigationButtonsDiv.onclick = function (e) {
                    e.cancelBubble = true;
                };

                navigationButtonsDivInner = document.createElement('div');
                navigationButtonsDivInner.style.marginTop = '-3px';
                navigationButtonsDivInner.style.height = '28px';
                navigationButtonsDivInner.style.zIndex = '999';
                navigationButtonsDivInner.onclick = function (e) {
                    e.cancelBubble = true;
                };

                backButton = document.createElement('button');
                backButton.setAttribute('rel', 'iab-button');
                backButton.setAttribute('class', 'inappbrowser-footer-back iab-footer-button');
                backButton.style.border = '0';
                backButton.style.background = 'none';
                backButton.style.fontSize = '24px';
                backButton.style.color = '#147efb';
                backButton.style.marginLeft = '20px';

                backButton.innerHTML = '<i class="icon ion-ios-arrow-back"></i>';
                backButton.addEventListener('click', function (e) {
                    if (popup.canGoBack) {
                        popup.goBack();
                    }
                });

                forwardButton = document.createElement('button');
                forwardButton.setAttribute('rel', 'iab-button');
                forwardButton.setAttribute('class', 'inappbrowser-footer-forward iab-footer-button');
                forwardButton.style.border = '0';
                forwardButton.style.background = 'none';
                forwardButton.style.fontSize = '24px';
                forwardButton.style.color = '#147efb';

                forwardButton.innerHTML = '<i class="icon ion-ios-arrow-forward"></i>';
                forwardButton.addEventListener('click', function (e) {
                    if (popup.canGoForward) {
                        popup.goForward();
                    }
                });

                closeButton = document.createElement('button');
                closeButton.setAttribute('rel', 'iab-button');
                closeButton.setAttribute('class', 'inappbrowser-footer-close iab-footer-button');
                closeButton.style.border = '0';
                closeButton.style.background = 'none';
                closeButton.style.fontSize = '24px';
                closeButton.style.color = '#147efb';
                closeButton.style.float = 'right';
                closeButton.style.marginRight = '20px';

                closeButton.innerHTML = '<i class="icon ion-android-close"></i>';
                closeButton.addEventListener('click', function (e) {
                    setTimeout(function () {
                        IAB.close();
                    }, 0);
                });

                // iframe navigation is not yet supported
                backButton.disabled = true;
                forwardButton.disabled = true;

                navigationButtonsDivInner.appendChild(backButton);
                navigationButtonsDivInner.appendChild(forwardButton);
                navigationButtonsDivInner.appendChild(closeButton);
                navigationButtonsDiv.appendChild(navigationButtonsDivInner);

                browserWrap.appendChild(navigationButtonsDiv);
            } else {
                popup.style.height = '100%';
            }

            // start listening for navigation events
            attachNavigationEvents(popup, win);

            popup.src = strUrl;
        }
    },

    injectScriptCode: function (win, fail, args) {
        var code = args[0];
        var hasCallback = args[1];

        if (browserWrap && popup) {
            try {
                popup.contentWindow.eval(code);
                if (hasCallback) {
                    win([]);
                }
            } catch (e) {
                console.error('Error occured while trying to injectScriptCode: ' + JSON.stringify(e));
            }
        }
    },

    injectScriptFile: function (win, fail, args) {
        var msg = 'Browser cordova-plugin-inappbrowser injectScriptFile is not yet implemented';
        console.warn(msg);
        if (fail) {
            fail(msg);
        }
    },

    injectStyleCode: function (win, fail, args) {
        var msg = 'Browser cordova-plugin-inappbrowser injectStyleCode is not yet implemented';
        console.warn(msg);
        if (fail) {
            fail(msg);
        }
    },

    injectStyleFile: function (win, fail, args) {
        var msg = 'Browser cordova-plugin-inappbrowser injectStyleFile is not yet implemented';
        console.warn(msg);
        if (fail) {
            fail(msg);
        }
    }
};

module.exports = IAB;

require('cordova/exec/proxy').add('InAppBrowser', module.exports);

});
