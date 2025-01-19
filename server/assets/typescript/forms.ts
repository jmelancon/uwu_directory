import $ from "jquery";
import {AjaxSettings} from "datatables.net-bs5";
import {populateModalList, redirectOnModalClose, resetModal, showModal} from "./modal";
import {isHandledResponse, isListResponse, isRedirectResponse} from './types'

/**
 * Blanket tool for handling responses from AJAX requests.
 *
 * @param {object} data - Could possibly be a handled response, but also may not be!
 * @param {boolean} hasSucceeded - If the request has succeeded or not.
 * @param {int} statusCode
 */
function handleResponse(data: any, hasSucceeded: boolean, statusCode: number){
    // Clear
    resetModal()

    // Check if the response is handled.
    if (isHandledResponse(data)){
        // Check if the response has a list associated
        if (isListResponse(data)){
            populateModalList(data.listContents);
        } else if (isRedirectResponse(data)){
            redirectOnModalClose(data.url)
        }
        showModal(data.title, data.message);
    } else if (hasSucceeded) {
        showModal(
            "Success!",
            "Your request has been submitted."
        )
    } else if (statusCode === 0){
        showModal(
            "Uh oh!",
            "The server could not be reached. Please check your network connection and try again."
        )
    } else {
        showModal(
            "Uh oh!",
            `An unhandled exception has occurred. Please check your input and try again. Status code: ${statusCode}`
        )
    }
}

/**
 * Simply a passthrough function to the handleResponse function.
 *
 * @param {object} data - Parsed response from the server, if applicable
 * @param {string} _textStatus - A string describing the response status
 * @param {jqXHR} jqXHR - The jQuery XHR response object
 */
function handleSuccess(data: object, _textStatus: string, jqXHR: JQueryXHR) {
    handleResponse(data, true, jqXHR.status);
}

/**
 * Simply a passthrough function to the handleResponse function.
 *
 * @param {string} textStatus - A string describing the response status
 * @param {jqXHR} jqXHR - The jQuery XHR response object
 */
function handleFailure(jqXHR: JQueryXHR, textStatus: string){
    handleResponse(jqXHR.responseJSON ?? textStatus, false, jqXHR.status);
}

window.addEventListener("load", (_e) => {
    const submitBtn = $("#submitBtn");

    $("form button").on('click', function(event){
        event.preventDefault();

        console.log("henlo")

        // Lock out button
        submitBtn.attr("disabled", "disabled");

        // Grab form input
        const formData = new FormData($(this).closest("form")[0]);

        // Check for token as URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get("token")
        const suffix = token ? "?token=" + encodeURIComponent(token) : "";

        // Set request options
        const options: AjaxSettings = {
            method: $(this).data("requestMethod"),
            data: Object.fromEntries(formData),
            dataType: "",
            success: handleSuccess,
            error: handleFailure
        };

        // Fire request
        $.ajax(
            $(this).data("requestPath") + suffix,
            options
        );

        // Unlock button
        submitBtn.removeAttr("disabled");
    });

    $("form").on("submit", (event) => {
        event.preventDefault();
        let buttons = $(event.target).find(".btn");
        if (buttons.length === 1){
            buttons[0].click();
        }
    })
});