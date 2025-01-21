import {Modal} from 'bootstrap';
import $ from 'jquery';
import '@popperjs/core';

import {prepDataTable} from "./typescript/datatables";
import './typescript/egg'
import './typescript/passwords'
import './typescript/forms'
import './typescript/modal'
import './typescript/datatables'
import './typescript/flashes'
import './typescript/popover'

declare global {
    interface Window {
        validatePasswords: Function;
        flashes: Flash[];
        modal: Modal;
        $: JQueryStatic;
    }
}



// Set dark mode if applicable
document.documentElement.dataset.bsTheme =
    window.matchMedia("(prefers-color-scheme: dark)").matches ?
        "dark" : "light";

window.addEventListener("load",(_e) => {
    window.$ = $;

    prepDataTable(
        "#example",
        {
            columns: [
                {data: 'firstName'},
                {data: 'lastName'},
                {data: 'username'},
                {data: 'email'},
                {
                    data: null,
                    orderable: false,
                    defaultContent:
                        "<button class='btn bi-pencil-fill' aria-label='Edit'/>" +
                        "<button class='btn bi-trash-fill' aria-label='Delete'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: '/api/v1/datatables/users',
                type: "POST"
            },
        }
    );

    prepDataTable(
        "#example2",
        {
            columns: [
                {data: 'name'},
                {data: 'size'},
                {
                    data: null,
                    orderable: false,
                    defaultContent:
                        "<button class='btn bi-eye-fill' aria-label='View Members'/>" +
                        "<button class='btn bi-trash-fill' aria-label='Delete'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: '/api/v1/datatables/groups',
                type: "POST"
            },
        }
    );
})