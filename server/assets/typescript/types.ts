export function isHandledResponse(obj: any): obj is HandledResponse {
    return 'sentinel' in obj && obj.sentinel
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