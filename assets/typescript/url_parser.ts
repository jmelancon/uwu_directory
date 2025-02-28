let rootUrl: string;
let basePath: string = "/";

/**
 * Take a relative URL and make it absolute so reverse proxies work.
 *
 * @param relative
 * The relative path to a destination.
 *
 * @return
 * The constructed URL.
 */
export function parseUrl(relative: string): string {
    // Strip trailing and leading slashes (if they exist)
    const base = basePath.replace(/^(.*?)\/$/, "$1");
    const rel = relative.replace(/^\/?(.*)/, "$1");
    return `${base}/${rel}`;
}
window.addEventListener("load",(_e) => {
    // We need the root URL of the site so our stuff works when behind a reverse proxy. A reference
    // to the URL is hidden in the site's <header>.
    rootUrl = document.getElementById("root_url")?.getAttribute("href") ?? "";
    if (rootUrl) {
        basePath = new URL(rootUrl).pathname ?? "/";
    }
});