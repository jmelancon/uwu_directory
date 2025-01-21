import $ from 'jquery';

function processMajorTabChange(node: HTMLElement){
    // Declare some constants
    const me = $(node);
    const others = $(`nav a:not(#${me[0].id})`);
    const myName = me.data("myName");
    const myChildren = $(`nav a[data-sidenav-parent='${myName}']`);
    const otherChildren = $(`nav a[data-sidenav-parent]:not([data-sidenav-parent='${myName}'])`);

    // Hide nav children that aren't mine
    myChildren.removeClass("visually-hidden");
    otherChildren.addClass("visually-hidden");

    // Set CSS for myself and others
    me.addClass("child-selected");
    others.removeClass("child-selected");
}

window.addEventListener("load", (_e) => {
    // Process initial tab layout
    processMajorTabChange($("nav a.active")[0])

    // Add hook for tab changes
    $("nav a[data-bs-toggle='tab']:not([data-sidenav-parent])").on("click", (e) => {
        processMajorTabChange(e.target.parentElement ?? e.target);
    })
})