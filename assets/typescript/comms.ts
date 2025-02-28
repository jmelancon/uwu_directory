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
export function issueRequest(endpoint: string, method: string, data: BodyInit|null): void{
    // Set request options
    const options: RequestInit = {
        method: method,
        body: data,
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-Requested-By": "uwu_client",
        },
        mode: "cors",
        credentials: "same-origin"
    };

    // Fire request
    fetch(endpoint, options)
        .then((response: Response) => {
            // Get the response JSON, then act on it.
            response.json().then((value: any) => {
                handleResponse(value, response.ok, response.status);
            }).catch((_error: SyntaxError) => {
                handleResponse(null, false, response.status)
            })
        })
}

export function deleteRequest(endpoint: string): void{
    issueRequest(
        endpoint,
        "DELETE",
        null
    );
}

export function postRequest(endpoint: string, data: BodyInit|null = null): void{
    issueRequest(
        endpoint,
        "POST",
        data
    );
}