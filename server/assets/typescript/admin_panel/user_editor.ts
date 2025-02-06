import {resetModal, showModal} from '../modal';
import $ from 'jquery';
export function fetchUser(username: string){
    // Interact with web API to attempt user fetch
    const landingDiv: JQuery = $("#edit_user_landing");
    const skeletonDiv: JQuery = $("#edit_user_skeleton");
    const editorDiv: JQuery = $("#edit_user_editor");

    const editorLink = <HTMLLinkElement>$("#edit_user_link")[0];
    if (editorLink)
        editorLink.click();

    skeletonDiv.removeClass("visually-hidden");
    landingDiv.addClass("visually-hidden");

    const options: JQuery.AjaxSettings = {
        url: `/api/v1/user/${username}`,
        method: "GET",
        success: (data: User) => {
            // Populate fields
            editorDiv.find("[name='firstName']").val(data.firstName);
            editorDiv.find("[name='lastName']").val(data.lastName);
            editorDiv.find("[name='email']").val(data.email);
            editorDiv.find("[name='identifier']").val(data.identifier);

            editorDiv.find(".form-check-input").removeAttr("checked");
            data.roleDNs.forEach((roleDn: string) => {
                const checkbox = <HTMLInputElement>document.getElementById(`edit_user_group_${roleDn}`);
                if (checkbox)
                    checkbox.checked = true;
            })

            editorDiv.find("button[data-request-path]").attr("data-request-path", `/api/v1/user/${data.identifier}`);

            skeletonDiv.addClass("visually-hidden");
            editorDiv.removeClass("visually-hidden");
        },
        error: () => {
            skeletonDiv.addClass("visually-hidden");
            landingDiv.removeClass("visually-hidden");

            resetModal();
            showModal(
                "Communications Error",
                "uh ohhhh, something went wrong......."
            )
        }
    }

    $.ajax(
        options
    )
}

window.addEventListener("load", (_e) => {
    const userTab: JQuery = $("#edit_user_link");
    const userEditDivs: JQuery = $("#edit_user > div");
    const userEditLanding: JQuery = $("#edit_user_landing");

    userTab[0].addEventListener("hidden.bs.tab", (_e) => {
        userEditDivs.addClass("visually-hidden");
        userEditLanding.removeClass("visually-hidden");
    })
})