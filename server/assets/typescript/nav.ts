function processMajorTabChange(node: HTMLElement){
    // Declare some constants
    const others = document.querySelectorAll(`nav a:not(#${node.id})`);
    const myName = node.attributes.getNamedItem("data-my-name")?.value ?? "";

    // Am I a parent or a child?
    if (node.attributes.getNamedItem("data-sidenav-parent")){
        const parentTabId = node.attributes.getNamedItem("data-sidenav-parent")?.value;

        // Set this child group visible
        document.querySelectorAll(`nav a[data-sidenav-parent=\"${parentTabId}\"]`).forEach((element) => {
            element.classList.remove("visually-hidden");
        });

        // Set all others to hidden
        document.querySelectorAll(`nav a[data-sidenav-parent]:not([data-sidenav-parent='${parentTabId}'])`)
            .forEach((element) => {
                element.classList.add("visually-hidden");
            });

        // Remove child-selected from others, but ensure that the parent has it!
        others.forEach((element) => {
            element.classList.remove("child-selected")
        })
        document.getElementById(parentTabId + "_link")?.classList.add("child-selected");

    } else {
        // Show children that are mine
        document.querySelectorAll(`nav a[data-sidenav-parent='${myName}']`).forEach((element) => {
            element.classList.remove("visually-hidden");
        });

        // Hide those that aren't
        document.querySelectorAll(`nav a[data-sidenav-parent]:not([data-sidenav-parent='${myName}'])`)
            .forEach((element) => {
                element.classList.add("visually-hidden");
            });

        // Snatch the child-selected class
        node.classList.add("child-selected");
        others.forEach((element) => {
            element.classList.remove("child-selected");
        });
    }
}

window.addEventListener("load", (_e) => {
    // Process initial tab layout
    const activeTab = document.querySelector("nav a.active");
    if (activeTab && activeTab instanceof HTMLElement)
        processMajorTabChange(activeTab);

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
    document.querySelectorAll("nav a[data-bs-toggle='tab']").forEach((element) => {
        observer.observe(element, {attributes: true});
    });
})