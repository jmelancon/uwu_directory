import {populateModalList, showModal, resetModal} from "./modal";
addEventListener("keyup", (event) => {
    if ( event.key == "~" && !(["input", "textarea", "select"].includes(document.activeElement?.tagName?.toLowerCase() ?? ""))){
        resetModal();
        populateModalList(
            [
                "Bootstrap 5: Stylesheets & Icons",
                "Symfony 7: Web Backend & Templating Engine",
                "Roboto: Primary Typeface",
                "DataTables: Asynchronous Table Provider",
                "OpenLDAP (smblds): Directory Backend",
                "thephpleague/oauth2-server-bundle: OAuth2 Library"
            ]
        );
        showModal(
            "🌸 uwu_directory, V0.2.0_alpha 🌸",
            "Uncomplicated Web User Directory. Created with <3 by 4096kb. Queer rights! 🏳️‍🌈 🏳️‍⚧️"
        );
    }
});