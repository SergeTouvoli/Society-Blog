document.addEventListener("DOMContentLoaded", function() {
  const textarea = document.querySelector("#comment");
  
  textarea.addEventListener("keyup", function() {
    displayCharCount(this, 5, 150);
  });
});


