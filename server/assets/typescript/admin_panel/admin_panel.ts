import './nav'
import './user_editor'
import './datatables'

import {fetchUser} from "./user_editor";
import {populateModalConfirm, resetModal, showModal} from "../modal";
import {deleteRequest, postRequest} from "../comms";
import {fetchMembers} from "./membership_editor";
import {prepDataTable} from "./datatables";
import {parseUrl} from "../url_parser";

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
        const context: string = caller.attributes.getNamedItem("data-argument-context")?.value ?? "";

        // Check if confirmation is required. If it is, we'll need to

        if (argumentIndex >= 0 && argument){
            switch (table.id){
                case "example":
                    if (ariaLabel === "Edit")
                        fetchUser(argument);
                    else if (ariaLabel === "Delete"){
                        resetModal();
                        populateModalConfirm(deleteRequest, parseUrl("/api/v1/user/") + argument);
                        showModal("Delete User?", "Are you sure you want to delete this user?");
                    }
                    break;
                case "view_groups_datatable":
                    if (ariaLabel === "View Members")
                        fetchMembers(argument);
                    else if (ariaLabel === "Delete"){
                        resetModal();
                        populateModalConfirm(deleteRequest, parseUrl("/api/v1/group/") + argument);
                        showModal("Delete Group?", "Are you sure you want to delete this group?");
                    }
                    break;
                case "view_services_datatable":
                    if (ariaLabel === "Reset Password"){
                        resetModal();
                        populateModalConfirm(postRequest, parseUrl(`/api/v1/service/${argument}/password`) )
                        showModal("Reset Service Credentials?", "Are you sure you want to reset this service's credentials? This will disconnect the service until its configuration is updated.")
                    }
                    else if (ariaLabel === "Delete"){
                        resetModal();
                        populateModalConfirm(deleteRequest, parseUrl(`/api/v1/service/${argument}`) )
                        showModal("Delete Service?", "Are you sure you want to delete this service?")
                    }
                    break;
                case "edit_members_datatable":
                    if (ariaLabel === "Revoke"){
                        resetModal();
                        populateModalConfirm(deleteRequest, parseUrl(`/api/v1/user/${argument}/membership/${context}`) )
                        showModal("Revoke Membership?", "Are you sure you want to revoke this user's membership?")
                    }
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
                url: parseUrl('/api/v1/table/user') ,
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
                        "<button class='btn bi-trash-fill' aria-label='Delete' onclick='window.editButtonProxy(this)' data-argument-index='0'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: parseUrl('/api/v1/table/group'),
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
                        "<button class='btn bi-trash-fill' aria-label='Delete' onclick='window.editButtonProxy(this)' data-argument-index='0'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: parseUrl('/api/v1/table/service'),
                type: "POST"
            },
        }
    );
})