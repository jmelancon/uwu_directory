import '@popperjs/core';

import './typescript/egg'
import './typescript/passwords'
import './typescript/forms'
import './typescript/modal'
import './typescript/flashes'
import './typescript/popover'


declare global {
    interface Window {
        validatePasswords: Function;
        flashes: Flash[];
        $: JQueryStatic;
        editButtonProxy: Function;
    }
}



// Light/Dark mode management
const colorScheme = window.matchMedia('(prefers-color-scheme: dark)');

function setColorScheme(query: Event|MediaQueryList){
    if (query instanceof Event)
        query = <MediaQueryList>query.target;

    document.documentElement.dataset.bsTheme =
        query.matches ?
            "dark" : "light";
}

setColorScheme(colorScheme);
colorScheme.addEventListener('change', setColorScheme);