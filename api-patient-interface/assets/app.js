import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './styles/custom.css';
import './styles/hostlar-main.css';
import './js/hostlar-app.js';

// CSS + JS de bootstrap-select
import 'bootstrap-select/dist/css/bootstrap-select.min.css';
import 'bootstrap-select';

import './js/custom.js';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
