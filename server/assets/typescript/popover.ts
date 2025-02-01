import {Popover} from "bootstrap";
window.addEventListener("load", (_e) => {
    [...document.querySelectorAll('[data-bs-toggle="popover"]')].map(popoverTriggerEl => new Popover(popoverTriggerEl))
})