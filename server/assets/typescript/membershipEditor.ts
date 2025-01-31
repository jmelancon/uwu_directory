import $ from 'jquery';
import {prepDataTable} from "./datatables";
export function fetchMembers(group: string){
    // Spawn a new datatable for the group
    const memberEditFlavorText: JQuery = $("#edit_members > p");
    memberEditFlavorText[0].innerText = `Showing members of group "${group}".`

    const memberEditDataTable = $("#edit_members_datatable");
    memberEditDataTable.removeClass("visually-hidden");

    const editorLink = <HTMLLinkElement>$("#edit_members_link")[0];
    if (editorLink)
        editorLink.click();

    prepDataTable(
        "#edit_members_datatable",
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
                        "<button class='btn bi-trash-fill' aria-label='Revoke'/>"
                }
            ],
            serverSide: true,
            ajax: {
                url: '/api/v1/table/group/' + encodeURI(group),
                type: "POST"
            },
        }
    );
}

window.addEventListener("load", (_e) => {
    const memberTab: JQuery = $("#edit_members_link");
    const memberEditFlavorText: JQuery = $("#edit_members > p");
    const memberEditTable: JQuery = $("#edit_members_datatable");

    memberTab[0].addEventListener("hidden.bs.tab", (_e) => {
        if (memberEditTable.hasClass("dataTable")){
            // DataTable needs to be destroyed
            memberEditTable.DataTable().destroy();
            memberEditTable.addClass("visually-hidden")
            memberEditFlavorText[0].innerText = "Pick a group from the View Groups tab to begin.";
        }
    })
})