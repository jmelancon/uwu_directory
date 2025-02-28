import {populateModalList, resetModal, showModal} from "./modal";

function displayFlashes(){
    resetModal();
    populateModalList(
        window.flashes.map(
            function(flash: Flash){
                return flash.message;
            }
        )
    )
    showModal(
        "Alert",
        "The server left the following message(s) pertaining to your previous request:"
    )
}

window.addEventListener("load", (_e) => {
    if (window.flashes.length > 0)
        displayFlashes();
})