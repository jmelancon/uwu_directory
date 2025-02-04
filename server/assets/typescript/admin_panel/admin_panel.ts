import './nav'
import './userEditor'
import './datatables'

import {fetchUser} from "./userEditor";
import {populateModalConfirm, resetModal, showModal} from "../modal";
import {deleteRequest, postRequest} from "../comms";
import {fetchMembers} from "./membershipEditor";
import {prepDataTable} from "./datatables";

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
                case "view_services_datatable":
                    if (ariaLabel === "Reset Password"){
                        resetModal();
                        populateModalConfirm(postRequest, `/api/v1/service/${argument}/password`)
                        showModal("Reset Service Credentials?", "Are you sure you want to reset this service's credentials? This will disconnect the service until its configuration is updated.")
                    }
                    break
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
                        "<button class='btn bi-key-fill' aria-label='Reset Password' onclick='window.editButtonProxy(this)' data-argument-index='0'/>" +
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