import { Modal, Ripple, initMDB } from "mdb-ui-kit/js/mdb.umd.min.js";
import $ from 'jquery';
import '@popperjs/core';

/**
 * Definition for handled responses from the server. Strictly useful for type-hinting in
 * IDEs such as PhpStorm.
 *
 * @typedef {object} HandledResponse
 * @property {string} title: The title to be shown in the modal
 * @property {string} message: The message to be shown in the modal
 * @property {string} sentinel: A sentinel value to signal that this is a handled
 *                              response. The value will always be "omg haiiiiiii :3"
 * @property {string} responseType: The type of response. Useful for if we need to convey a differently-structured data item.
 */

/**
 * List response. Simply requires an unordered list to be shown in the modal.
 *
 * @typedef {HandledResponse} ListResponse
 * @property {array<string>} listContents
 */

/**
 * Flash item. Used to report errors when loading a fresh page.
 *
 * @typedef {object} Flash
 * @property {string} type
 * @property {string} message
 */

// Initialize MDB
initMDB({Modal, Ripple});

// Create global modal for usage in error/success messages
window.modal = new Modal($("#modal"));

// Assume submit button always has the ID `submitBtn`. Create an element for it.
const submitBtn = $("#submitBtn");

// Set dark mode if applicable
document.documentElement.dataset.mdbTheme =
    window.matchMedia("(prefers-color-scheme: dark)").matches ?
        "dark" : "light";

/**
 * Rather than relying on the stock alert(), which is gross and ugly,
 * we'll use the much cuter MDBootstrap Modal element. This function will
 * set its contents and show it.
 * @param {string} title
 * @param {string} message
 */
function showModal(title, message){
    $("#modalLabel").text(title);
    $("#modalMessage").text(message);
    modal.show();
}

/**
 * Password validation. Should be called by onkeyup on password
 * `<input>` elements. The password box must have the ID `password`,
 * and the password confirmation box must have the ID `passwordConfirm`.
 * Depending on if the two boxes contents match and if the boxes
 * are valid, the submit button with ID `submitBtn` will be either enabled
 * or disabled.
 */
function validatePasswords(){
    const passwd = $("#password")[0];
    const confirm = $("#passwordConfirm")[0];

    const passwordsMatch = passwd.value === confirm.value;
    const passwordValid = passwd.checkValidity();

    if (passwordsMatch && passwordValid)
        submitBtn.removeAttr("disabled");
    else
        submitBtn.attr("disabled", "disabled");
}
window.validatePasswords = validatePasswords;

/**
 * Clear the modal.
 */
function resetModal(){
    // Clear and hide list
    const list = $("#modalList")
    list.children('li').remove();
    list.addClass("visually-hidden");
}

/**
 * Fill the modalList `<ul>`. Show it too.
 *
 * @param {array<string>} list
 */
function populateModalList(list){
    const modalList = $("#modalList");
    list.forEach(function(entry){
        const li = document.createElement( "li" )
        li.innerText = entry;
        modalList.append(li);
    });

    modalList.removeClass("visually-hidden")
}

/**
 * Blanket tool for handling responses from AJAX requests.
 *
 * @param {object} data - Could possibly be a handled response, but also may not be!
 * @param {boolean} hasSucceeded - If the request has succeeded or not.
 * @param {int} statusCode
 */
function handleResponse(data, hasSucceeded, statusCode){
    // Clear
    resetModal()

    // Check if the response is handled.
    if (Object.hasOwn(data, "sentinel") && data.sentinel === "omg haiiiiiii :3"){
        /** @var {HandledResponse} data */
        // Check if the response has a list associated
        if (data.responseType === "list"){
            /** @var {ListResponse} data */
            populateModalList(data.listContents);
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
 * @param {string} textStatus - A string describing the response status
 * @param {jqXHR} jqXHR - The jQuery XHR response object
 */
function handleSuccess(data, textStatus, jqXHR) {
    handleResponse(data, true, jqXHR.status);
}

/**
 * Simply a passthrough function to the handleResponse function.
 *
 * @param {string} textStatus - A string describing the response status
 * @param {jqXHR} jqXHR - The jQuery XHR response object
 */
function handleFailure(jqXHR, textStatus){
    handleResponse(jqXHR.responseJSON ?? textStatus, false, jqXHR.status);
}

function displayFlashes(){
    /** @var {array<Flash>} window.flashes */
    resetModal();
    populateModalList(
        window.flashes.map(
            function(flash){
                return flash.message;
            }
        )
    )
    showModal(
        "Alert",
        "There was an issue with your request. The server left the following message(s):"
    )
}

if (window.flashes.length > 0)
    displayFlashes();

$("form button").on('click', function(event){
    event.preventDefault();

    // Lock out button
    submitBtn.attr("disabled", "disabled");

    // Grab form input
    const formData = new FormData($(this).closest("form")[0]);

    // Check for token as URL parameter
    var urlParams = new URLSearchParams(window.location.search);
    var suffix = "";
    if (urlParams.has('token'))
        suffix = "?token=" + encodeURIComponent(urlParams.get("token"));

    // Set request options
    let options = {
        method: $(this).data("requestMethod"),
        data: Object.fromEntries(formData),
        dataType: false,
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

$("form").on("submit", function(event){
    event.preventDefault();
    let buttons = $(this).find(".btn");
    if (buttons.length === 1){
        buttons[0].click();
    }
})