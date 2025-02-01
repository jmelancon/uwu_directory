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
import {populateModalConfirm, resetModal, showModal} from "./typescript/modal";
import {deleteRequest} from "./typescript/comms";

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

window.addEventListener("load",(_e) => {
    window.editButtonProxy = (caller: HTMLElement) => {
        // Grab the aria-label on the button. This is how we'll identify the action.
        const ariaLabel: string|null = caller.getAttribute("aria-label");

        // Traverse outward to find the argument to provide to our function call
        const rows = caller.closest("tr")?.querySelectorAll("td");
        const table = caller.closest("table");
        if (!rows || !table)
            return;

        const argumentIndex: number = +(caller.attributes.getNamedItem("data-argument-index")?.value ?? -1);
        const argument: string = rows[argumentIndex].innerText;

        // Check if confirmation is required. If it is, we'll need to

        if (argumentIndex >= 0 && argument){
            switch (table.id){
                case "example":
                    if (ariaLabel === "Edit")
                        fetchUser(argument);
                    else if (ariaLabel === "Delete"){
                        resetModal();
                        populateModalConfirm(deleteRequest, "/api/v1/user/" + argument);
                        showModal("Delete User?", "Are you sure you want to delete this user?");
                    }
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
                        "<button class='btn bi-trash-fill' aria-label='Delete' onclick='window.editButtonProxy(this)' data-argument-index='2'/>"
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