import $ from "jquery";
import {populateModalList, showModal, resetModal} from "./modal";
addEventListener("keyup", (event) => {
    if (event.key == "~" && (document.activeElement ? $(document.activeElement).filter(":input").length == 0 : true)){
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
            "Directory Application, V0.0.0_alpha",
            "Created with <3 by 4096kb, copyright 2025. Open source and AGPL-licensed. Queer rights! 🏳️‍🌈 🏳️‍⚧️"
        );
    }
});