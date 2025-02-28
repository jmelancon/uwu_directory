export function isHandledResponse(obj: any): obj is HandledResponse {
    return obj && Object.hasOwn(obj, "sentinel") && obj.sentinel === "omg haiiiiiii :3"
}

export function isListResponse(obj: HandledResponse): obj is ListResponse{
    return obj.responseType === "list";
}

export function isRedirectResponse(obj: HandledResponse): obj is RedirectResponse{
    return obj.responseType === "redirect";
}

export function isSecretResponse(obj: HandledResponse): obj is SecretResponse{
    return obj.responseType === "secret";
}