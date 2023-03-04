document.addEventListener("DOMContentLoaded", function() {
  const textarea = document.getElementById("comment_content");
  textarea.addEventListener("keyup", function() {
    displayCharCount(this, 5, 150);
  });

  loadComment();
});

async function submitComment(event) {
  event.preventDefault(); 

  const currentOrigin = window.location.origin;
  const url = currentOrigin + '/Social/ajouter-commentaire';

  // Récupère les données du formulaire
  const form = document.getElementById('commentForm');
  const formData = new FormData(form);
  const successInfos = document.querySelector('.successComment');
  const errorInfos = document.querySelector('.errorComment');
  

  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    const data = await response.json();
    if(data.errors && data.errors.length > 0) {
      errorInfos.textContent = data.errors.join('; ');
    } else {
      errorInfos.textContent = '';
      successInfos.textContent = 'Commentaire ajouté avec succès !';
      loadComment();
      const commentContent = document.getElementById('comment_content');
      commentContent.value = '';
    }
  } catch (error) {
    console.error(error);
    errorInfos.textContent = 'Une erreur est survenue. Veuillez réessayer.';
  }
}
