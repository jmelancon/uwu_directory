import {issueRequest} from "./comms";

window.addEventListener("load", (_e) => {
    document.querySelectorAll("form").forEach((element) => {
        element.addEventListener("submit", async (event) => {
            event.preventDefault();

            // Get caller. It'll hold the request information.
            const caller = event.submitter;
            if (caller == null || !(caller instanceof HTMLButtonElement))
                return;

            // Lock out form buttons
            element.querySelectorAll("button.btn").forEach((button) => {
                debugger;
                if (button instanceof HTMLButtonElement)
                    button.disabled = true;
            })

            // Grab form input
            const formData = new FormData(element);

            // Check for token as URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get("token")
            const suffix = token ? "?token=" + encodeURIComponent(token) : "";

            // Serialize FormData to object
            // This code is actually so shit it's not even funny
            let serializedData = {};
            for (const item of formData) {
                const key = item[0];
                const formValue = item[1];
                // Since HTML [name] syntax permits arrays, associative or indexed, we'll have to iterate
                // over the string and ensure that these arrays are created. Define a regex for the syntax first.
                const re = /(?<RootKeyArray>[^[\n]+)\[]|\[(?<KeyArray>.+?)]\[]|\[(?<Key>.+?)]|(?<RootKey>^[^[\n]+)/gm;

                // We'll use a """"pointer"""" to keep track of where we are in the object.
                let pointer = serializedData;

                // Iterate!
                Array.from(key.matchAll(re)).forEach((results: RegExpExecArray, index: number, array: RegExpExecArray[]) => {
                    // Grab the named groups from the regex results
                    const groups = results.groups;
                    if (!groups)
                        throw new Error();

                    const isArray: boolean = (groups["RootKeyArray"] != undefined) || (groups["KeyArray"] != undefined);
                    const isLeaf: boolean = index == array.length - 1
                    const property: string = (groups["RootKeyArray"] ?? "")
                        + (groups["KeyArray"] ?? "")
                        + (groups["Key"] ?? "")
                        + (groups["RootKey"] ?? "");
                    const propertyExists: boolean = pointer.hasOwnProperty(property);

                    // Are we at the end of the statement and need to assign?
                    if (isLeaf){
                        if (isArray && !propertyExists){//@ts-ignore
                            pointer[property] = [];
                        }

                        if (isArray) { //@ts-ignore
                            pointer[property].append(regexResults);
                        }
                        else{ //@ts-ignore
                            pointer[property] = formValue;
                        }

                    }
                    else if (!propertyExists){ //@ts-ignore
                        pointer[property] = isArray ? [] : {};
                    }

                    // Increment pointer
                    { //@ts-ignore
                        pointer = pointer[property];
                    }
                });
            }

            issueRequest(
                caller.getAttribute("data-request-path") + suffix,
                caller.getAttribute("data-request-method") ?? "",
                JSON.stringify(serializedData)
            );

            // Unlock button
            element.querySelectorAll("button.btn").forEach((button) => {
                if (button instanceof HTMLButtonElement)
                    button.disabled = false;
            })
        });
    });
});