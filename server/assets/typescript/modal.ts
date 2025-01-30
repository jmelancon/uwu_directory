import {Modal} from "bootstrap";
import $ from 'jquery';

/**
 * Rather than relying on the stock alert(), which is gross and ugly,
 * we'll use the much cuter Bootstrap Modal element. This function will
 * set its contents and show it.
 */
export function showModal(title: string, message: string){
    $("#modalLabel").text(title);
    $("#modalMessage").text(message);
    window.modal.show();
}

/**
 * Clear the modal.
 */
export function resetModal(){
    // Clear and hide list
    const list = $("#modalList")
    list.children('li').remove();
    list.addClass("visually-hidden");

    const secret = $("#modalSecret");
    secret[0].innerText = "";
    secret.closest("div").addClass("visually-hidden");
}

/**
 * Add a secret to the preformatted segment in the modal and show it.
 */
export function populateModalSecret(secret: string){
    const modalSecret = $("#modalSecret");
    modalSecret[0].innerText = secret;
    modalSecret.closest("div").removeClass("visually-hidden");
}

/**
 * Fill the modalList `<ul>`. Show it too.
 */
export function populateModalList(list: string[]){
    const modalList = $("#modalList");
    list.forEach(function(entry){
        const li = document.createElement( "li" )
        li.innerText = entry;
        modalList.append(li);
    });

    modalList.removeClass("visually-hidden")
}

/**
 * Set the modal's dismiss action to change the window location.
 */
export function redirectOnModalClose(url: string){
    document.getElementById("modal")?.addEventListener("hide.bs.modal", (_e: Event) => {
        window.location.href = url;
    });
}

window.addEventListener("load", (_e) => {
    window.modal = new Modal("#modal");
});