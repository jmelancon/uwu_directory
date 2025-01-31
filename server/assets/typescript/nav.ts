import $ from 'jquery';

function processMajorTabChange(node: HTMLElement){
    // Declare some constants
    const me = $(node);
    const others = $(`nav a:not(#${me[0].id})`);
    const myName = me.data("myName");

    // Am I a parent or a child?
    if (me.data("sidenavParent")){
        const parentTabId = me.data("sidenavParent")
        const fellowChildren = $(`nav a[data-sidenav-parent=\"${parentTabId}\"]`);
        const otherChildren = $(`nav a[data-sidenav-parent]:not([data-sidenav-parent='${parentTabId}'])`);

        // Set this child group visible
        fellowChildren.removeClass("visually-hidden");
        otherChildren.addClass("visually-hidden");

        // Remove child-selected from others, but ensure that the parent has it!
        others.removeClass("child-selected");
        $("#" + parentTabId + "_link").addClass("child-selected");

    } else {
        const myChildren = $(`nav a[data-sidenav-parent='${myName}']`);
        const otherChildren = $(`nav a[data-sidenav-parent]:not([data-sidenav-parent='${myName}'])`);

        // Hide nav children that aren't mine
        myChildren.removeClass("visually-hidden");
        otherChildren.addClass("visually-hidden");

        // Snatch the child-selected class
        me.addClass("child-selected");
        others.removeClass("child-selected");
    }
}

window.addEventListener("load", (_e) => {
    // Process initial tab layout
    processMajorTabChange($("nav a.active")[0])

    // Add a mutation observer to watch for aria-selected changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (
                mutation.type === "attributes"
                && mutation.attributeName === "aria-selected"
                && mutation.target instanceof HTMLElement
                && mutation.target.getAttribute("aria-selected") === "true") {
                processMajorTabChange(<HTMLElement>(mutation.target));
            }
        });
    });

    // Add hook for tab changes
    $("nav a[data-bs-toggle='tab']").each((_index, element) => {
        observer.observe(element, {attributes: true});
    })
})