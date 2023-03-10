document.addEventListener("DOMContentLoaded", function() {
  var passwordFields = document.querySelectorAll("#newPassword, #newPasswordConfirm");

  passwordFields.forEach(field => {
    field.addEventListener("keyup", function() {
      verifFormatPassword(document.querySelector("#newPassword"));
      verifFormatPasswordConfirm(document.querySelector("#newPassword"), document.querySelector("#newPasswordConfirm"));
    });
    field.addEventListener("focus", function() {
      verifFormatPassword(document.querySelector("#newPassword"));
      verifFormatPasswordConfirm(document.querySelector("#newPassword"), document.querySelector("#newPasswordConfirm"));
    });
  });
  if (passwordFields[0].value.length !== "" && passwordFields[1].value.length !== "") {
    verifFormatPassword(document.querySelector("#newPassword"));
    verifFormatPasswordConfirm(document.querySelector("#newPassword"), document.querySelector("#newPasswordConfirm"));
  }
  
});