import $ from "jquery";
import {issueRequest} from "./comms";
window.addEventListener("load", (_e) => {
    const submitBtn = $("#submitBtn");

    $("form button").on('click', function(event){
        event.preventDefault();

        // Lock out button
        submitBtn.attr("disabled", "disabled");

        // Grab form input
        const formData = new FormData($(this).closest("form")[0]);

        // Check for token as URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get("token")
        const suffix = token ? "?token=" + encodeURIComponent(token) : "";

        issueRequest(
            $(this).data("requestPath") + suffix,
            $(this).data("requestMethod"),
            Object.fromEntries(formData)
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