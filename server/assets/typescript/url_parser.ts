let rootUrl: string;

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
    return rootUrl + relative;
}
window.addEventListener("load",(_e) => {
    // We need the root URL of the site so our stuff works when behind a reverse proxy. A reference
    // to the URL is hidden in the site's <header>.
    rootUrl = document.getElementById("root_url")?.getAttribute("href") ?? "";
});