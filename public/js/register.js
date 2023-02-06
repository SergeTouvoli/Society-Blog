document.addEventListener("DOMContentLoaded", function() {
  const password = document.querySelector("#userPassword");
  
  if (password.value.length !== "") {
    verifFormatPassword(password);
  }
  
  password.addEventListener("keyup", function() {
    verifFormatPassword(password);
  });
  
  password.addEventListener("focus", function() {
    verifFormatPassword(password);
  });

});