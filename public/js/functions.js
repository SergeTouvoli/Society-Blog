function openMenu() {
    const navBar = document.querySelector("#myTopnav");
    if (navBar.classList.contains("responsive")) {
        navBar.classList.remove("responsive");
    } else {
        navBar.classList.add("responsive");
    }
}

function loadComment(){
    const currentOrigin = window.location.origin;
    const outputDiv = document.querySelector('#listComments');
    const url = currentOrigin + '/Social/charger-commentaire';
    const idPost = document.querySelector('.card-article').dataset.id;
      
    fetch(url,{
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'idPost=' + idPost
    })
    .then(response => response.text())
    .then(data => {
      outputDiv.innerHTML = data;
    });
  
}
  
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.querySelector('#output_image').src = reader.result;
        document.querySelector('#output_image').style.height = '150px';
        document.querySelector('#output_image').style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}

function displayCharCount(element, minCaractères = 0, maxCaractères) {
    const infosClass = element.getAttribute("data-infos");
    const infos = document.querySelector("." + infosClass);
    const nbrCaractères = element.value.length;

    if (infos) {
        infos.style.display = "block";
        infos.textContent = nbrCaractères + '/' + maxCaractères + ' caractères';
        
        if (nbrCaractères < minCaractères || nbrCaractères > maxCaractères) {
            infos.style.color = "red";
        } else {
            infos.style.color = "green";
        }
        
        infos.addEventListener('blur', function() {
            infos.style.display = "none";
        });
    }
}

function verifFormatPassword(input) {
    if (!input) return;
    const messageBox = document.querySelector('#messageBox');
    const password = input.value;

    messageBox.style.display = 'block';

    verifLowerCaseLetters(password, "letter");
    verifUpperCaseLetters(password, "capital");
    verifNumbers(password, "number");
    verifLength(password, "length", 5);
}

function verifFormatPasswordConfirm(inputPassword, inputPasswordConfirm) {
    if (!inputPassword || !inputPasswordConfirm) return;
    let password = inputPassword.value;
    let passwordConfirm = inputPasswordConfirm.value;
    let msg = document.querySelector('#conform');

    msg.style.display = 'block';

    inputPasswordConfirm.addEventListener('keyup', function() {
        if (password.length === passwordConfirm.length) {
            msg.classList.remove("invalid");
            msg.classList.add("valid");
        } else {
            msg.classList.remove("valid");
            msg.classList.add("invalid");
        }
    });
}

function verifLowerCaseLetters(input, id) {
    const lowerCaseLetters = /[a-z]/g;
    const element = document.getElementById(id);

    if (input.match(lowerCaseLetters)) {
        element.classList.remove('invalid');
        element.classList.add('valid');
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
    }
}

function verifUpperCaseLetters(input, id) {
    const upperCaseLetters = /[A-Z]/g;
    const element = document.getElementById(id);

    if (input.match(upperCaseLetters)) {
        element.classList.remove('invalid');
        element.classList.add('valid');
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
    }
}

function verifNumbers(input, id) {
    const numbers = /[0-9]/g;
    const element = document.getElementById(id);

    if (input.match(numbers)) {
        element.classList.remove('invalid');
        element.classList.add('valid');
    } else {
        element.classList.remove('valid');
        element.classList.add('invalid');
    }
}

function verifLength(input, id, minLength = 0, maxLength = null) {
    const element = document.getElementById(id);

    if(input.length >= minLength || maxLength !== null && input.length <= maxLength ){
        element.classList.remove("invalid");
        element.classList.add("valid");
    }else{
        element.classList.remove("valid");
        element.classList.add("invalid");
    }
}

function confirmDeletePost(idPost) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
        window.location.href = `suppression-post/${idPost}`;
    }
}

function confirmDeleteUser(idUser) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        window.location.href = `suppression-user/${idUser}`;
    }
}

function openAccount(idUser) {
    window.location.href = `compte/${idUser}`;  
}

function changeRole(idUser) {
    if (confirm('Êtes-vous sûr de vouloir changer le rôle de cet utilisateur ?')) {
        window.location.href = `change-role/${idUser}`;
    }
}

function viewPassword(eye, password) {
    if (password.type === 'password') {
        password.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
        eye.style.color = '#9FC5E2';
    } else {
        password.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
        eye.style.color = 'black';
    }
    setTimeout(() => {
        password.style.transition = 'all 0.5s';
        password.type = 'password';
        eye.style.transition = 'all 0.5s';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
        eye.style.color = 'black';
    }, 10000);
}

function hidePassword() {
    password.type = 'password';
    eye.classList.remove('fa-eye-slash');
    eye.classList.add('fa-eye');
    eye.style.color = 'black';
}  