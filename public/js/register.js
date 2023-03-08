document.addEventListener("DOMContentLoaded", function() {
  const password = document.getElementById("userPassword");
  const fileInput = document.getElementById("userAvatar");
  
  
  if (password.value.length !== "") {
    verifFormatPassword(password);
  }

  password.addEventListener("keyup", function() {
    verifFormatPassword(password);
  });
  
  password.addEventListener("focus", function() {
    verifFormatPassword(password);
  });

  fileInput.addEventListener("change", function(event) {
    previewImage(event);
  });

});