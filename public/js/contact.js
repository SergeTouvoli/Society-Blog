document.addEventListener("DOMContentLoaded", function() {
  const subject = document.querySelector("#contactSubject");
  const textarea = document.querySelector("textarea");

  textarea.addEventListener("keyup", function() {
  displayCharCount(this, 10, 2000);
  });
  
  subject.addEventListener("keyup", function() {
    displayCharCount(this, 3, 45);
  });
});