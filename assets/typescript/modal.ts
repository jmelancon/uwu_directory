import {Modal} from "bootstrap";

let modal: Modal;

/**
 * Rather than relying on the stock alert(), which is gross and ugly,
 * we'll use the much cuter Bootstrap Modal element. This function will
 * set its contents and show it.
 */
export function showModal(title: string, message: string){
    const modalElement = document.querySelector("#modal");

    // Assert that the modal isn't already open. If it is, we need to close it and wait for it
    // to go away.
    if (modalElement
        && modalElement instanceof HTMLElement
        && (modalElement.checkVisibility() || modalElement.style.display !== "none")){
        modal.hide();
        modalElement.addEventListener("hidden.bs.modal", () => {
            // Set a 50ms sleep so we don't break the CSS on the backdrop
            new Promise(resolve => {setTimeout(resolve, 50)}).then(() => {
                showModal(title, message);
            });
        }, {once: true})
        return;
    } else {
        const modalLabel = document.getElementById("modalLabel");
        const modalMessage = document.getElementById("modalMessage");

        if (modalLabel && modalMessage){
            modalLabel.innerText = title;
            modalMessage.innerText = message;
        }
        modal.show();
    }
}

/**
 * Clear the modal.
 */
export function resetModal(){
    // Clear and hide list
    const list = document.getElementById("modalList");
    if (list){
        for (const child of list.children){
            child.remove();
        }
        list.classList.add("visually-hidden");
    }

    // Clear and hide secret
    const secret = document.getElementById("modalSecret");
    if (secret){
        secret.innerText = "";
        secret.closest("div")?.classList.add("visually-hidden");
    }

    // Hide confirmation div
    const confirmDiv = document.getElementById("modalConfirm");
    const dismissDiv = document.getElementById("modalDismiss")
    if (confirmDiv && dismissDiv){
        confirmDiv.classList.add("visually-hidden");
        dismissDiv.classList.remove("visually-hidden");

        // Recreate the confirm button so we can remove event listeners
        const confirmButton = confirmDiv.querySelector("button.btn-danger");
        if (confirmButton){
            const newButton = confirmButton.cloneNode(true);
            confirmDiv.insertBefore(newButton, confirmButton);
            confirmButton.remove();
        }
    }
}

/**
 * Add a secret to the preformatted segment in the modal and show it.
 *
 * @param secret
 * The secret!!! waow!
 */
export function populateModalSecret(secret: string){
    const modalSecret = document.getElementById("modalSecret");
    if (!modalSecret)
        return;

    modalSecret.innerText = secret;
    modalSecret.closest("div")?.classList.remove("visually-hidden");
}

/**
 * Fill the modalList `<ul>`. Show it too.
 */
export function populateModalList(list: string[]){
    const modalList = document.getElementById("modalList");
    if (!modalList)
        return;

    list.forEach(function(entry){
        const li = document.createElement( "li" );
        li.innerText = entry;
        modalList.appendChild(li);
    });

    modalList.classList.remove("visually-hidden");
}

/**
 * Set up the modal for use as a confirmation dialog.
 */
export function populateModalConfirm(action: Function, ...args: any[]) {
    // All we'll do is copy over the attributes from the calling button, minus the blocking
    // attribute.
    const confirmDiv = document.getElementById("modalConfirm");
    const dismissDiv = document.getElementById("modalDismiss");

    if (!(confirmDiv && dismissDiv))
        return;

    const confirmButton = confirmDiv.querySelector("button.btn-danger");

    if (!confirmButton)
        return;

    confirmButton.addEventListener("click", action.bind(null, ...args), {once: true});

    confirmDiv.classList.remove("visually-hidden");
    dismissDiv.classList.add("visually-hidden");
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
    modal = new Modal("#modal");
});