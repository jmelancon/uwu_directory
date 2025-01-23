/**
 * Definition for handled responses from the server. Strictly useful for type-hinting in
 * IDEs such as PhpStorm.
 */
interface HandledResponse {
    title: string;
    message: string;
    sentinel: string;
    responseType: string;
}

/**
 * List response. Simply requires an unordered list to be shown in the modal.
 */
interface ListResponse extends HandledResponse{
    listContents: string[];
}

/**
 * Redirect response. Used to draw the response, then redirect once the user dismisses the modal.
 */
interface RedirectResponse extends HandledResponse{
    url: string;
}

/**
 * Flash item. Used to report errors when loading a fresh page.
 */
interface Flash {
    type: string;
    message: string;
}
/**
 * User.
 */
interface User {
    identifier: string;
    firstName: string;
    lastName: string;
    email: string;
    roleDNs: string[];
}