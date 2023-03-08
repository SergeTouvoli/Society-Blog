
document.addEventListener("DOMContentLoaded", () => {
  
  const searchInput = document.querySelector('#search');
  const outputDiv = document.querySelector('.resultSearch');

  searchInput.addEventListener('keyup', function() {
    searchPosts(this.value);
  });

  searchInput.addEventListener('focus', function() {
    outputDiv.style.display = 'block';
  });

});
  
function searchPosts(inputValue) {

  const currentOrigin = window.location.origin;
  const outputDiv = document.querySelector('.resultSearch');
  const url = currentOrigin + '/Social/recherchePost';
  
  if (inputValue !== '') {
    fetch(url,{
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'string=' + inputValue
    })
    .then(response => response.text())
    .then(data => {
      outputDiv.innerHTML = data;
      outputDiv.style.display = 'block';
    });
  } else {
    outputDiv.innerHTML = '';
    outputDiv.style.display = 'none';
  }
}
  