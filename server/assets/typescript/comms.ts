import $ from "jquery";
import {AjaxSettings} from "datatables.net-bs5";

import {populateModalList, populateModalSecret, redirectOnModalClose, resetModal, showModal} from "./modal";
import {isHandledResponse, isListResponse, isRedirectResponse, isSecretResponse} from './types'

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
        } else if (isSecretResponse(data)){
            populateModalSecret(data.secret);
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

/**
 * Wrap jQuery's Ajax method with some standardized modal stuff.
 *
 * @param endpoint
 * The endpoint to issue the request to.
 *
 * @param method
 * The request method to use.
 *
 * @param data
 * The serialized data as a Javascript Object. Optional.
 */
export function issueRequest(endpoint: string, method: string, data: {[p: string]: any}|null): void{
    // Set request options
    const options: AjaxSettings = {
        method: method,
        success: handleSuccess,
        error: handleFailure
    };

    // Add data if needed
    if (data){
        options.data = data;
        options.dataType = "";
    }

    // Fire request
    $.ajax(
        endpoint,
        options
    );
}

export function deleteRequest(endpoint: string): void{
    issueRequest(
        endpoint,
        "DELETE",
        null
    );
}