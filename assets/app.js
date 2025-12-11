import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import './styles/app.css';

document.addEventListener('DOMContentLoaded', () => {
    main();
});

function main() {
    addQueryDataJson();
}

function addQueryDataJson() {
    let element = document.createElement('script');
    let data = {
        'locale': document.querySelector('html').getAttribute('lang') || 'en',
    };

    element.setAttribute('id', 'query_data');
    element.setAttribute('type', 'application/json');
    element.textContent = JSON.stringify(data);

    document.querySelector('body').append(element);
}
