import $ from 'jquery';

let submitBtn: JQuery;

/**
 * Password validation. Should be called by onkeyup on password
 * `<input>` elements. The password box must have the ID `password`,
 * and the password confirmation box must have the ID `passwordConfirm`.
 * Depending on if the two boxes contents match and if the boxes
 * are valid, the submit button with ID `submitBtn` will be either enabled
 * or disabled.
 */
window.validatePasswords = function validatePasswords(){
    const passwd = $("#password");
    const confirm = $("#passwordConfirm");

    const passwordsMatch = passwd.val() === confirm.val();
    const passwordValid = (<HTMLFormElement>passwd[0]).checkValidity();

    if (passwordsMatch && passwordValid)
        submitBtn.removeAttr("disabled");
    else
        submitBtn.attr("disabled", "disabled");
}

window.addEventListener("load", (_e) => {
    submitBtn = $("#submitBtn");
})