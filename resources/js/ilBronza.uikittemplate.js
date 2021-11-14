/*

add this to package.json dependencies list

        "uikit": "^3.6.21",
        "uikit-icons": "^0.5.0",
        "@fortawesome/fontawesome-free": "^5.15.3",

*/


import UIkit from 'uikit';
import Icons from 'uikit/dist/js/uikit-icons';

window.UIkit = UIkit;
UIkit.use(Icons);

require('select2');

require( './ilBronza.ajaxFetchers.js' );
require( './ilBronza.uikittemplate.utilities.js' );

