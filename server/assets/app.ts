import {Modal} from 'bootstrap';
import $ from 'jquery';
import '@popperjs/core';

import {prepDataTable} from "./typescript/datatables";
import {fetchUser} from "./typescript/userEditor";
import './typescript/egg'
import './typescript/passwords'
import './typescript/forms'
import './typescript/modal'
import './typescript/datatables'
import './typescript/flashes'
import './typescript/popover'
import './typescript/nav'
import './typescript/userEditor'
import {fetchMembers} from "./typescript/membershipEditor";

declare global {
    interface Window {
        validatePasswords: Function;
        flashes: Flash[];
        modal: Modal;
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

window.addEventListener("load",(_e) => {
    window.$ = $;
    window.editButtonProxy = (caller: HTMLElement) => {
        const ariaLabel: string|null = caller.getAttribute("aria-label");
        const rows: JQuery = $(caller).closest("tr").children("td");
        const table: JQuery = $(caller).closest("table");
        const argumentIndex: number = +$(caller).data("argumentIndex");
        const argument: string = rows[argumentIndex].innerText;

        if (argument && table.length === 1){
            switch (table[0].id){
                case "example":
                    if (ariaLabel === "Edit")
                        fetchUser(argument);
                    break;
                case "view_groups_datatable":
                    if (ariaLabel === "View Members")
                        fetchMembers(argument);
                    break;
            }
        }
    }

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
                        "<button class='btn bi-pencil-fill' aria-label='Edit' onclick='window.editButtonProxy(this)' data-argument-index='2'/>" +
                        "<button class='btn bi-trash-fill' aria-label='Delete'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: '/api/v1/table/user',
                type: "POST"
            },
        }
    );

    prepDataTable(
        "#view_groups_datatable",
        {
            columns: [
                {data: 'name'},
                {data: 'size'},
                {
                    data: null,
                    orderable: false,
                    defaultContent:
                        "<button class='btn bi-eye-fill' aria-label='View Members' onclick='window.editButtonProxy(this)' data-argument-index='0'/>" +
                        "<button class='btn bi-trash-fill' aria-label='Delete'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: '/api/v1/table/group',
                type: "POST"
            },
        }
    );

    prepDataTable(
        "#view_services_datatable",
        {
            columns: [
                {data: 'cn'},
                {data: 'dn'},
                {
                    data: null,
                    orderable: false,
                    defaultContent:
                        "<button class='btn bi-key-fill' aria-label='Reset Password'/>" +
                        "<button class='btn bi-trash-fill' aria-label='Delete'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: '/api/v1/table/service',
                type: "POST"
            },
        }
    );
})